<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function index()
    {
        $solicitudes = User::where('status', 'pendiente')
            ->withCount('cortes')
            ->orderBy('updated_at')
            ->get();

        return view('barberos.solicitudes', compact('solicitudes'));
    }

    public function aceptar(User $user)
    {
        abort_unless($user->status === 'pendiente', 404);

        $user->status = 'aceptado';
        $user->rejection_reason = null;
        $user->save();

        return back()->with('ok', 'Barbero aceptado. Ya aparece a los clientes.');
    }

    public function rechazar(Request $request, User $user)
    {
        abort_unless($user->status === 'pendiente', 404);

        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo.required' => 'Escribe el motivo del rechazo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $user->status = 'rechazado';
        $user->rejection_reason = $datos['motivo'];
        $user->save();

        return back()->with('ok', 'Solicitud rechazada.');
    }
}