<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    protected function casts(): array
    {
        return ['trabaja' => 'boolean'];
    }
}