<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function index(Request $request)
    {
        return view('barberos.perfil', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->merge(['phone' => $this->normalizarTelefono($request->input('phone'))]);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^\+506\d{8}$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location_address' => ['nullable', 'string', 'max:255'],
            'location_url' => [
                'nullable', 'url', 'max:255',
                'regex:~^https?://((www\.)?google\.[a-z.]+/maps|maps\.google\.[a-z.]+|maps\.app\.goo\.gl|goo\.gl/maps)~i',
            ],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_foto' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => 'El teléfono debe tener 8 dígitos (Costa Rica).',
            'location_url.url' => 'El enlace de ubicación no es válido.',
            'location_url.regex' => 'El enlace debe ser de Google Maps.',
        ]);

        if ($request->hasFile('foto')) {
            $this->borrarFoto($user->photo_path);
            $datos['photo_path'] = $request->file('foto')->store('perfiles', 'public');
        } elseif ($request->boolean('quitar_foto')) {
            $this->borrarFoto($user->photo_path);
            $datos['photo_path'] = null;
        }

        unset($datos['foto'], $datos['quitar_foto']);

        $user->fill($datos)->save();

        return redirect()->route('perfil')->with('ok', 'Perfil actualizado.');
    }

    private function normalizarTelefono(?string $valor): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $valor);

        if ($digitos === '') {
            return null;
        }

        if (strlen($digitos) === 11 && str_starts_with($digitos, '506')) {
            $digitos = substr($digitos, 3);
        }

        return strlen($digitos) === 8 ? '+506' . $digitos : $digitos;
    }

    private function borrarFoto(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }
    }
}