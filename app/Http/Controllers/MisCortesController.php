<?php

namespace App\Http\Controllers;

use App\Models\Corte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MisCortesController extends Controller
{
    public function index(Request $request): View
    {
        $cortes = Corte::activos()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $seleccionados = $request->user()
            ->cortes()
            ->pluck('cortes.id')
            ->all();

        return view('barberos.mis-cortes', [
            'cortes' => $cortes,
            'seleccionados' => $seleccionados,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'cortes' => ['nullable', 'array'],
            'cortes.*' => ['integer', 'distinct'],
        ]);

        $user = $request->user();

        // Solo se aceptan cortes que existan y estén activos.
        $elegidos = Corte::activos()
            ->whereIn('id', $datos['cortes'] ?? [])
            ->pluck('id');

        // Se conservan los vínculos con cortes hoy desactivados por el admin,
        // para que reaparezcan marcados si los vuelve a activar.
        $inactivosActuales = $user->cortes()
            ->where('cortes.activo', false)
            ->pluck('cortes.id');

        $user->cortes()->sync($elegidos->merge($inactivosActuales)->all());

        return redirect()
            ->route('mis-cortes')
            ->with('ok', 'Tus cortes se guardaron correctamente.');
    }
}