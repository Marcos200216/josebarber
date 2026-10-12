<?php

use App\Http\Controllers\Admin\CorteController;
use App\Http\Controllers\Admin\SolicitudController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BloqueoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\GaleriaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MiCitaController;
use App\Http\Controllers\MisCortesController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InicioController::class, 'index'])->name('inicio');
// Enlace privado de la cita (sin PIN): abre "Tus citas" en el inicio.
Route::get('cita/{token}', [MiCitaController::class, 'enlace'])->middleware('throttle:60,1,cita-enlace')->name('cita.enlace');

// Reserva pública (el cliente no inicia sesión)
Route::prefix('reservar')->name('reserva.')->middleware('throttle:60,1')->group(function () {
    Route::get('corte/{corte}/barberos', [ReservaController::class, 'barberos'])->whereNumber('corte')->name('barberos');
    Route::get('disponibilidad', [ReservaController::class, 'disponibilidad'])->name('disponibilidad');
    Route::get('primer-espacio', [ReservaController::class, 'primerEspacio'])->name('primer');
    Route::post('/', [ReservaController::class, 'store'])->middleware('throttle:8,1,reserva-store')->name('store');

    // Ver mi cita (teléfono + PIN). Cada POST tiene su propio contador de intentos.
       // Ver mi cita (teléfono + PIN). Cada POST tiene su propio contador de intentos.
    Route::prefix('mi-cita')->name('mi-cita.')->group(function () {
        Route::get('/', [MiCitaController::class, 'index'])->name('index');
        Route::post('verificar', [MiCitaController::class, 'verificar'])->middleware('throttle:20,1,mi-cita-verificar')->name('verificar');
                Route::post('olvide-codigo', [MiCitaController::class, 'olvideCodigo'])->middleware('throttle:10,1,mi-cita-olvide')->name('olvide');
        Route::post('salir', [MiCitaController::class, 'salir'])->name('salir');
        Route::post('{cita}/cancelar', [MiCitaController::class, 'cancelar'])->whereNumber('cita')->middleware('throttle:20,1,mi-cita-accion')->name('cancelar');

        Route::get('{cita}/barberos', [MiCitaController::class, 'barberos'])->whereNumber('cita')->name('barberos');
        Route::get('{cita}/disponibilidad', [MiCitaController::class, 'disponibilidad'])->whereNumber('cita')->name('disponibilidad');
        Route::post('{cita}/cambiar', [MiCitaController::class, 'cambiar'])->whereNumber('cita')->middleware('throttle:20,1,mi-cita-accion')->name('cambiar');
    });
});

Route::prefix('barbero')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/registro', [AuthController::class, 'showRegister'])->name('registro');
        Route::post('/registro', [AuthController::class, 'register']);
    });

    Route::post('/salir', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::middleware('auth')->prefix('panel')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('panel');
    Route::post('solicitud', [PanelController::class, 'enviarSolicitud'])->name('panel.solicitud');
    Route::post('motivo-visto', [PanelController::class, 'motivoVisto'])->name('panel.motivo');

    Route::get('perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');

    Route::get('mis-cortes', [MisCortesController::class, 'index'])->name('mis-cortes');
    Route::put('mis-cortes', [MisCortesController::class, 'update'])->name('mis-cortes.update');

    Route::get('horario', [HorarioController::class, 'index'])->name('horario');
    Route::put('horario', [HorarioController::class, 'update'])->name('horario.update');

    Route::get('bloqueos', [BloqueoController::class, 'index'])->name('bloqueos');
    Route::post('bloqueos', [BloqueoController::class, 'store'])->name('bloqueos.store');
    Route::put('bloqueos/{bloqueo}', [BloqueoController::class, 'update'])->whereNumber('bloqueo')->name('bloqueos.update');
    Route::delete('bloqueos/{bloqueo}', [BloqueoController::class, 'destroy'])->whereNumber('bloqueo')->name('bloqueos.destroy');

    Route::get('galeria', [GaleriaController::class, 'index'])->name('galeria');
    Route::post('galeria', [GaleriaController::class, 'store'])->middleware('throttle:30,1')->name('galeria.store');
    Route::put('galeria/{foto}', [GaleriaController::class, 'update'])->whereNumber('foto')->name('galeria.update');
    Route::delete('galeria/{foto}', [GaleriaController::class, 'destroy'])->whereNumber('foto')->name('galeria.destroy');

    Route::get('citas', [CitaController::class, 'index'])->name('citas');
    Route::patch('citas/{cita}/completar', [CitaController::class, 'completar'])->whereNumber('cita')->name('citas.completar');
    Route::patch('citas/{cita}/no-asistio', [CitaController::class, 'noAsistio'])->whereNumber('cita')->name('citas.no-asistio');
    Route::patch('citas/{cita}/cancelar', [CitaController::class, 'cancelar'])->whereNumber('cita')->name('citas.cancelar');
    Route::put('citas/{cita}/hora', [CitaController::class, 'reprogramar'])->whereNumber('cita')->name('citas.hora');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('cortes', [CorteController::class, 'index'])->name('cortes.index');
        Route::post('cortes', [CorteController::class, 'store'])->name('cortes.store');
        Route::put('cortes/{corte}', [CorteController::class, 'update'])->name('cortes.update');
        Route::patch('cortes/{corte}/activo', [CorteController::class, 'toggle'])->name('cortes.toggle');

        Route::get('solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::patch('solicitudes/{user}/aceptar', [SolicitudController::class, 'aceptar'])->name('solicitudes.aceptar');
        Route::patch('solicitudes/{user}/rechazar', [SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');
    });
});