<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class EnviarRecordatorios extends Command
{
    protected $signature = 'citas:recordatorios {--simular : Solo muestra qué enviaría, sin enviar ni marcar nada}';

    protected $description = 'Envía por WhatsApp los recordatorios de 24 h y 2 h antes de cada cita pendiente';

    /** Ventana en horas (desde, hasta) antes de la cita en la que sale cada aviso. */
    private const AVISOS = [
        '24h' => [23, 24],
        '2h' => [1, 2],
    ];

    public function handle(WhatsAppService $whatsapp): int
    {
        $simular = (bool) $this->option('simular');
        $total = 0;

        foreach (self::AVISOS as $aviso => [$desdeHoras, $hastaHoras]) {
            $columna = "recordatorio_{$aviso}_at";

            $citas = Cita::with(['barbero', 'corte'])
                ->where('estado', 'pendiente')
                ->whereNull($columna)
                ->whereBetween('inicio', [now()->addHours($desdeHoras), now()->addHours($hastaHoras)])
                ->get();

            foreach ($citas as $cita) {
                if ($simular) {
                    $this->line("[simulación] {$aviso} → cita {$cita->id} ({$cita->cliente_nombre}, {$cita->inicio->format('d/m H:i')})");
                    $total++;
                    continue;
                }

                // Se marca ANTES de enviar y solo si nadie más la marcó: así nunca se repite el aviso.
                $reclamada = Cita::whereKey($cita->id)->whereNull($columna)->update([$columna => now()]);

                if ($reclamada === 0) {
                    continue;
                }

                $whatsapp->recordatorio($cita, $aviso);
                $total++;
            }
        }

        $this->info($simular
            ? "Se enviarían {$total} recordatorio(s)."
            : "Recordatorios procesados: {$total}.");

        return self::SUCCESS;
    }
}