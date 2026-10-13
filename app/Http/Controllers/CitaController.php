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
use App\Services\DisponibilidadService;
use Illuminate\Http\JsonResponse;

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

    /**
     * Calendario del barbero: por cada día del mes, si trabaja y cuántas citas pendientes tiene.
     * GET panel/citas/calendario?mes=YYYY-MM
     */
    public function calendario(Request $request): JsonResponse
    {
        $tz = config('app.timezone');
        $mes = (string) $request->query('mes', now($tz)->format('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            return response()->json(['ok' => false, 'mensaje' => 'El mes no es válido.'], 422);
        }

        $user = $request->user();
        $ini = Carbon::createFromFormat('!Y-m', $mes, $tz)->startOfMonth();
        $fin = $ini->copy()->endOfMonth();
        $hoy = now($tz)->startOfDay();

        $diasTrabajo = $user->horarios()->where('trabaja', true)->pluck('dia_semana')
            ->map(fn ($d) => (int) $d)->all();

        $conteo = Cita::where('user_id', $user->id)
            ->where('estado', 'pendiente')
            ->whereBetween('inicio', [$ini, $fin])
            ->get(['inicio'])
            ->groupBy(fn (Cita $c) => $c->inicio->format('Y-m-d'))
            ->map(fn ($grupo) => $grupo->count());

        $dias = [];
        for ($d = $ini->copy(); $d->lte($fin); $d->addDay()) {
            $clave = $d->format('Y-m-d');
            $dias[$clave] = [
                'trabaja' => in_array($d->dayOfWeek, $diasTrabajo, true),
                'citas' => (int) ($conteo[$clave] ?? 0),
                'pasado' => $d->lt($hoy),
            ];
        }

        return response()->json([
            'ok' => true,
            'mes' => $mes,
            'titulo' => ucfirst($ini->copy()->locale('es')->translatedFormat('F Y')),
            'primer_dia_semana' => $ini->dayOfWeek, // 0 = domingo
            'dias' => $dias,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * Agenda de un día, hora por hora: libre, cita, bloqueo. Sin las horas de almuerzo.
     * GET panel/citas/dia?fecha=YYYY-MM-DD   (solo desde hoy en adelante)
     */
    public function dia(Request $request): JsonResponse
    {
        $tz = config('app.timezone');
        $texto = (string) $request->query('fecha', '');

        try {
            $dia = Carbon::createFromFormat('!Y-m-d', $texto, $tz);
        } catch (\Throwable $e) {
            $dia = null;
        }

        if (! $dia || $dia->format('Y-m-d') !== $texto) {
            return response()->json(['ok' => false, 'mensaje' => 'La fecha no es válida.'], 422);
        }

        $ahora = now($tz);

        if ($dia->lt($ahora->copy()->startOfDay())) {
            return response()->json(['ok' => false, 'mensaje' => 'Solo puedes ver desde hoy en adelante.'], 422);
        }

        $user = $request->user();
        $diaIni = $dia->copy();
        $diaFin = $dia->copy()->endOfDay();

        $horario = $user->horarios()->where('dia_semana', $dia->dayOfWeek)->first();
        $trabaja = $horario && $horario->trabaja;

        $citas = Cita::with('corte')
            ->where('user_id', $user->id)
            ->where('estado', 'pendiente')
            ->where('inicio', '<', $diaFin)
            ->where('fin', '>', $diaIni)
            ->orderBy('inicio')
            ->get();

        $bloqueos = $user->bloqueos()
            ->where('inicio', '<', $diaFin)
            ->where('fin', '>', $diaIni)
            ->get();

        $filas = [];
        $mostradas = [];
        $jornada = null;
        $pausa = null;

        if ($trabaja) {
            $fecha = $dia->format('Y-m-d');
            $jIni = Carbon::parse($fecha . ' ' . $horario->hora_inicio, $tz);
            $jFin = Carbon::parse($fecha . ' ' . $horario->hora_fin, $tz);
            $pIni = ($horario->pausa_inicio && $horario->pausa_fin) ? Carbon::parse($fecha . ' ' . $horario->pausa_inicio, $tz) : null;
            $pFin = ($horario->pausa_inicio && $horario->pausa_fin) ? Carbon::parse($fecha . ' ' . $horario->pausa_fin, $tz) : null;
            $paso = DisponibilidadService::INTERVALO_MINUTOS;

            $jornada = $jIni->format('g:i a') . ' a ' . $jFin->format('g:i a');
            $pausa = $pIni ? $pIni->format('g:i a') . ' a ' . $pFin->format('g:i a') : null;

            for ($ini = $jIni->copy(); $ini->lt($jFin); $ini->addMinutes($paso)) {
                $fin = $ini->copy()->addMinutes($paso);
                if ($fin->gt($jFin)) {
                    $fin = $jFin->copy();
                }

                // La hora que cae completa dentro del almuerzo no se muestra.
                if ($pIni && $ini->gte($pIni) && $fin->lte($pFin)) {
                    continue;
                }

                $enFila = $citas->filter(fn (Cita $c) => $c->inicio->lt($fin) && $c->fin->gt($ini))->values();
                $empiezan = $enFila->filter(fn (Cita $c) => $c->inicio->gte($ini))->values();

                if ($empiezan->isNotEmpty()) {
                    $tipo = 'cita';
                    $lista = $empiezan;
                } elseif ($enFila->isNotEmpty()) {
                    $tipo = 'continua';
                    $lista = $enFila;
                } elseif ($bloqueos->contains(fn ($b) => $b->inicio->lt($fin) && $b->fin->gt($ini))) {
                    $tipo = 'bloqueo';
                    $lista = collect();
                } elseif ($fin->lte($ahora)) {
                    $tipo = 'pasada';
                    $lista = collect();
                } else {
                    $tipo = 'libre';
                    $lista = collect();
                }

                foreach ($lista as $c) {
                    $mostradas[$c->id] = true;
                }

                $filas[] = [
                                        'hora' => $ini->format('g:i a'),
                    'hasta' => $fin->format('g:i a'),
                    'valor' => $ini->format('H:i'),
                    'tipo' => $tipo,
                    'citas' => $lista->map(fn (Cita $c) => $this->presentarDia($c))->all(),
                ];
            }
        }

        // Citas que quedaron fuera de su horario (por ejemplo, movidas a mano).
        $otras = $citas->filter(fn (Cita $c) => ! isset($mostradas[$c->id]))
            ->map(fn (Cita $c) => $this->presentarDia($c))
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'fecha' => $dia->format('Y-m-d'),
            'etiqueta' => $this->etiquetaDia($dia),
            'titulo' => ucfirst($dia->copy()->locale('es')->translatedFormat('l j \d\e F')),
            'trabaja' => (bool) $trabaja,
            'jornada' => $jornada,
            'pausa' => $pausa,
            'total_citas' => $citas->count(),
            'horas_libres' => collect($filas)->where('tipo', 'libre')->count(),
            'filas' => $filas,
            'otras' => $otras,
        ])->header('Cache-Control', 'no-store');
    }

    private function presentarDia(Cita $c): array
    {
        return [
            'id' => $c->id,
            'cliente' => $c->cliente_nombre,
            'telefono' => $c->cliente_telefono,
            'wa' => preg_replace('/\D/', '', (string) $c->cliente_telefono),
            'corte' => $c->corte?->nombre ?? 'Corte',
            'ini' => $c->inicio->format('g:i a'),
            'fin' => $c->fin->format('g:i a'),
            'minutos' => (int) round(($c->fin->getTimestamp() - $c->inicio->getTimestamp()) / 60),
        ];
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