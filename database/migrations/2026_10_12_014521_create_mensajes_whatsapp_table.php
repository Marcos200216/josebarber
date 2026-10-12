<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cita_id')->nullable()->index();
            $table->string('telefono', 20)->nullable();
            $table->string('tipo', 40);
            $table->text('texto');
            // simulado | enviado | fallido | omitido
            $table->string('estado', 20)->default('simulado')->index();
            $table->string('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_whatsapp');
    }
};