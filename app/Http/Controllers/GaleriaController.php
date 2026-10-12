<?php

namespace App\Http\Controllers;

use App\Models\GaleriaBarbero;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GaleriaController extends Controller
{
    private const MAX_FOTOS = 12;

    public function index(Request $request): View
    {
        $fotos = GaleriaBarbero::where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($f) => [
                'id' => $f->id,
                'url' => asset('storage/' . $f->imagen_path),
                'descripcion' => $f->descripcion,
            ]);

        return view('barberos.galeria', [
            'fotos' => $fotos,
            'max' => self::MAX_FOTOS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'descripcion' => ['nullable', 'string', 'max:120'],
        ], [
            'imagen.required' => 'Elige una foto para subir.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'imagen.max' => 'La foto es demasiado pesada (máximo 4 MB).',
            'imagen.uploaded' => 'No se pudo subir la foto. Prueba con una más liviana.',
            'descripcion.max' => 'La descripción no puede pasar de 120 caracteres.',
        ]);

        $user = $request->user();

        if (GaleriaBarbero::where('user_id', $user->id)->count() >= self::MAX_FOTOS) {
            return back()
                ->withErrors(['imagen' => 'Ya llegaste al máximo de ' . self::MAX_FOTOS . ' fotos. Elimina una para agregar otra.'])
                ->withInput();
        }

        $path = $request->file('imagen')->store('galeria', 'public');
        $descripcion = trim((string) $request->input('descripcion'));

        $foto = new GaleriaBarbero();
        $foto->forceFill([
            'user_id' => $user->id,
            'imagen_path' => $path,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
        ])->save();

        return redirect()->route('galeria')->with('ok', 'Foto agregada a tu galería.');
    }

        public function update(Request $request, int $foto): RedirectResponse
    {
        $request->validate([
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'descripcion' => ['nullable', 'string', 'max:120'],
        ], [
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'imagen.max' => 'La foto es demasiado pesada (máximo 4 MB).',
            'imagen.uploaded' => 'No se pudo subir la foto. Prueba con una más liviana.',
            'descripcion.max' => 'La descripción no puede pasar de 120 caracteres.',
        ]);

        $modelo = GaleriaBarbero::where('user_id', $request->user()->id)->findOrFail($foto);
        $descripcion = trim((string) $request->input('descripcion'));
        $anterior = null;

        $cambios = ['descripcion' => $descripcion !== '' ? $descripcion : null];

        if ($request->hasFile('imagen')) {
            $anterior = $modelo->imagen_path;
            $cambios['imagen_path'] = $request->file('imagen')->store('galeria', 'public');
        }

        $modelo->forceFill($cambios)->save();

        // Se borra la foto vieja solo después de guardar la nueva.
        if ($anterior) {
            Storage::disk('public')->delete($anterior);
        }

        return redirect()
            ->route('galeria')
            ->with('ok', $anterior ? 'Foto actualizada.' : 'Descripción actualizada.');
    }

    public function destroy(Request $request, int $foto): RedirectResponse
    {
        $modelo = GaleriaBarbero::where('user_id', $request->user()->id)->findOrFail($foto);

        Storage::disk('public')->delete($modelo->imagen_path);
        $modelo->delete();

        return redirect()->route('galeria')->with('ok', 'Foto eliminada.');
    }
}