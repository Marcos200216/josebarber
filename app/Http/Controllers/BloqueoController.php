<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BloqueoController extends Controller
{
    public function index(Request $request): View
    {
        $bloqueos = $request->user()
            ->bloqueos()
            ->where('fin', '>=', now())
            ->orderBy('inicio')
            ->get()
            ->map(fn ($b) => $this->presentar($b));

        return view('barberos.bloqueos', ['bloqueos' => $bloqueos]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$inicio, $fin, $motivo] = $this->datos($request);

        $user = $request->user();
        $bloqueo = $user->bloqueos()->make();
        $bloqueo->forceFill([
            'user_id' => $user->id,
            'inicio' => $inicio,
            'fin' => $fin,
            'motivo' => $motivo,
        ])->save();

        return redirect()
            ->route('bloqueos')
            ->with('ok', 'Bloqueo guardado.' . $this->avisoCitas($user->id, $inicio, $fin));
    }

    public function update(Request $request, int $bloqueo): RedirectResponse
    {
        $user = $request->user();
        $modelo = $user->bloqueos()->findOrFail($bloqueo);

        [$inicio, $fin, $motivo] = $this->datos($request);

        $modelo->forceFill([
            'inicio' => $inicio,
            'fin' => $fin,
            'motivo' => $motivo,
        ])->save();

        return redirect()
            ->route('bloqueos')
            ->with('ok', 'Bloqueo actualizado.' . $this->avisoCitas($user->id, $inicio, $fin));
    }

    public function destroy(Request $request, int $bloqueo): RedirectResponse
    {
        $request->user()->bloqueos()->findOrFail($bloqueo)->delete();

        return redirect()->route('bloqueos')->with('ok', 'Bloqueo eliminado.');
    }

    /**
     * Valida el formulario y devuelve [inicio, fin, motivo].
     */
    private function datos(Request $request): array
    {
        $request->validate([
            'motivo' => ['nullable', 'string', 'max:120'],
            'fecha_inicio' => ['required', 'date_format:Y-m-d'],
            'fecha_fin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fecha_inicio'],
            'todo_el_dia' => ['nullable', 'boolean'],
            'hora_inicio' => ['required_unless:todo_el_dia,1', 'nullable', 'date_format:H:i'],
            'hora_fin' => ['required_unless:todo_el_dia,1', 'nullable', 'date_format:H:i'],
        ], [
            'fecha_inicio.required' => 'Indica la fecha de inicio.',
            'fecha_inicio.date_format' => 'La fecha de inicio no es válida.',
            'fecha_fin.required' => 'Indica la fecha de fin.',
            'fecha_fin.date_format' => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'hora_inicio.required_unless' => 'Indica la hora de inicio.',
            'hora_inicio.date_format' => 'La hora de inicio no es válida.',
            'hora_fin.required_unless' => 'Indica la hora de fin.',
            'hora_fin.date_format' => 'La hora de fin no es válida.',
            'motivo.max' => 'El motivo no puede pasar de 120 caracteres.',
        ]);

        $tz = config('app.timezone');
        $todo = $request->boolean('todo_el_dia');

        if ($todo) {
            $inicio = Carbon::createFromFormat('Y-m-d', $request->input('fecha_inicio'), $tz)->setTime(0, 0, 0);
            $fin = Carbon::createFromFormat('Y-m-d', $request->input('fecha_fin'), $tz)->setTime(23, 59, 59);
        } else {
            $inicio = Carbon::createFromFormat('Y-m-d H:i', $request->input('fecha_inicio') . ' ' . $request->input('hora_inicio'), $tz);
            $fin = Carbon::createFromFormat('Y-m-d H:i', $request->input('fecha_fin') . ' ' . $request->input('hora_fin'), $tz);
        }

        if ($fin->lte($inicio)) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora de fin debe ser posterior a la de inicio.',
            ]);
        }

        if ($fin->lte(now())) {
            throw ValidationException::withMessages([
                'fecha_fin' => 'El bloqueo debe terminar en el futuro.',
            ]);
        }

        $motivo = trim((string) $request->input('motivo'));

        return [$inicio, $fin, $motivo !== '' ? $motivo : null];
    }

    /**
     * Texto de aviso si el bloqueo se cruza con citas pendientes.
     */
    private function avisoCitas(int $userId, Carbon $inicio, Carbon $fin): string
    {
        $n = Cita::where('user_id', $userId)
            ->where('estado', 'pendiente')
            ->where('inicio', '<', $fin)
            ->where('fin', '>', $inicio)
            ->count();

        if ($n === 0) {
            return '';
        }

        return $n === 1
            ? ' Ojo: hay 1 cita pendiente en ese rango; no se canceló automáticamente.'
            : " Ojo: hay $n citas pendientes en ese rango; no se cancelaron automáticamente.";
    }

    /**
     * Prepara un bloqueo para la vista.
     */
    private function presentar($b): array
    {
        $ini = Carbon::parse($b->inicio)->locale('es');
        $fin = Carbon::parse($b->fin)->locale('es');

        $todo = $ini->format('H:i') === '00:00' && $fin->format('H:i') === '23:59';
        $mismoDia = $ini->isSameDay($fin);

        $f = fn (Carbon $c) => ucfirst($c->translatedFormat('D j M'));

        if ($todo) {
            $texto = $mismoDia ? $f($ini) : $f($ini) . ' → ' . $f($fin);
            $detalle = 'Todo el día';
        } elseif ($mismoDia) {
            $texto = $f($ini);
            $detalle = $ini->format('H:i') . ' – ' . $fin->format('H:i');
        } else {
            $texto = $f($ini) . ' ' . $ini->format('H:i') . ' → ' . $f($fin) . ' ' . $fin->format('H:i');
            $detalle = '';
        }

        return [
            'id' => $b->id,
            'motivo' => $b->motivo ?: 'Bloqueo',
            'texto' => $texto,
            'detalle' => $detalle,
            'en_curso' => $ini->lte(now()),
            'editar' => [
                'id' => $b->id,
                'motivo' => $b->motivo,
                'fecha_inicio' => $ini->format('Y-m-d'),
                'fecha_fin' => $fin->format('Y-m-d'),
                'todo_el_dia' => $todo,
                'hora_inicio' => $todo ? '12:00' : $ini->format('H:i'),
                'hora_fin' => $todo ? '13:00' : $fin->format('H:i'),
            ],
        ];
    }
}