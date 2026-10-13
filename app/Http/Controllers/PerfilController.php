<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_foto' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => 'El teléfono debe tener 8 dígitos (Costa Rica).',
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

    /** Cambiar el correo (pide la contraseña actual). */
    public function correo(Request $request)
    {
        $user = $request->user();

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $datos = $request->validateWithBag('correo', [
            'email' => [
                'required', 'string', 'email', 'max:255',
                'not_in:' . Str::lower($user->email),
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['required', 'current_password'],
        ], [
            'email.required' => 'Escribe tu correo nuevo.',
            'email.email' => 'Ese correo no parece válido.',
            'email.max' => 'Ese correo es muy largo.',
            'email.not_in' => 'Ese ya es tu correo actual.',
            'email.unique' => 'Ese correo ya está en uso.',
            'current_password.required' => 'Escribe tu contraseña actual para confirmar.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
        ]);

        $user->forceFill(['email' => $datos['email']])->save();

        return redirect()->route('perfil')->with('ok', 'Correo actualizado.');
    }

    /** Cambiar la contraseña (actual + nueva + confirmación). */
    public function clave(Request $request)
    {
        $user = $request->user();

        $datos = $request->validateWithBag('clave', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Escribe tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Escribe tu contraseña nueva.',
            'password.min' => 'La contraseña nueva debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña nueva es muy larga (máximo 72 caracteres).',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password.different' => 'La contraseña nueva debe ser distinta a la actual.',
        ]);

        $user->forceFill([
            'password' => Hash::make($datos['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('perfil')->with('ok', 'Contraseña actualizada.');
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