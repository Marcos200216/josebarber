<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de cortes (solo lo administra el admin)
        Schema::create('cortes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->unsignedSmallInteger('duracion_minutos')->default(30);
            $table->string('imagen_path')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        // Qué cortes hace cada barbero
        Schema::create('barbero_corte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corte_id')->constrained('cortes')->cascadeOnDelete();
            $table->unique(['user_id', 'corte_id']);
        });

        // Horario semanal (0 = domingo ... 6 = sábado)
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->boolean('trabaja')->default(true);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->unique(['user_id', 'dia_semana']);
        });

        // Bloqueos puntuales: almuerzo, trámites, vacaciones
        Schema::create('bloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('motivo')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'inicio']);
        });

        // Citas
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('corte_id')->constrained('cortes')->restrictOnDelete();
            $table->string('cliente_nombre');
            $table->string('cliente_telefono', 20);
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->enum('estado', ['pendiente', 'completada', 'cancelada', 'no_asistio'])->default('pendiente');
            $table->string('token', 64)->unique();
            $table->dateTime('slot_activo')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'slot_activo']);
            $table->index(['user_id', 'inicio']);
            $table->index('cliente_telefono');
        });

        // "Mejores cortes" del perfil de cada barbero
        Schema::create('galeria_barbero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('imagen_path');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Configuración general editable por el admin (footer, ubicación, plantillas de WhatsApp)
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        // Registro de mensajes de WhatsApp
        Schema::create('mensajes_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->string('telefono', 20);
            $table->string('tipo');
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->text('respuesta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_log');
        Schema::dropIfExists('ajustes');
        Schema::dropIfExists('galeria_barbero');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('bloqueos');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('barbero_corte');
        Schema::dropIfExists('cortes');
    }
};
