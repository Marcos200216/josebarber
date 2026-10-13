<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\MensajeWhatsapp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Único punto de salida de mensajes de WhatsApp.
 * Nunca lanza errores: si algo falla, la reserva/cambio/cancelación sigue funcionando.
 */
class WhatsAppService
{
    // ------------------------------------------------------------------
    //  Mensajes del negocio
    // ------------------------------------------------------------------

    public function citaNueva(Cita $cita, string $pin): void
    {
        $barbero = $cita->barbero;

        $paraCliente = "*Cita reservada*\n\n"
            . "Hola {$cita->cliente_nombre}, tu cita quedó reservada.\n\n"
            . $this->detalle($cita)
            . "\n\nTu código: {$pin}\n"
            . "Lo necesitas para ver, cambiar o cancelar tu cita.\n\n"
            . "Ver mi cita sin código:\n" . $cita->urlEnlace();

        // Con el envío real no se guarda el PIN en la base de datos.
        $guardado = config('whatsapp.driver') === 'simulado'
            ? null
            : str_replace("Tu código: {$pin}", 'Tu código: ****', $paraCliente);

        $this->enviar($cita->cliente_telefono, $paraCliente, 'cita_nueva_cliente', $cita->id, $guardado);

        $this->enviar($barbero?->phone, "*Nueva cita*\n\n"
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false), 'cita_nueva_barbero', $cita->id);
    }

    public function citaCambiada(Cita $cita, int $barberoAnteriorId, Carbon $inicioAnterior): void
    {
        $barbero = $cita->barbero;

        $this->enviar($cita->cliente_telefono, "*Cita cambiada*\n\n"
            . "Hola {$cita->cliente_nombre}, tu cita fue cambiada. Estos son los datos nuevos:\n\n"
            . $this->detalle($cita), 'cita_cambiada_cliente', $cita->id);

        $cambioDeBarbero = $barberoAnteriorId !== (int) $cita->user_id;

        $this->enviar($barbero?->phone, ($cambioDeBarbero ? "*Cita movida a tu agenda*\n\n" : "*Cita cambiada*\n\n")
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false)
            . "\n\nAntes: " . $this->cuando($inicioAnterior), 'cita_cambiada_barbero', $cita->id);

        if ($cambioDeBarbero) {
            $anterior = User::find($barberoAnteriorId);

            $this->enviar($anterior?->phone, "*Cita reasignada*\n\n"
                . "Una cita ya no es contigo. El cliente la movió a otro barbero.\n\n"
                . "Cliente: {$cita->cliente_nombre}\n"
                . "Era: " . $this->cuando($inicioAnterior), 'cita_cambiada_barbero_anterior', $cita->id);
        }
    }

    public function citaCancelada(Cita $cita): void
    {
        $this->enviar($cita->cliente_telefono, "*Cita cancelada*\n\n"
            . "Hola {$cita->cliente_nombre}, tu cita fue cancelada.\n\n"
            . $this->detalle($cita)
            . "\n\nCuando quieras, puedes reservar otra cita en el sitio.",
            'cita_cancelada_cliente', $cita->id);

        $this->enviar($cita->barbero?->phone, "*Cita cancelada*\n\n"
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false), 'cita_cancelada_barbero', $cita->id);
    }

    /** Recordatorio al cliente y al barbero. $cuando: '24h' o '2h'. */
    public function recordatorio(Cita $cita, string $cuando): void
    {
        $barbero = $cita->barbero;
        $es24 = $cuando === '24h';

        $titulo = $es24 ? 'tu cita es mañana' : 'tu cita es en 2 horas';

        $this->enviar($cita->cliente_telefono, "*Recordatorio de cita*\n\n"
            . "Hola {$cita->cliente_nombre}, {$titulo}.\n\n"
            . $this->detalle($cita)
            . "\n\n¿No puedes asistir? Cámbiala o cancélala aquí:\n" . $cita->urlEnlace(),
            "recordatorio_{$cuando}_cliente", $cita->id);

        $this->enviar($barbero?->phone, ($es24 ? "*Cita de mañana*\n\n" : "*Cita en 2 horas*\n\n")
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false),
            "recordatorio_{$cuando}_barbero", $cita->id);
    }

    public function codigoNuevo(string $telefono, string $nombre, string $pin, ?int $citaId = null): void
    {
        $mensaje = "*Código nuevo*\n\n"
            . "Hola {$nombre}, este es tu código nuevo.\n\n"
            . "Código: {$pin}\n\n"
            . "Lo necesitas para ver, cambiar o cancelar tus citas. Si no lo pediste tú, ignora este mensaje.";

        // Con el envío real no se guarda el PIN en la base de datos.
        $guardado = config('whatsapp.driver') === 'simulado'
            ? null
            : str_replace("Código: {$pin}", 'Código: ****', $mensaje);

        $this->enviar($telefono, $mensaje, 'codigo_nuevo', $citaId, $guardado);
    }

    // ------------------------------------------------------------------
    //  Envío
    // ------------------------------------------------------------------

    public function enviar(?string $telefono, string $mensaje, string $tipo, ?int $citaId = null, ?string $textoGuardado = null): void
    {
        try {
            $numero = $this->normalizar($telefono);
            $driver = (string) config('whatsapp.driver', 'simulado');
            $error = null;

            if ($numero === null) {
                $estado = 'omitido';
                $error = 'Teléfono vacío o inválido';
            } elseif ($driver === 'simulado') {
                $estado = 'simulado';
            } elseif ($driver === 'evolution') {
                [$estado, $error] = $this->enviarConEvolution($numero, $mensaje);
            } else {
                $estado = 'fallido';
                $error = "Driver «{$driver}» no existe";
            }

            MensajeWhatsapp::create([
                'cita_id' => $citaId,
                'telefono' => $numero ?? $telefono,
                'tipo' => $tipo,
                'texto' => $textoGuardado ?? $mensaje,
                'estado' => $estado,
                'error' => $error,
            ]);

            if ($estado === 'simulado') {
                Log::info("[WhatsApp simulado] {$tipo} → {$numero}");
            }
        } catch (Throwable $e) {
            Log::error('WhatsAppService: ' . $e->getMessage());
        }
    }

    /** Envía por Evolution API. Devuelve [estado, error]. Nunca lanza errores. */
    private function enviarConEvolution(string $numero, string $mensaje): array
    {
        $url = rtrim((string) config('whatsapp.url'), '/');
        $instancia = (string) config('whatsapp.instance');
        $clave = (string) config('whatsapp.api_key');

        if ($url === '' || $instancia === '' || $clave === '') {
            return ['fallido', 'Falta configurar EVOLUTION_URL, EVOLUTION_INSTANCE o EVOLUTION_API_KEY'];
        }

        try {
            $respuesta = Http::withHeaders(['apikey' => $clave])
                ->connectTimeout(3)
                ->timeout(8)
                ->acceptJson()
                ->post("{$url}/message/sendText/{$instancia}", [
                    'number' => ltrim($numero, '+'),
                    'text' => $mensaje,
                ]);

            if ($respuesta->successful()) {
                return ['enviado', null];
            }

            return ['fallido', Str::limit('HTTP ' . $respuesta->status() . ': ' . $respuesta->body(), 250)];
        } catch (Throwable $e) {
            Log::warning('WhatsApp (Evolution): ' . $e->getMessage());

            return ['fallido', Str::limit('No se pudo conectar con Evolution: ' . $e->getMessage(), 250)];
        }
    }

    /** Devuelve +506######## o null. */
    private function normalizar(?string $crudo): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $crudo);

        if (strlen($d) === 11 && str_starts_with($d, '506')) {
            $d = substr($d, 3);
        }

        return preg_match('/^[24-8]\d{7}$/', $d) ? '+506' . $d : null;
    }

    // ------------------------------------------------------------------
    //  Textos
    // ------------------------------------------------------------------

    private function detalle(Cita $cita, bool $conBarbero = true): string
    {
        $lineas = ['Servicio: ' . ($cita->corte?->nombre ?? 'Corte')];

        if ($conBarbero) {
            $lineas[] = 'Barbero: ' . ($cita->barbero?->name ?? 'Barbero');
        }

        $lineas[] = 'Fecha: ' . $this->fecha($cita->inicio);
        $lineas[] = 'Hora: ' . $this->hora($cita->inicio);

        return implode("\n", $lineas);
    }

    private function fecha(Carbon $c): string
    {
        return ucfirst($c->copy()->locale('es')->translatedFormat('l j \d\e F'));
    }

    private function hora(Carbon $c): string
    {
        return $c->format('g:i a');
    }

    private function cuando(Carbon $c): string
    {
        return $this->fecha($c) . ' a las ' . $this->hora($c);
    }
}