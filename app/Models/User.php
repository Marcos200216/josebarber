<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'bio',
        'photo_path', 'location_address', 'location_url',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function scopeVisibles($query)
    {
        return $query->where('status', 'aceptado')->where('is_active', true);
    }

    public function cortes(): BelongsToMany
    {
        return $this->belongsToMany(Corte::class, 'barbero_corte');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function galeria(): HasMany
    {
        return $this->hasMany(GaleriaBarbero::class);
    }

        public function getFotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}