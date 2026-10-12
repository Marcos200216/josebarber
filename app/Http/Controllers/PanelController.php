<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $motivo = $user->status === 'rechazado' ? $user->rejection_reason : null;

        $requisitos = $this->requisitos($user);
        $completo = collect($requisitos)->every(fn ($r) => $r['ok']);

        return view('barberos.index', compact('user', 'motivo', 'requisitos', 'completo'));
    }

    public function enviarSolicitud(Request $request)
    {
        $user = $request->user();

        if (! in_array($user->status, ['borrador', 'rechazado'], true)) {
            return redirect()->route('panel');
        }

        $faltan = collect($this->requisitos($user))
            ->reject(fn ($r) => $r['ok'])
            ->pluck('texto');

        if ($faltan->isNotEmpty()) {
            return redirect()
                ->route('panel')
                ->withErrors([
                    'solicitud' => 'Aún no puedes enviar la solicitud. Falta: ' . $faltan->implode(', ') . '.',
                ]);
        }

        $user->status = 'pendiente';
        $user->rejection_reason = null;
        $user->save();

        return redirect()->route('panel')->with('ok', 'Solicitud enviada. El administrador la revisará pronto.');
    }

    public function motivoVisto(Request $request)
    {
        $user = $request->user();

        if ($user->status === 'rechazado' && $user->rejection_reason !== null) {
            $user->rejection_reason = null;
            $user->save();
        }

        return response()->noContent();
    }

    /**
     * Requisitos para poder enviar la solicitud.
     */
    private function requisitos(User $user): array
    {
        $cortesActivos = $user->cortes()->where('cortes.activo', true)->count();

        return [
            [
                'texto' => 'Teléfono de contacto',
                'ok' => filled($user->phone),
                'url' => route('perfil'),
                'accion' => 'Completar en Mi perfil',
            ],
            [
                'texto' => 'Descripción personal',
                'ok' => filled(trim((string) $user->bio)),
                'url' => route('perfil'),
                'accion' => 'Completar en Mi perfil',
            ],
            [
                'texto' => 'Al menos un corte marcado',
                'ok' => $cortesActivos > 0,
                'url' => route('mis-cortes'),
                'accion' => 'Elegir en Mis cortes',
            ],
        ];
    }
}