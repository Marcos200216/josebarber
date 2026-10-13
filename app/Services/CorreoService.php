<?php

    namespace App\Services;

    use App\Models\Cita;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Throwable;

    /**
     * Único punto de salida de correos (Brevo por API HTTPS).
     * Nunca lanza errores: si algo falla, la reserva/cambio/cancelación sigue funcionando.
     */
    class CorreoService
    {
        public function citaNueva(Cita $cita, string $pin): void
        {
            $this->enviar($cita->cliente_email, $cita->cliente_nombre, 'Tu cita quedó reservada', [
                'titulo' => 'Cita reservada',
                'intro' => "Hola {$cita->cliente_nombre}, tu cita quedó reservada.",
                'filas' => $this->detalle($cita),
                'codigo' => $pin,
                'nota' => 'Necesitas este código para ver, cambiar o cancelar tu cita.',
                'enlace' => ['Ver mi cita sin código', $cita->urlEnlace()],
            ], 'cita_nueva_cliente');

            $barbero = $cita->barbero;

            $this->enviar($barbero?->email, $barbero?->name, 'Nueva cita', [
                'titulo' => 'Nueva cita',
                'intro' => 'Tienes una cita nueva en tu agenda.',
                'filas' => ['Cliente' => $cita->cliente_nombre, 'Teléfono' => $cita->cliente_telefono]
                    + $this->detalle($cita, false),
            ], 'cita_nueva_barbero');
        }

               public function citaCambiada(Cita $cita, int $barberoAnteriorId, Carbon $inicioAnterior): void
       {
           $this->enviar($cita->cliente_email, $cita->cliente_nombre, 'Tu cita fue cambiada', [
               'titulo' => 'Cita cambiada',
               'intro' => "Hola {$cita->cliente_nombre}, tu cita fue cambiada. Estos son los datos nuevos.",
               'filas' => $this->detalle($cita),
               'nota' => 'Antes: ' . $this->cuando($inicioAnterior),
               'enlace' => ['Ver mi cita', $cita->urlEnlace()],
           ], 'cita_cambiada_cliente');

           $barbero = $cita->barbero;
           $cambioDeBarbero = $barberoAnteriorId !== (int) $cita->user_id;
           $titulo = $cambioDeBarbero ? 'Cita movida a tu agenda' : 'Cita cambiada';

           $this->enviar($barbero?->email, $barbero?->name, $titulo, [
               'titulo' => $titulo,
               'intro' => $cambioDeBarbero ? 'Un cliente movió su cita a tu agenda.' : 'Un cliente cambió la hora de su cita.',
               'filas' => ['Cliente' => $cita->cliente_nombre, 'Teléfono' => $cita->cliente_telefono]
                   + $this->detalle($cita, false),
               'nota' => 'Antes: ' . $this->cuando($inicioAnterior),
           ], 'cita_cambiada_barbero');

           if ($cambioDeBarbero) {
               $anterior = User::find($barberoAnteriorId);

               $this->enviar($anterior?->email, $anterior?->name, 'Cita reasignada', [
                   'titulo' => 'Cita reasignada',
                   'intro' => 'Una cita ya no es contigo: el cliente la movió a otro barbero.',
                   'filas' => ['Cliente' => $cita->cliente_nombre, 'Era' => $this->cuando($inicioAnterior)],
               ], 'cita_cambiada_barbero_anterior');
           }
       }

       public function citaCancelada(Cita $cita): void
       {
           $this->enviar($cita->cliente_email, $cita->cliente_nombre, 'Tu cita fue cancelada', [
               'titulo' => 'Cita cancelada',
               'intro' => "Hola {$cita->cliente_nombre}, tu cita fue cancelada.",
               'filas' => $this->detalle($cita),
               'nota' => 'Cuando quieras, puedes reservar otra cita en el sitio.',
               'enlace' => ['Reservar otra cita', route('inicio')],
           ], 'cita_cancelada_cliente');

           $barbero = $cita->barbero;

           $this->enviar($barbero?->email, $barbero?->name, 'Cita cancelada', [
               'titulo' => 'Cita cancelada',
               'intro' => 'Un cliente canceló su cita.',
               'filas' => ['Cliente' => $cita->cliente_nombre, 'Teléfono' => $cita->cliente_telefono]
                   + $this->detalle($cita, false),
           ], 'cita_cancelada_barbero');
       }

       /** Recordatorio al cliente. $cuando: '24h' o '2h'. */
       public function recordatorio(Cita $cita, string $cuando): void
       {
           $es24 = $cuando === '24h';
           $cuandoTexto = $es24 ? 'es mañana' : 'es en 2 horas';

           $this->enviar($cita->cliente_email, $cita->cliente_nombre, "Recordatorio: tu cita {$cuandoTexto}", [
               'titulo' => 'Recordatorio de cita',
               'intro' => "Hola {$cita->cliente_nombre}, tu cita {$cuandoTexto}.",
               'filas' => $this->detalle($cita),
               'nota' => '¿No puedes asistir? Cámbiala o cancélala desde el botón.',
               'enlace' => ['Ver mi cita', $cita->urlEnlace()],
           ], "recordatorio_{$cuando}_cliente");
       }

       public function codigoNuevo(?string $email, string $nombre, string $pin): void
       {
           $this->enviar($email, $nombre, 'Tu código nuevo', [
               'titulo' => 'Código nuevo',
               'intro' => "Hola {$nombre}, este es tu código nuevo.",
               'codigo' => $pin,
               'nota' => 'Lo necesitas para ver, cambiar o cancelar tus citas. Si no lo pediste tú, ignora este mensaje.',
           ], 'codigo_nuevo');
       }

        // ------------------------------------------------------------------
        //  Envío
        // ------------------------------------------------------------------

        public function enviar(?string $email, ?string $nombre, string $asunto, array $partes, string $tipo): void
        {
            try {
                if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Log::info("[Correo omitido] {$tipo}: correo vacío o inválido");

                    return;
                }

                $clave = (string) config('correo.brevo_key');
                $remitente = (string) config('correo.remitente');

                if ($clave === '' || $remitente === '') {
                    Log::warning('CorreoService: falta BREVO_API_KEY o MAIL_FROM_ADDRESS');

                    return;
                }

                $armado = $this->armar($partes);

                $respuesta = Http::withHeaders(['api-key' => $clave])
                    ->connectTimeout(3)
                    ->timeout(8)
                    ->acceptJson()
                    ->post('https://api.brevo.com/v3/smtp/email', [
                        'sender' => ['name' => (string) config('correo.remitente_nombre'), 'email' => $remitente],
                        'to' => [['email' => $email, 'name' => $nombre ?: $email]],
                        'subject' => $asunto,
                        'htmlContent' => $armado['html'],
                        'textContent' => $armado['texto'],
                    ]);

                if ($respuesta->successful()) {
                    Log::info("[Correo enviado] {$tipo}");
                } else {
                    // No se registra el contenido del correo (lleva el PIN).
                    Log::error("CorreoService: {$tipo} falló, HTTP {$respuesta->status()}: " . Str::limit($respuesta->body(), 200));
                }
            } catch (Throwable $e) {
                Log::error('CorreoService: ' . $e->getMessage());
            }
        }

       // ------------------------------------------------------------------
       //  Textos y diseño
       // ------------------------------------------------------------------

        /** @return array{html: string, texto: string} */
        private function armar(array $p): array
        {
            $html = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:480px;margin:0 auto;padding:24px;color:#000;">'
                . '<h2 style="margin:0 0 12px;font-size:22px;text-transform:uppercase;">' . e($p['titulo']) . '</h2>'
                . '<p style="margin:0 0 16px;font-size:16px;line-height:1.5;">' . e($p['intro']) . '</p>';

            $texto = $p['titulo'] . "\n\n" . $p['intro'] . "\n\n";

            if (! empty($p['filas'])) {
                $html .= '<table style="width:100%;border-collapse:collapse;margin-bottom:16px;background:#E8E8E8;border-radius:10px;">';
                foreach ($p['filas'] as $rotulo => $valor) {
                    $html .= '<tr><td style="padding:8px 14px;font-size:13px;color:#525252;">' . e($rotulo) . '</td>'
                        . '<td style="padding:8px 14px;font-size:15px;font-weight:bold;">' . e($valor) . '</td></tr>';
                    $texto .= "{$rotulo}: {$valor}\n";
                }
                $html .= '</table>';
                $texto .= "\n";
            }

            if (! empty($p['codigo'])) {
                $html .= '<div style="text-align:center;margin:16px 0;padding:18px;background:#000;border-radius:12px;">'
                    . '<div style="font-size:12px;letter-spacing:2px;color:#B8B8B8;text-transform:uppercase;">Tu código</div>'
                    . '<div style="font-size:40px;letter-spacing:10px;font-weight:bold;color:#fff;margin-top:6px;">' . e($p['codigo']) . '</div></div>';
                $texto .= "Tu código: {$p['codigo']}\n\n";
            }

            if (! empty($p['nota'])) {
                $html .= '<p style="margin:0 0 16px;font-size:14px;color:#525252;">' . e($p['nota']) . '</p>';
                $texto .= $p['nota'] . "\n\n";
            }

            if (! empty($p['enlace'])) {
                [$etiqueta, $url] = $p['enlace'];
                $html .= '<p style="margin:0 0 16px;"><a href="' . e($url) . '" style="display:inline-block;padding:12px 22px;background:#000;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">' . e($etiqueta) . '</a></p>';
                $texto .= "{$etiqueta}: {$url}\n\n";
            }

            $html .= '<p style="margin:24px 0 0;font-size:12px;color:#8C8C8C;">' . e((string) config('correo.remitente_nombre')) . '</p></div>';

            return ['html' => $html, 'texto' => trim($texto)];
        }

        private function detalle(Cita $cita, bool $conBarbero = true): array
        {
            $filas = ['Servicio' => $cita->corte?->nombre ?? 'Corte'];

            if ($conBarbero) {
                $filas['Barbero'] = $cita->barbero?->name ?? 'Barbero';
            }

            $filas['Fecha'] = $this->fecha($cita->inicio);
            $filas['Hora'] = $cita->inicio->format('g:i a');

            return $filas;
        }

        private function fecha(Carbon $c): string
        {
            return ucfirst($c->copy()->locale('es')->translatedFormat('l j \d\e F'));
        }

               private function cuando(Carbon $c): string
       {
           return $this->fecha($c) . ' a las ' . $c->format('g:i a');
       }
    }
