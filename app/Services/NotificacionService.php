<?php

   namespace App\Services;

   use App\Models\Cita;
   use Carbon\Carbon;
   use Closure;
   use Illuminate\Support\Facades\Log;
   use Throwable;

   /**
    * Decide por qué canal salen los avisos, según NOTIFICACION_CANAL:
    * correo | whatsapp | simulado.
    */
   class NotificacionService
   {
       public function __construct(
           private CorreoService $correo,
           private WhatsAppService $whatsapp,
       ) {
       }

       public function citaNueva(Cita $cita, string $pin): void
       {
           $this->despachar(
               'citaNueva',
               fn () => $this->correo->citaNueva($cita, $pin),
               fn () => $this->whatsapp->citaNueva($cita, $pin),
           );
       }

       public function citaCambiada(Cita $cita, int $barberoAnteriorId, Carbon $inicioAnterior): void
       {
           $this->despachar(
               'citaCambiada',
               fn () => $this->correo->citaCambiada($cita, $barberoAnteriorId, $inicioAnterior),
               fn () => $this->whatsapp->citaCambiada($cita, $barberoAnteriorId, $inicioAnterior),
           );
       }

       public function citaCancelada(Cita $cita): void
       {
           $this->despachar(
               'citaCancelada',
               fn () => $this->correo->citaCancelada($cita),
               fn () => $this->whatsapp->citaCancelada($cita),
           );
       }

       public function recordatorio(Cita $cita, string $cuando): void
       {
           $this->despachar(
               "recordatorio_{$cuando}",
               fn () => $this->correo->recordatorio($cita, $cuando),
               fn () => $this->whatsapp->recordatorio($cita, $cuando),
           );
       }

       public function codigoNuevo(string $telefono, ?string $email, string $nombre, string $pin, ?int $citaId = null): void
       {
           $this->despachar(
               'codigoNuevo',
               fn () => $this->correo->codigoNuevo($email, $nombre, $pin),
               fn () => $this->whatsapp->codigoNuevo($telefono, $nombre, $pin, $citaId),
           );
       }

       /** Con el canal "correo" hace falta un correo guardado; con los demás no. */
       public function puedeEnviarCodigo(?string $email): bool
       {
           return config('correo.canal') !== 'correo' || filled($email);
       }

       private function despachar(string $metodo, Closure $porCorreo, Closure $porWhatsapp): void
       {
           try {
               match ((string) config('correo.canal', 'simulado')) {
                   'correo' => $porCorreo(),
                   'whatsapp' => $porWhatsapp(),
                   default => Log::info("[Notificación simulada] {$metodo}"),
               };
           } catch (Throwable $e) {
               Log::error('NotificacionService: ' . $e->getMessage());
           }
       }
   }