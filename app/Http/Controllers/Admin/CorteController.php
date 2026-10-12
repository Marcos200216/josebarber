<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Corte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CorteController extends Controller
{
    public function index()
    {
        $cortes = Corte::orderBy('orden')->orderBy('nombre')->get();

        return view('barberos.cortes', compact('cortes'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        if ($request->hasFile('imagen')) {
            $datos['imagen_path'] = $request->file('imagen')->store('cortes', 'public');
        }

        unset($datos['imagen'], $datos['quitar_imagen']);
        $datos['activo'] = $request->boolean('activo');
        $datos['orden'] = $datos['orden'] ?? 0;

        Corte::create($datos);

        return redirect()->route('admin.cortes.index')->with('ok', 'Corte creado correctamente.');
    }

    public function update(Request $request, Corte $corte)
    {
        $datos = $this->validar($request);

        if ($request->hasFile('imagen')) {
            $this->borrarImagen($corte);
            $datos['imagen_path'] = $request->file('imagen')->store('cortes', 'public');
        } elseif ($request->boolean('quitar_imagen')) {
            $this->borrarImagen($corte);
            $datos['imagen_path'] = null;
        }

        unset($datos['imagen'], $datos['quitar_imagen']);
        $datos['activo'] = $request->boolean('activo');
        $datos['orden'] = $datos['orden'] ?? 0;

        $corte->update($datos);

        return redirect()->route('admin.cortes.index')->with('ok', 'Corte actualizado correctamente.');
    }

    public function toggle(Corte $corte)
    {
        $corte->update(['activo' => ! $corte->activo]);

        return back()->with('ok', $corte->activo ? 'Corte activado.' : 'Corte desactivado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'duracion_minutos' => ['required', 'integer', 'min:5', 'max:480'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_imagen' => ['nullable', 'boolean'],
        ]);
    }

    private function borrarImagen(Corte $corte): void
    {
        if ($corte->imagen_path) {
            Storage::disk('public')->delete($corte->imagen_path);
        }
    }
}