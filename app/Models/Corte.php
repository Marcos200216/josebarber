<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Corte extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'precio' => 'decimal:2'];
    }

    public function barberos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'barbero_corte');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen_path ? asset('storage/' . $this->imagen_path) : null;
    }
}