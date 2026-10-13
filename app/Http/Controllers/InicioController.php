<?php

namespace App\Http\Controllers;

use App\Models\Corte;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function index(): View
    {
        return view('clientes.index', $this->datos());
    }

    public function datos(): array
    {
        // Cortes activos con al menos un barbero visible (aceptado + activo)
        // que tenga algún día de trabajo en su horario.
        $cortes = Corte::activos()
            ->whereHas('barberos', function ($q) {
                $q->visibles()->whereHas('horarios', fn ($h) => $h->where('trabaja', true));
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Corte $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'descripcion' => $c->descripcion,
                'precio' => '₡' . number_format($c->precio, 0, ',', ' '),
                'duracion' => (int) $c->duracion_minutos,
                'imagen' => $c->imagen_url,
            ])
            ->values()
            ->all();

        return [
            'marca' => config('app.name'),
            'cortes' => $cortes,
        ];
    }
}