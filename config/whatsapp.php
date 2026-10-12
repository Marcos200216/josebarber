<?php

return [
    // simulado = guarda el mensaje en la base de datos y en el log, sin enviarlo.
    // evolution = envío real con Evolution API.
    'driver' => env('WHATSAPP_DRIVER', 'simulado'),

    // Evolution API (WhatsApp real)
    'url' => env('EVOLUTION_URL', 'http://localhost:8081'),
    'instance' => env('EVOLUTION_INSTANCE', 'barberia'),
    'api_key' => env('EVOLUTION_API_KEY'),
];