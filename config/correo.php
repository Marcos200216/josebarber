   <?php

   return [
       // correo | whatsapp | simulado (no envía nada, solo escribe en el log)
       'canal' => env('NOTIFICACION_CANAL', 'simulado'),

       'brevo_key' => env('BREVO_API_KEY'),
       'remitente' => env('MAIL_FROM_ADDRESS'),
       'remitente_nombre' => env('MAIL_FROM_NAME', 'Jose Barber Cut'),
   ];