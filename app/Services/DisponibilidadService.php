<?php

namespace App\Services;

use App\Models\Corte;
use App\Models\User;
use Carbon\Carbon;

class DisponibilidadService
{
       public const DIAS_ADELANTE = 30;      // hoy + 29 = 30 días reservables
    public const MARGEN_MINUTOS = 0;     // anticipación mínima
    public const INTERVALO_MINUTOS = 60;  // una hora ofrecida cada 60 min

    /**
     * Horas libres ("H:i") de un barbero para un corte en un día.
     * Horario del día − pausa − bloqueos − citas pendientes (por traslape).
     * Si se pasa $ignorarCitaId, esa cita no cuenta como ocupada (sirve para moverla).
     * No revisa si el barbero es visible: eso lo decide el controlador.
     */
    public function horasLibres(User $barbero, Corte $corte, Carbon $dia, ?int $ignorarCitaId = null): array
    {
        $tz = config('app.timezone');
        $ahora = now($tz);
        $dia = $dia->copy()->setTimezone($tz)->startOfDay();

        // Fuera de la ventana reservable
        if ($dia->lt($ahora->copy()->startOfDay())
            || $dia->gt($ahora->copy()->addDays(self::DIAS_ADELANTE - 1)->startOfDay())) {
            return [];
        }

        $horario = $barbero->horarios()->where('dia_semana', $dia->dayOfWeek)->first();

        if (! $horario || ! $horario->trabaja) {
            return [];
        }

        $fecha = $dia->format('Y-m-d');
        $jornadaIni = Carbon::parse($fecha . ' ' . $horario->hora_inicio, $tz);
        $jornadaFin = Carbon::parse($fecha . ' ' . $horario->hora_fin, $tz);
        $diaIni = $dia->copy();
        $diaFin = $dia->copy()->endOfDay();

        // Intervalos ocupados: [inicio, fin]
        $ocupados = [];

        if ($horario->pausa_inicio && $horario->pausa_fin) {
            $ocupados[] = [
                Carbon::parse($fecha . ' ' . $horario->pausa_inicio, $tz),
                Carbon::parse($fecha . ' ' . $horario->pausa_fin, $tz),
            ];
        }

        foreach ($barbero->bloqueos()->where('inicio', '<', $diaFin)->where('fin', '>', $diaIni)->get() as $b) {
            $ocupados[] = [$b->inicio, $b->fin];
        }

        $citas = $barbero->citas()
            ->where('estado', 'pendiente')
            ->when($ignorarCitaId, fn ($q) => $q->where('id', '!=', $ignorarCitaId))
            ->where('inicio', '<', $diaFin)
            ->where('fin', '>', $diaIni)
            ->get(['inicio', 'fin']);

        foreach ($citas as $c) {
            $ocupados[] = [$c->inicio, $c->fin];
        }

        $minimo = $ahora->copy()->addMinutes(self::MARGEN_MINUTOS);
        $duracion = (int) $corte->duracion_minutos;
        $libres = [];

        for ($ini = $jornadaIni->copy(); $ini->copy()->addMinutes($duracion)->lte($jornadaFin); $ini->addMinutes(self::INTERVALO_MINUTOS)) {
            if ($ini->lt($minimo)) {
                continue;
            }

            $fin = $ini->copy()->addMinutes($duracion);
            $choca = false;

            foreach ($ocupados as [$oIni, $oFin]) {
                if ($ini->lt($oFin) && $fin->gt($oIni)) {
                    $choca = true;
                    break;
                }
            }

            if (! $choca) {
                $libres[] = $ini->format('H:i');
            }
        }

        return $libres;
    }

    /**
     * Primer espacio libre (Carbon) de un barbero para un corte, o null.
     */
    public function proximoEspacio(User $barbero, Corte $corte): ?Carbon
    {
        $tz = config('app.timezone');
        $hoy = now($tz)->startOfDay();

        for ($i = 0; $i < self::DIAS_ADELANTE; $i++) {
            $dia = $hoy->copy()->addDays($i);
            $horas = $this->horasLibres($barbero, $corte, $dia);

            if ($horas) {
                return Carbon::parse($dia->format('Y-m-d') . ' ' . $horas[0], $tz);
            }
        }

        return null;
    }
}