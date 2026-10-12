<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Corte;
use Illuminate\View\View;

class InicioController extends Controller
{
    private const ICONOS = [
        'instagram' => 'instagram',
        'facebook' => 'facebook',
        'whatsapp' => 'message-circle',
        'telefono' => 'phone',
        'correo' => 'mail',
        'web' => 'globe',
        'tiktok' => 'music',
        'otro' => 'link',
    ];

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

        $direccion = trim((string) Ajuste::obtener('footer_direccion', ''));
        $mapa = $this->urlSegura(Ajuste::obtener('footer_mapa_url', ''));
        $horario = trim((string) Ajuste::obtener('footer_horario', ''));

        // "Dónde estamos": el admin la puede apagar, y se oculta sola si no hay datos.
        $mostrarUbicacion = Ajuste::obtener('mostrar_ubicacion', '1') === '1'
            && ($direccion !== '' || $mapa !== null);

        return [
            'marca' => config('app.name'),
            'cortes' => $cortes,
            'ubicacion' => [
                'mostrar' => $mostrarUbicacion,
                'direccion' => $direccion,
                'mapa' => $mapa,
                'horario' => $horario,
            ],
            'footer' => [
                'texto' => trim((string) Ajuste::obtener('footer_texto', '')),
                'direccion' => $direccion,
                'mapa' => $mapa,
                'horario' => $horario,
                'enlaces' => $this->enlaces(),
            ],
        ];
    }

    private function enlaces(): array
    {
        $crudo = json_decode((string) Ajuste::obtener('footer_enlaces', '[]'), true);

        if (! is_array($crudo)) {
            return [];
        }

        $lista = [];

        foreach ($crudo as $e) {
            $url = $this->urlSegura($e['url'] ?? null);

            if ($url === null) {
                continue;
            }

            $tipo = array_key_exists($e['tipo'] ?? '', self::ICONOS) ? $e['tipo'] : 'otro';

            $lista[] = [
                'icono' => self::ICONOS[$tipo],
                'etiqueta' => trim((string) ($e['etiqueta'] ?? '')) ?: ucfirst($tipo),
                'url' => $url,
            ];
        }

        return $lista;
    }

    /** Solo acepta enlaces http(s), mailto o tel. Evita "javascript:" y similares. */
    private function urlSegura(?string $url): ?string
    {
        $url = trim((string) $url);

        return preg_match('#^(https?://|mailto:|tel:)#i', $url) ? $url : null;
    }
}