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

        $paraCliente = "Hola {$cita->cliente_nombre}, tu cita quedó reservada ✅\n\n"
            . $this->detalle($cita)
            . "\n\n🔐 Tu código: {$pin}\n"
            . "Lo necesitas para ver, cambiar o cancelar tu cita.\n\n"
            . "🔗 Ver mi cita sin código:\n" . $cita->urlEnlace()
            . $this->comoLlegar($barbero)
            . "\n\n" . config('app.name');

        // Con el envío real no se guarda el PIN en la base de datos.
        $guardado = config('whatsapp.driver') === 'simulado'
            ? null
            : str_replace("Tu código: {$pin}", 'Tu código: ••••', $paraCliente);

        $this->enviar($cita->cliente_telefono, $paraCliente, 'cita_nueva_cliente', $cita->id, $guardado);

        $this->enviar($barbero?->phone, "Nueva cita 📅\n\n"
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false), 'cita_nueva_barbero', $cita->id);
    }

    public function citaCambiada(Cita $cita, int $barberoAnteriorId, Carbon $inicioAnterior): void
    {
        $barbero = $cita->barbero;

        $this->enviar($cita->cliente_telefono, "Hola {$cita->cliente_nombre}, tu cita fue cambiada 🔄\n\n"
            . $this->detalle($cita)
            . $this->comoLlegar($barbero)
            . "\n\n" . config('app.name'), 'cita_cambiada_cliente', $cita->id);

        $cambioDeBarbero = $barberoAnteriorId !== (int) $cita->user_id;

        $this->enviar($barbero?->phone, ($cambioDeBarbero ? "Cita movida a tu agenda 🔄\n\n" : "Cita cambiada 🔄\n\n")
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false)
            . "\n\nAntes era: " . $this->cuando($inicioAnterior), 'cita_cambiada_barbero', $cita->id);

        if ($cambioDeBarbero) {
            $anterior = User::find($barberoAnteriorId);

            $this->enviar($anterior?->phone, "Una cita ya no es contigo 🔄\n\n"
                . "Cliente: {$cita->cliente_nombre}\n"
                . "Era: " . $this->cuando($inicioAnterior) . "\n"
                . "El cliente la movió a otro barbero.", 'cita_cambiada_barbero_anterior', $cita->id);
        }
    }

    public function citaCancelada(Cita $cita): void
    {
        $this->enviar($cita->cliente_telefono, "Hola {$cita->cliente_nombre}, tu cita fue cancelada ❌\n\n"
            . $this->detalle($cita)
            . "\n\nCuando quieras reservar otra, entra al sitio.\n\n" . config('app.name'),
            'cita_cancelada_cliente', $cita->id);

        $this->enviar($cita->barbero?->phone, "Cita cancelada ❌\n\n"
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

        $this->enviar($cita->cliente_telefono, "Hola {$cita->cliente_nombre}, {$titulo} ⏰\n\n"
            . $this->detalle($cita)
            . $this->comoLlegar($barbero)
            . "\n\n🔗 ¿No puedes venir? Cámbiala o cancélala aquí:\n" . $cita->urlEnlace()
            . "\n\n" . config('app.name'),
            "recordatorio_{$cuando}_cliente", $cita->id);

        $this->enviar($barbero?->phone, ($es24 ? "Cita de mañana ⏰\n\n" : "Cita en 2 horas ⏰\n\n")
            . "Cliente: {$cita->cliente_nombre}\n"
            . "Teléfono: {$cita->cliente_telefono}\n"
            . $this->detalle($cita, false),
            "recordatorio_{$cuando}_barbero", $cita->id);
    }

    public function codigoNuevo(string $telefono, string $nombre, string $pin, ?int $citaId = null): void
    {
        $mensaje = "Hola {$nombre}, este es tu código nuevo 🔐\n\n"
            . "Código: {$pin}\n\n"
            . "Lo necesitas para ver, cambiar o cancelar tus citas. Si no lo pediste tú, ignora este mensaje.\n\n"
            . config('app.name');

        // Con el envío real no se guarda el PIN en la base de datos.
        $guardado = config('whatsapp.driver') === 'simulado'
            ? null
            : str_replace("Código: {$pin}", 'Código: ••••', $mensaje);

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
        $linea = '✂️ ' . ($cita->corte?->nombre ?? 'Corte');

        if ($conBarbero) {
            $linea .= "\n👤 Barbero: " . ($cita->barbero?->name ?? 'Barbero');
        }

        return $linea . "\n📅 " . $this->cuando($cita->inicio);
    }

    private function cuando(Carbon $c): string
    {
        $c = $c->copy()->locale('es');

        return ucfirst($c->translatedFormat('l j \d\e F')) . ' a las ' . $c->format('g:i a');
    }

    private function comoLlegar(?User $barbero): string
    {
        $url = trim((string) $barbero?->location_url);
        $dir = trim((string) $barbero?->location_address);

        if ($url === '' && $dir === '') {
            return '';
        }

        return "\n\n📍 " . ($dir !== '' ? $dir : 'Cómo llegar') . ($url !== '' ? "\n{$url}" : '');
    }
}