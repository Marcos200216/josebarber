<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recordatorios de 24 h y 2 h antes de cada cita (cada 5 minutos).
Schedule::command('citas:recordatorios')
    ->everyFiveMinutes()
    ->withoutOverlapping();