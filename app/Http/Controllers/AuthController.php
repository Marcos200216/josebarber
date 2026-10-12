<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const LOGIN_MAX = 6;        // fallos de acceso por minuto (por correo + IP)
    private const REGISTRO_MAX = 10;    // intentos de registro por minuto (por IP)
    private const VENTANA_SEGUNDOS = 60;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $clave = 'login:' . Str::lower((string) $request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::LOGIN_MAX)) {
            return back()->withErrors(['acceso' => $this->textoEspera($clave)])->onlyInput('email');
        }

        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido, por ejemplo nombre@correo.com.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        if (! Auth::attempt($datos, $request->boolean('recordar'))) {
            RateLimiter::hit($clave, self::VENTANA_SEGUNDOS);

            return back()->withErrors(['acceso' => 'Correo o contraseña incorrectos.'])->onlyInput('email');
        }

        if (! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['acceso' => 'Esta cuenta está desactivada.'])->onlyInput('email');
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return redirect()->intended('/panel');
    }

    public function showRegister()
    {
        return view('auth.registro');
    }

    public function register(Request $request)
    {
        $clave = 'registro:' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::REGISTRO_MAX)) {
            return back()->withErrors(['acceso' => $this->textoEspera($clave)])->onlyInput('name', 'email');
        }

        RateLimiter::hit($clave, self::VENTANA_SEGUNDOS);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Escribe tu nombre.',
            'name.max' => 'Tu nombre es muy largo (máximo 100 letras).',
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido, por ejemplo nombre@correo.com.',
            'email.max' => 'Ese correo es muy largo.',
            'email.unique' => 'Ese correo ya está registrado. Inicia sesión o usa otro.',
            'password.required' => 'Escribe una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = new User($datos);
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/panel');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function textoEspera(string $clave): string
    {
        $segundos = max(1, RateLimiter::availableIn($clave));

        return 'Demasiados intentos. Espera ' . $segundos . ' segundo' . ($segundos === 1 ? '' : 's') . ' e inténtalo de nuevo.';
    }
}