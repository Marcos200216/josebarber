<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Corte;
use App\Models\User;
use App\Services\DisponibilidadService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use App\Services\WhatsAppService;

class MiCitaController extends Controller
{
    private const SESION = 'mi_cita';
    private const MINUTOS_SESION = 15;
    private const HORAS_MINIMAS = 2;      // anticipación mínima para cancelar o cambiar
    private const MAX_INTENTOS = 5;       // fallos por teléfono
    private const BLOQUEO_SEGUNDOS = 900; // 15 minutos

    public function __construct(
        private DisponibilidadService $disponibilidad,
        private WhatsAppService $whatsapp,
    ) {}

    /** Teléfono + PIN → abre una verificación corta y devuelve las citas. */
    public function verificar(Request $request): JsonResponse
    {
        // Honeypot: un humano no llena este campo.
        if ($request->filled('sitio_web')) {
            return $this->error(422, 'envio', 'No pudimos procesar tu solicitud.');
        }

        $telefono = $this->normalizarTelefono((string) $request->input('telefono', ''));
        $pin = (string) $request->input('pin', '');

        $errores = [];
        if (! $telefono) {
            $errores['telefono'] = ['Escribe tu teléfono de 8 dígitos, por ejemplo 7084 2740.'];
        }
        if (! preg_match('/^\d{4}$/', $pin)) {
            $errores['pin'] = ['Tu PIN tiene 4 dígitos.'];
        }
        if ($errores) {
            return $this->error(422, 'validacion', 'Revisa los datos.', ['errores' => $errores]);
        }

        // Límite estricto por teléfono (además del límite por IP de la ruta).
        $clave = 'mi-cita:' . $telefono;
        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            $min = max(1, (int) ceil(RateLimiter::availableIn($clave) / 60));

            return $this->error(429, 'bloqueado', "Demasiados intentos. Espera {$min} minuto(s) e inténtalo de nuevo.");
        }

        $citas = $this->pendientesDe($telefono)->get();

        $acierto = false;
        foreach ($citas as $c) {
            if ($c->pin_hash && Hash::check($pin, $c->pin_hash)) {
                $acierto = true;
                break;
            }
        }

        // Si el teléfono no tiene citas, gastamos el mismo tiempo para no revelar que no existe.
        if ($citas->isEmpty()) {
            Hash::make($pin);
        }

        if (! $acierto) {
            RateLimiter::hit($clave, self::BLOQUEO_SEGUNDOS);

            return $this->error(422, 'credenciales', 'Teléfono o PIN incorrecto.');
        }

        RateLimiter::clear($clave);

        // migrate(true) cambia el ID de sesión pero CONSERVA el token CSRF de la página.
        $request->session()->migrate(true);
        $request->session()->put(self::SESION, [
            'telefono' => $telefono,
            'hasta' => now()->addMinutes(self::MINUTOS_SESION)->getTimestamp(),
        ]);

