<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use Illuminate\Database\Seeder;

class AjustesSeeder extends Seeder
{
    public function run(): void
    {
        $valores = [
            'mostrar_ubicacion' => '1',
            'footer_texto' => 'Cortes con cita, sin filas y sin complicaciones.',
            'footer_direccion' => 'Dirección de ejemplo, San José, Costa Rica',
            'footer_mapa_url' => 'https://maps.google.com/',
            'footer_horario' => 'Lunes a sábado, 9:00 am a 6:00 pm',
            'footer_enlaces' => json_encode([
                ['tipo' => 'instagram', 'etiqueta' => '@ejemplo', 'url' => 'https://instagram.com/ejemplo'],
                ['tipo' => 'whatsapp', 'etiqueta' => 'WhatsApp', 'url' => 'https://wa.me/50600000000'],
            ], JSON_UNESCAPED_UNICODE),
        ];

        foreach ($valores as $clave => $valor) {
            Ajuste::firstOrCreate(['clave' => $clave], ['valor' => $valor]);
        }
    }
}