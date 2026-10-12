<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HorarioController extends Controller
{
    /** Orden de presentación (lunes a domingo) con la numeración de Carbon. */
    private const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        0 => 'Domingo',
    ];

    private const FORMATO_HORA = '/^([01]\d|2[0-3]):[0-5]\d$/';

    public function index(Request $request): View
    {
        $guardados = $request->user()->horarios()->get()->keyBy('dia_semana');
        $hayGuardados = $guardados->isNotEmpty();

        $dias = [];
        foreach (self::DIAS as $num => $nombre) {
            $h = $guardados->get($num);

            if ($h) {
                $tienePausa = filled($h->pausa_inicio) && filled($h->pausa_fin);

                $dias[$num] = [
                    'nombre' => $nombre,
                    'trabaja' => (bool) $h->trabaja,
                    'inicio' => substr((string) $h->hora_inicio, 0, 5) ?: '09:00',
                    'fin' => substr((string) $h->hora_fin, 0, 5) ?: '18:00',
                    'pausa' => $tienePausa,
                    'pausa_inicio' => $tienePausa ? substr((string) $h->pausa_inicio, 0, 5) : '12:00',
                    'pausa_fin' => $tienePausa ? substr((string) $h->pausa_fin, 0, 5) : '13:00',
                ];
            } else {
                // Sugerencia inicial mientras no haya nada guardado: lunes a sábado, con pausa al mediodía.
                $trabaja = ! $hayGuardados && $num !== 0;

                $dias[$num] = [
                    'nombre' => $nombre,
                    'trabaja' => $trabaja,
                    'inicio' => '09:00',
                    'fin' => '18:00',
                    'pausa' => $trabaja,
                    'pausa_inicio' => '12:00',
                    'pausa_fin' => '13:00',
                ];
            }
        }

        return view('barberos.horario', [
            'dias' => $dias,
            'hayGuardados' => $hayGuardados,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $entrada = $request->input('dias', []);
        $errores = [];
        $limpio = [];

        foreach (self::DIAS as $num => $nombre) {
            $fila = is_array($entrada[$num] ?? null) ? $entrada[$num] : [];
            $trabaja = ! empty($fila['trabaja']);
            $inicio = $fila['inicio'] ?? null;
            $fin = $fila['fin'] ?? null;
            $pausa = $trabaja && ! empty($fila['pausa']);
            $pausaInicio = $fila['pausa_inicio'] ?? null;
            $pausaFin = $fila['pausa_fin'] ?? null;

            if ($trabaja) {
                $horarioValido = false;

                if (! is_string($inicio) || ! is_string($fin)
                    || ! preg_match(self::FORMATO_HORA, $inicio) || ! preg_match(self::FORMATO_HORA, $fin)) {
                    $errores["dias.$num"] = "$nombre: indica una hora de entrada y una de salida válidas.";
                } elseif ($fin <= $inicio) {
                    $errores["dias.$num"] = "$nombre: la hora de salida debe ser posterior a la de entrada.";
                } else {
                    $horarioValido = true;
                }

                if ($horarioValido && $pausa) {
                    if (! is_string($pausaInicio) || ! is_string($pausaFin)
                        || ! preg_match(self::FORMATO_HORA, $pausaInicio) || ! preg_match(self::FORMATO_HORA, $pausaFin)) {
                        $errores["dias.$num"] = "$nombre: indica una hora de inicio y una de fin válidas para la pausa.";
                    } elseif ($pausaFin <= $pausaInicio) {
                        $errores["dias.$num"] = "$nombre: el fin de la pausa debe ser posterior a su inicio.";
                    } elseif ($pausaInicio < $inicio || $pausaFin > $fin) {
                        $errores["dias.$num"] = "$nombre: la pausa debe quedar dentro de tu horario de trabajo ($inicio a $fin).";
                    }
                }
            }

            $limpio[$num] = [
                'trabaja' => $trabaja,
                'inicio' => $inicio,
                'fin' => $fin,
                'pausa' => $pausa,
                'pausa_inicio' => $pausaInicio,
                'pausa_fin' => $pausaFin,
            ];
        }

        if ($errores) {
            return back()->withErrors($errores)->withInput();
        }

        $user = $request->user();

        DB::transaction(function () use ($user, $limpio) {
            $existentes = $user->horarios()->get()->keyBy('dia_semana');

            foreach ($limpio as $num => $d) {
                $h = $existentes->get($num) ?? new Horario();

                $inicioPrevio = $h->exists ? substr((string) $h->hora_inicio, 0, 5) : null;
                $finPrevio = $h->exists ? substr((string) $h->hora_fin, 0, 5) : null;

                $h->forceFill([
                    'user_id' => $user->id,
                    'dia_semana' => $num,
                    'trabaja' => $d['trabaja'],
                    'hora_inicio' => $d['trabaja'] ? $d['inicio'] : ($inicioPrevio ?: '09:00'),
                    'hora_fin' => $d['trabaja'] ? $d['fin'] : ($finPrevio ?: '18:00'),
                    'pausa_inicio' => $d['pausa'] ? $d['pausa_inicio'] : null,
                    'pausa_fin' => $d['pausa'] ? $d['pausa_fin'] : null,
                ])->save();
            }
        });

        return redirect()
            ->route('horario')
            ->with('ok', 'Tu horario semanal se guardó correctamente.');
    }
}