<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CitaController extends Controller
{
    private const ETIQUETAS = [
        'completada' => 'Terminada',
        'cancelada' => 'Cancelada',
        'no_asistio' => 'No asistió',
    ];

    public function index(Request $request): View
    {
        $uid = $request->user()->id;
        $ahora = now();

        $pendientes = Cita::with('corte')
            ->where('user_id', $uid)
            ->where('estado', 'pendiente')
            ->orderBy('inicio')
            ->get();

        // Agrupadas: primero "Por cerrar" (ya empezaron), luego por día.
        $grupos = [];
        foreach ($pendientes as $c) {
            $yaInicio = $c->inicio->lte($ahora);
            $clave = $yaInicio ? 'por_cerrar' : $c->inicio->format('Y-m-d');

            if (! isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'titulo' => $yaInicio ? 'Por cerrar' : $this->etiquetaDia($c->inicio),
                    'por_cerrar' => $yaInicio,
                    'citas' => [],
                ];
            }
            $grupos[$clave]['citas'][] = $this->presentar($c, $yaInicio);
        }

        $hoy = $pendientes->filter(fn ($c) => $c->inicio->isToday())->count();

        $historial = Cita::with('corte')
            ->where('user_id', $uid)
            ->where('estado', '!=', 'pendiente')
            ->orderByDesc('inicio')
            ->limit(60)
            ->get()
            ->map(fn ($c) => [
                'estado' => $c->estado,
                'etiqueta' => self::ETIQUETAS[$c->estado] ?? ucfirst($c->estado),
                'fecha' => $this->etiquetaDia($c->inicio) . ' · ' . $c->inicio->format('H:i'),
                'cliente' => $c->cliente_nombre,
                'corte' => $c->corte?->nombre ?? 'Corte',
            ]);

        return view('barberos.citas', [
            'grupos' => $grupos,
            'total' => $pendientes->count(),
            'hoy' => $hoy,
            'historial' => $historial,
        ]);
    }

    public function completar(Request $request, int $cita): RedirectResponse
    {
        return $this->cambiarEstado($request, $cita, 'completada', 'Cita marcada como terminada.', true);
    }

    public function noAsistio(Request $request, int $cita): RedirectResponse
    {
        return $this->cambiarEstado($request, $cita, 'no_asistio', 'Cita marcada como "no asistió".', true);
    }

    public function cancelar(Request $request, int $cita): RedirectResponse
    {
        // Paso WhatsApp: aquí se avisará al cliente de la cancelación.
        return $this->cambiarEstado($request, $cita, 'cancelada', 'Cita cancelada. El espacio quedó libre.', false);
    }

    public function reprogramar(Request $request, int $cita): RedirectResponse
    {
        $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
        ], [
            'fecha.required' => 'Indica la nueva fecha.',
            'fecha.date_format' => 'La fecha no es válida.',
            'hora.required' => 'Indica la nueva hora.',
            'hora.date_format' => 'La hora no es válida.',
        ]);

        $user = $request->user();
        $tz = config('app.timezone');
        $nuevoInicio = Carbon::createFromFormat('Y-m-d H:i', $request->input('fecha') . ' ' . $request->input('hora'), $tz);

        if ($nuevoInicio->lte(now())) {
            throw ValidationException::withMessages(['hora' => 'La nueva hora debe ser en el futuro.']);
        }

        try {
            DB::transaction(function () use ($user, $cita, $nuevoInicio) {
                $modelo = Cita::where('user_id', $user->id)->lockForUpdate()->findOrFail($cita);

                if ($modelo->estado !== 'pendiente') {
                    throw ValidationException::withMessages(['accion' => 'Esa cita ya no está pendiente.']);
                }

                // La duración original se conserva.
                $minutos = (int) round(($modelo->fin->getTimestamp() - $modelo->inicio->getTimestamp()) / 60);
                $nuevoFin = $nuevoInicio->copy()->addMinutes($minutos);

                $choca = Cita::where('user_id', $user->id)
                    ->where('estado', 'pendiente')
                    ->where('id', '!=', $modelo->id)
                    ->where('inicio', '<', $nuevoFin)
                    ->where('fin', '>', $nuevoInicio)
                    ->orderBy('inicio')
                    ->first();

                if ($choca) {
                    throw ValidationException::withMessages([
                        'hora' => 'Ya tienes otra cita en ese horario (de '
                            . $choca->inicio->format('H:i') . ' a ' . $choca->fin->format('H:i') . ').',
                    ]);
                }

                $modelo->inicio = $nuevoInicio;
                $modelo->fin = $nuevoFin;
                $modelo->save(); // el modelo recalcula slot_activo solo
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['hora' => 'Ese horario acaba de ocuparse. Elige otro.']);
        }

        // Paso WhatsApp: aquí se avisará al cliente del cambio de hora.
        return redirect()
            ->route('citas')
            ->with('ok', 'Cita movida al ' . $this->etiquetaDia($nuevoInicio) . ' a las ' . $nuevoInicio->format('H:i') . '.');
    }

    private function cambiarEstado(Request $request, int $cita, string $estado, string $mensaje, bool $exigeInicio): RedirectResponse
    {
        $modelo = Cita::where('user_id', $request->user()->id)->findOrFail($cita);

        if ($modelo->estado !== 'pendiente') {
            return redirect()->route('citas')->withErrors(['accion' => 'Esa cita ya no está pendiente.']);
        }

        if ($exigeInicio && $modelo->inicio->isFuture()) {
            return redirect()->route('citas')->withErrors(['accion' => 'Esa cita todavía no ha empezado.']);
        }

        $modelo->estado = $estado;
        $modelo->save(); // slot_activo queda en null: el espacio se libera

        return redirect()->route('citas')->with('ok', $mensaje);
    }

    private function etiquetaDia(Carbon $fecha): string
    {
        if ($fecha->isToday()) {
            return 'Hoy';
        }
        if ($fecha->isTomorrow()) {
            return 'Mañana';
        }

        return ucfirst($fecha->copy()->locale('es')->translatedFormat('D j M'));
    }

    private function presentar(Cita $c, bool $yaInicio): array
    {
        $minutos = (int) round(($c->fin->getTimestamp() - $c->inicio->getTimestamp()) / 60);
        $corte = $c->corte;

        return [
            'id' => $c->id,
            'cliente' => $c->cliente_nombre,
            'telefono' => $c->cliente_telefono,
            'wa' => preg_replace('/\D/', '', (string) $c->cliente_telefono),
            'corte' => $corte?->nombre ?? 'Corte',
            'precio' => $corte ? '₡' . number_format($corte->precio, 0, ',', ' ') : null,
            'minutos' => $minutos,
            'hora_ini' => $c->inicio->format('H:i'),
            'hora_fin' => $c->fin->format('H:i'),
            'dia' => $this->etiquetaDia($c->inicio),
            'ya_inicio' => $yaInicio,
            'editar' => [
                'id' => $c->id,
                'cliente' => $c->cliente_nombre,
                'corte' => $corte?->nombre ?? 'Corte',
                'minutos' => $minutos,
                'fecha' => $c->inicio->format('Y-m-d'),
                'hora' => $c->inicio->format('H:i'),
            ],
        ];
    }
}