        return $this->json(['ok' => true, 'citas' => $this->presentarLista($citas)]);
    }

    /** Devuelve las citas si la verificación sigue vigente. */
    public function index(Request $request): JsonResponse
    {
        $telefono = $this->telefonoVerificado($request);
        if (! $telefono) {
            return $this->sinSesion();
        }

        return $this->json(['ok' => true, 'citas' => $this->presentarLista($this->pendientesDe($telefono)->get())]);
    }

    /** Cierra la verificación (botón "Salir"). */
    public function salir(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESION);

        return $this->json(['ok' => true]);
    }
    /**
     * "Olvidé mi código": genera un PIN nuevo (el mismo para todas las citas pendientes
     * de ese teléfono) y lo manda por WhatsApp. La respuesta es siempre igual, tenga
     * o no citas, para no revelar qué teléfonos existen.
     * POST /reservar/mi-cita/olvide-codigo  { telefono, sitio_web (honeypot) }
     */
    public function olvideCodigo(Request $request): JsonResponse
    {
        if ($request->filled('sitio_web')) {
            return $this->error(422, 'envio', 'No pudimos procesar tu solicitud.');
        }

        $telefono = $this->normalizarTelefono((string) $request->input('telefono', ''));

        if (! $telefono) {
            return $this->error(422, 'validacion', 'Revisa los datos.', [
                'errores' => ['telefono' => ['Escribe tu teléfono de 8 dígitos, por ejemplo 7084 2740.']],
            ]);
        }

        // Máximo 3 solicitudes por hora por teléfono (cuenta aunque no tenga citas),
        // para que nadie llene de mensajes el WhatsApp de otra persona.
        $clave = 'olvide:' . $telefono;
        if (RateLimiter::tooManyAttempts($clave, 3)) {
            $min = max(1, (int) ceil(RateLimiter::availableIn($clave) / 60));

            return $this->error(429, 'bloqueado', "Ya pediste varios códigos. Espera {$min} minuto(s) e inténtalo de nuevo.");
        }
        RateLimiter::hit($clave, 3600);

        $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $hash = Hash::make($pin); // se calcula siempre, para gastar el mismo tiempo

        $citas = $this->pendientesDe($telefono)->get();

        if ($citas->isNotEmpty()) {
            Cita::whereIn('id', $citas->pluck('id'))->update(['pin_hash' => $hash]);

            $this->whatsapp->codigoNuevo($telefono, $citas->first()->cliente_nombre, $pin, $citas->first()->id);
        }

        return $this->json([
            'ok' => true,
            'mensaje' => 'Si ese teléfono tiene citas pendientes, te enviamos un código nuevo por WhatsApp.',
        ]);
    }


    private const MINUTOS_SESION_ENLACE = 60;

    /**
     * Enlace privado (sin PIN): GET /cita/{token}
     * Abre una verificación para el teléfono de esa cita y manda al cliente al inicio,
     * donde se abrirá "Tus citas". Se redirige para que el token no quede en la barra.
     */
    public function enlace(Request $request, string $token): \Illuminate\Http\RedirectResponse
    {
        // Los tokens son de 48 letras y números; cualquier otra cosa ni se consulta.
        if (! preg_match('/^[A-Za-z0-9]{48}$/', $token)) {
            return redirect()->route('inicio')->with('enlace_invalido', true);
        }

        $cita = Cita::where('token', $token)
            ->where('estado', 'pendiente')
            ->where('fin', '>', now())
            ->first();

        if (! $cita) {
            return redirect()->route('inicio')->with('enlace_invalido', true);
        }

        // migrate(true), igual que en verificar(): NO usar regenerate() (rompe el token CSRF).
        $request->session()->migrate(true);
        $request->session()->put(self::SESION, [
            'telefono' => $cita->cliente_telefono,
            'hasta' => now()->addMinutes(self::MINUTOS_SESION_ENLACE)->getTimestamp(),
        ]);

        return redirect()->route('inicio')->with('abrir_mi_cita', true);
    }

    public function cancelar(Request $request, int $cita): JsonResponse
    {
        $telefono = $this->telefonoVerificado($request);
        if (! $telefono) {
            return $this->sinSesion();
        }

        $resultado = DB::transaction(function () use ($cita, $telefono) {
            // Solo una cita de ESTE teléfono: nunca se confía en el id que manda el navegador.
            $modelo = Cita::where('id', $cita)
                ->where('cliente_telefono', $telefono)
                ->lockForUpdate()
                ->first();

            if (! $modelo || $modelo->estado !== 'pendiente' || $modelo->fin->lte(now())) {
                return 'no-existe';
            }

            if ($modelo->inicio->lt(now()->addHours(self::HORAS_MINIMAS))) {
                return 'muy-tarde';
            }

            $modelo->estado = 'cancelada';
            $modelo->save(); // slot_activo queda en null: el espacio se libera

            return 'ok';
        });

        if ($resultado === 'ok') {
            $canceladaModelo = Cita::with(['corte', 'barbero'])->find($cita);

            if ($canceladaModelo) {
                $this->whatsapp->citaCancelada($canceladaModelo);
            }
        }

        $citas = $this->presentarLista($this->pendientesDe($telefono)->get());

        if ($resultado === 'muy-tarde') {
            return $this->error(422, 'muy-tarde', $this->textoMuyTarde('cancelar'), ['citas' => $citas]);
        }

        if ($resultado === 'no-existe') {
            return $this->error(404, 'no-existe', 'Esa cita ya no está disponible.', ['citas' => $citas]);
        }

        return $this->json(['ok' => true, 'mensaje' => 'Tu cita fue cancelada.', 'citas' => $citas]);
    }

    // ------------------------------------------------------------------
    //  CAMBIAR
    // ------------------------------------------------------------------

    /** Barberos que pueden atender el corte de esta cita (para cambiar de barbero). */
    public function barberos(Request $request, int $cita): JsonResponse
    {
        $telefono = $this->telefonoVerificado($request);
        if (! $telefono) {
            return $this->sinSesion();
        }

        $modelo = $this->citaVigente($telefono, $cita);
        if (! $modelo || ! $modelo->corte) {
            return $this->error(404, 'no-existe', 'Esa cita ya no está disponible.');
        }

        if ($modelo->inicio->lt(now()->addHours(self::HORAS_MINIMAS))) {
            return $this->error(422, 'muy-tarde', $this->textoMuyTarde('cambiar'));
        }

        $barberos = $this->barberosDelCorte($modelo->corte)->get()->map(fn(User $b) => [
            'id' => $b->id,
            'nombre' => $b->name,
            'foto' => $b->foto_url,
            'actual' => $b->id === $modelo->user_id,
        ])->values()->all();

        return $this->json(['ok' => true, 'barberos' => $barberos]);
    }

    /**
     * Días y horas libres de un barbero para mover esta cita.
     * GET /reservar/mi-cita/{cita}/disponibilidad?barbero=ID
     */
    public function disponibilidad(Request $request, int $cita): JsonResponse
    {
        $telefono = $this->telefonoVerificado($request);
        if (! $telefono) {
            return $this->sinSesion();
        }

        $modelo = $this->citaVigente($telefono, $cita);
        if (! $modelo || ! $modelo->corte) {
            return $this->error(404, 'no-existe', 'Esa cita ya no está disponible.');
        }

        if ($modelo->inicio->lt(now()->addHours(self::HORAS_MINIMAS))) {
            return $this->error(422, 'muy-tarde', $this->textoMuyTarde('cambiar'));
        }

        $corte = $modelo->corte;
        $barbero = $this->barberosDelCorte($corte)->whereKey($request->integer('barbero'))->first();
        if (! $barbero) {
            return $this->error(404, 'no-existe', 'Ese barbero ya no está disponible para este corte.');
        }

        $tz = config('app.timezone');
        $minimo = now()->addHours(self::HORAS_MINIMAS);
        $hoy = now()->startOfDay();
        $dias = [];

        for ($i = 0; $i < DisponibilidadService::DIAS_ADELANTE; $i++) {
            $dia = $hoy->copy()->addDays($i);
            $fecha = $dia->format('Y-m-d');
            $es = $dia->copy()->locale('es');

            $horas = [];
            foreach ($this->disponibilidad->horasLibres($barbero, $corte, $dia, $modelo->id) as $h) {
                $inicio = Carbon::createFromFormat('!Y-m-d H:i', $fecha . ' ' . $h, $tz);

                if ($inicio->lt($minimo)) {
                    continue; // respeta las 2 horas también para la hora nueva
                }

                // La hora que ya tiene con este mismo barbero no se ofrece como "cambio".
                if ($barbero->id === $modelo->user_id && $inicio->equalTo($modelo->inicio)) {
                    continue;
                }

                $horas[] = ['valor' => $h, 'texto' => Carbon::createFromFormat('H:i', $h)->format('g:i a')];
            }

            $dias[] = [
                'fecha' => $fecha,
                'etiqueta' => $this->etiquetaDia($dia),
                'semana' => ucfirst($es->translatedFormat('D')),
                'numero' => $dia->format('j'),
                'mes' => $es->translatedFormat('M'),
                'disponible' => ! empty($horas),
                'horas' => $horas,
            ];
        }

        return $this->json([
            'ok' => true,
            'barbero' => ['id' => $barbero->id, 'nombre' => $barbero->name, 'foto' => $barbero->foto_url],
            'actual' => ['fecha' => $modelo->inicio->format('Y-m-d'), 'hora' => $modelo->inicio->format('H:i')],
            'dias' => $dias,
        ]);
    }

    /**
     * Mueve la cita a otro día/hora y/o a otro barbero. El PIN no cambia.
     * POST /reservar/mi-cita/{cita}/cambiar  { barbero, fecha:Y-m-d, hora:H:i }
     */
    public function cambiar(Request $request, int $cita): JsonResponse
    {
        $telefono = $this->telefonoVerificado($request);
        if (! $telefono) {
            return $this->sinSesion();
        }

        $v = Validator::make($request->all(), [
            'barbero' => ['required', 'integer'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
        ], [
            'barbero.required' => 'Elige un barbero.',
            'barbero.integer' => 'Elige un barbero.',
            'fecha.required' => 'Elige un día.',
            'fecha.date_format' => 'Elige un día válido.',
            'hora.required' => 'Elige una hora.',
            'hora.date_format' => 'Elige una hora válida.',
        ]);

        if ($v->fails()) {
            return $this->error(422, 'validacion', $v->errors()->first(), ['errores' => $v->errors()->toArray()]);
        }

        $tz = config('app.timezone');
        $hora = (string) $request->input('hora');
        $barberoId = (int) $request->input('barbero');
        $nuevoInicio = Carbon::createFromFormat('!Y-m-d H:i', $request->input('fecha') . ' ' . $hora, $tz);

        $anterior = null;

        try {
            $resultado = DB::transaction(function () use ($cita, $telefono, $barberoId, $nuevoInicio, $hora, &$anterior) {
                // Solo una cita de ESTE teléfono (no se confía en el id que manda el navegador).
                $modelo = Cita::where('id', $cita)
                    ->where('cliente_telefono', $telefono)
                    ->lockForUpdate()
                    ->first();

                if (! $modelo || $modelo->estado !== 'pendiente' || $modelo->fin->lte(now())) {
                    return 'no-existe';
                }

                if ($modelo->inicio->lt(now()->addHours(self::HORAS_MINIMAS))) {
                    return 'muy-tarde';
                }

                $corte = Corte::activos()->find($modelo->corte_id);
                $barbero = $corte ? $this->barberosDelCorte($corte)->whereKey($barberoId)->first() : null;

                if (! $barbero) {
                    return 'no-disponible';
                }

                // Bloquea al barbero de destino: dos clientes a la vez se atienden uno detrás del otro.
                User::whereKey($barbero->id)->lockForUpdate()->first();

                if ($nuevoInicio->lt(now()->addHours(self::HORAS_MINIMAS))) {
                    return 'muy-pronto';
                }

                if ($barbero->id === $modelo->user_id && $nuevoInicio->equalTo($modelo->inicio)) {
                    return 'sin-cambio';
                }

                // Mismas reglas que al reservar (ventana de 14 días, pausa, bloqueos, traslape),
                // pero ignorando la propia cita para que no choque consigo misma.
                $libres = $this->disponibilidad->horasLibres($barbero, $corte, $nuevoInicio->copy()->startOfDay(), $modelo->id);

                if (! in_array($hora, $libres, true)) {
                    return 'hora-ocupada';
                }

                $anterior = ['barbero_id' => (int) $modelo->user_id, 'inicio' => $modelo->inicio->copy()];

                $modelo->user_id = $barbero->id;
                $modelo->inicio = $nuevoInicio;
                $modelo->fin = $nuevoInicio->copy()->addMinutes((int) $corte->duracion_minutos);

                // La cita tiene fecha nueva: los recordatorios de 24 h y 2 h se deben volver a enviar.
                $modelo->recordatorio_24h_at = null;
                $modelo->recordatorio_2h_at = null;

                $modelo->save(); // el modelo recalcula slot_activo solo


                return 'ok';
            });
        } catch (UniqueConstraintViolationException $e) {
            $resultado = 'hora-ocupada';
        }

        if ($resultado === 'ok' && $anterior) {
            $nueva = Cita::with(['corte', 'barbero'])->find($cita);

            if ($nueva) {
                $this->whatsapp->citaCambiada($nueva, $anterior['barbero_id'], $anterior['inicio']);
            }
        }

        $citas = $this->presentarLista($this->pendientesDe($telefono)->get());

        switch ($resultado) {
            case 'ok':
                return $this->json(['ok' => true, 'mensaje' => 'Tu cita fue cambiada.', 'citas' => $citas]);
            case 'muy-tarde':
                return $this->error(422, 'muy-tarde', $this->textoMuyTarde('cambiar'), ['citas' => $citas]);
            case 'muy-pronto':
                return $this->error(422, 'muy-pronto', 'La nueva hora debe ser al menos ' . self::HORAS_MINIMAS . ' horas desde ahora.');
            case 'sin-cambio':
                return $this->error(422, 'sin-cambio', 'Elegiste la misma hora que ya tienes. Escoge otra.');
            case 'hora-ocupada':
                return $this->error(409, 'hora-ocupada', 'Esa hora ya no está disponible. Elige otra, por favor.');
            case 'no-disponible':
                return $this->error(422, 'no-disponible', 'Ese corte o barbero ya no está disponible. Elige otro.');
            default:
                return $this->error(404, 'no-existe', 'Esa cita ya no está disponible.', ['citas' => $citas]);
        }
    }

    // ------------------------------------------------------------------

    private function pendientesDe(string $telefono): Builder
    {
        return Cita::with(['corte', 'barbero'])
            ->where('cliente_telefono', $telefono)
            ->where('estado', 'pendiente')
            ->where('fin', '>', now())
            ->orderBy('inicio');
    }

    /** Una cita pendiente y vigente de ESTE teléfono, o null. */
    private function citaVigente(string $telefono, int $id): ?Cita
    {
        return $this->pendientesDe($telefono)->where('id', $id)->first();
    }

    /** Barberos visibles (aceptado + activo) que hacen el corte y tienen algún día de trabajo. */
    private function barberosDelCorte(Corte $corte): Builder
    {
        return User::visibles()
            ->whereHas('cortes', fn($q) => $q->where('cortes.id', $corte->id))
            ->whereHas('horarios', fn($q) => $q->where('trabaja', true));
    }

    private function textoMuyTarde(string $accion): string
    {
        return 'Faltan menos de ' . self::HORAS_MINIMAS . ' horas para tu cita, por eso ya no se puede '
            . $accion . ' desde aquí. Comunícate con el barbero.';
    }

    private function telefonoVerificado(Request $request): ?string
    {
        $s = $request->session()->get(self::SESION);

        if (! is_array($s) || empty($s['telefono']) || (int) ($s['hasta'] ?? 0) < now()->getTimestamp()) {
            $request->session()->forget(self::SESION);

            return null;
        }

        return $s['telefono'];
    }

    private function presentarLista(Collection $citas): array
    {
        return $citas->map(fn(Cita $c) => $this->presentar($c))->values()->all();
    }

    private function presentar(Cita $c): array
    {
        $corte = $c->corte;
        $barbero = $c->barbero;
        $minutos = (int) round(($c->fin->getTimestamp() - $c->inicio->getTimestamp()) / 60);

        return [
            'id' => $c->id,
            'corte' => [
                'id' => $corte?->id,
                'nombre' => $corte?->nombre ?? 'Corte',
                'precio' => $corte ? '₡' . number_format($corte->precio, 0, ',', ' ') : null,
                'duracion' => $minutos,
            ],
            'barbero' => [
                'id' => $barbero?->id,
                'nombre' => $barbero?->name ?? 'Barbero',
                'direccion' => $barbero?->location_address,
                'mapa' => $this->urlSegura($barbero?->location_url),
            ],
            'fecha' => $c->inicio->format('Y-m-d'),
            'hora' => $c->inicio->format('H:i'),
            'cuando' => $this->etiquetaDia($c->inicio) . ' · ' . $this->hora12($c->inicio),
            'puede_modificar' => $c->inicio->gte(now()->addHours(self::HORAS_MINIMAS)),
        ];
    }

    private function etiquetaDia(Carbon $f): string
    {
        if ($f->isToday()) {
            return 'Hoy';
        }
        if ($f->isTomorrow()) {
            return 'Mañana';
        }

        return ucfirst($f->copy()->locale('es')->translatedFormat('D j M'));
    }

    private function hora12(Carbon $f): string
    {
        return $f->format('g:i') . ' ' . $f->format('a');
    }

    private function normalizarTelefono(string $crudo): ?string
    {
        $d = preg_replace('/\D/', '', $crudo);
        if (strlen($d) === 11 && str_starts_with($d, '506')) {
            $d = substr($d, 3);
        }

        return preg_match('/^[24-8]\d{7}$/', $d) ? '+506' . $d : null;
    }

    private function urlSegura(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        return preg_match('#^https?://#i', $url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function sinSesion(): JsonResponse
    {
        return $this->error(401, 'sin-sesion', 'Tu sesión terminó. Escribe tu teléfono y tu PIN otra vez.');
    }

    private function json(array $datos, int $status = 200): JsonResponse
    {
        return response()->json($datos, $status)->header('Cache-Control', 'no-store');
    }

    private function error(int $status, string $codigo, string $mensaje, array $extra = []): JsonResponse
    {
        return $this->json(array_merge(['ok' => false, 'codigo' => $codigo, 'mensaje' => $mensaje], $extra), $status);
    }
}
