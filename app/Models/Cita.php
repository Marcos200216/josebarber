<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Cita extends Model
{
    protected $guarded = [];
    protected $hidden = ['pin_hash', 'token'];

    protected function casts(): array
    {
        return [
            'inicio' => 'datetime',
            'fin' => 'datetime',
            'slot_activo' => 'datetime',
            'recordatorio_24h_at' => 'datetime',
            'recordatorio_2h_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cita $cita) {
            $cita->token = $cita->token ?: Str::random(48);
        });

        static::saving(function (Cita $cita) {
            // Si cambia la hora de una cita existente, se vuelven a enviar los recordatorios
            if ($cita->exists && $cita->isDirty('inicio')) {
                $cita->recordatorio_24h_at = null;
                $cita->recordatorio_2h_at = null;
            }

            $cita->slot_activo = $cita->estado === 'pendiente' ? $cita->inicio : null;
        });
    }

    /** Enlace privado para ver, cambiar o cancelar la cita sin PIN. */
    public function urlEnlace(): string
    {
        return route('cita.enlace', ['token' => $this->token]);
    }

    public function barbero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function corte(): BelongsTo
    {
        return $this->belongsTo(Corte::class);
    }
}