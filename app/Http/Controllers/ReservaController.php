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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\WhatsAppService;

class ReservaController extends Controller
{
    /** Máximo de citas pendientes (que aún no terminan) por teléfono. */
    private const MAX_PENDIENTES = 2;

       public function __construct(
        private DisponibilidadService $disponibilidad,
        private WhatsAppService $whatsapp,
    ) {
    }

    /**
     * Barberos visibles que hacen un corte, ordenados por su próxima hora libre.
     */
    public function barberos(int $corte): JsonResponse
    {
        $corteModelo = Corte::activos()->findOrFail($corte);

        $filas = [];
        foreach ($this->barberosDelCorte($corteModelo)->get() as $b) {
            $prox = $this->disponibilidad->proximoEspacio($b, $corteModelo);
            $filas[] = [
                'orden' => $prox ? $prox->getTimestamp() : PHP_INT_MAX,
                'datos' => $this->presentarBarbero($b) + [
                    'proximo' => $prox ? $this->presentarMomento($prox) : null,
                ],
            ];
        }

        usort($filas, fn ($a, $b) => $a['orden'] <=> $b['orden']);

        return response()->json([
            'corte' => ['id' => $corteModelo->id, 'nombre' => $corteModelo->nombre],
            'barberos' => array_map(fn ($f) => $f['datos'], $filas),
        ]);
    }

    /**
     * Los próximos 14 días de un barbero para un corte, cada uno con sus horas libres.
     * GET /reservar/disponibilidad?corte=1&barbero=2
     */
    public function disponibilidad(Request $request): JsonResponse
    {
        $corte = Corte::activos()->findOrFail($request->integer('corte'));
        $barbero = $this->barberosDelCorte($corte)->whereKey($request->integer('barbero'))->firstOrFail();

        $hoy = now()->startOfDay();
        $dias = [];

        for ($i = 0; $i < DisponibilidadService::DIAS_ADELANTE; $i++) {
            $dia = $hoy->copy()->addDays($i);
            $horas = $this->disponibilidad->horasLibres($barbero, $corte, $dia);
            $es = $dia->copy()->locale('es');

            $dias[] = [
                'fecha' => $dia->format('Y-m-d'),
                'etiqueta' => $this->etiquetaDia($dia),
                'semana' => ucfirst($es->translatedFormat('D')),
                'numero' => $dia->format('j'),
                'mes' => $es->translatedFormat('M'),
                'disponible' => ! empty($horas),
                'horas' => array_map(fn ($h) => [
                    'valor' => $h,
                    'texto' => Carbon::createFromFormat('H:i', $h)->format('g:i a'),
                ], $horas),
            ];
        }

        return response()->json([
            'corte' => [
                'id' => $corte->id,
                'nombre' => $corte->nombre,
                'duracion' => (int) $corte->duracion_minutos,
                'precio' => '₡' . number_format($corte->precio, 0, ',', ' '),
            ],
            'barbero' => ['id' => $barbero->id, 'nombre' => $barbero->name, 'foto' => $barbero->foto_url],
            'dias' => $dias,
        ]);
    }

    /**
     * Primer espacio libre entre todos los barberos que hacen el corte.
     * GET /reservar/primer-espacio?corte=1
     */
    public function primerEspacio(Request $request): JsonResponse
    {
        $corte = Corte::activos()->findOrFail($request->integer('corte'));

        $filas = [];
        foreach ($this->barberosDelCorte($corte)->get() as $b) {
            $prox = $this->disponibilidad->proximoEspacio($b, $corte);

            if ($prox) {
                $filas[] = [
                    'orden' => $prox->getTimestamp(),
                    'barbero' => ['id' => $b->id, 'nombre' => $b->name, 'foto' => $b->foto_url],
                    'momento' => $this->presentarMomento($prox),
                ];
            }
        }

        if (! $filas) {
            return response()->json(['disponible' => false]);
        }

        usort($filas, fn ($a, $b) => $a['orden'] <=> $b['orden']);

        $mapa = fn ($f) => ['barbero' => $f['barbero']] + $f['momento'];

        return response()->json([
            'disponible' => true,
            'principal' => $mapa($filas[0]),
            'otros' => array_map($mapa, array_slice($filas, 1, 3)),
        ]);
    }

    /**
     * Crea la cita.
     * POST /reservar  { corte, barbero, fecha:Y-m-d, hora:H:i, nombre, telefono, sitio_web (honeypot) }
     */
    public function store(Request $request): JsonResponse
    {
        // Honeypot: un humano nunca ve ni llena este campo.
        if ($request->filled('sitio_web')) {
            return $this->error('envio', 'No pudimos procesar tu solicitud. Inténtalo de nuevo.', 422);
        }

        $v = Validator::make($request->all(), [
            'corte' => ['required', 'integer'],
            'barbero' => ['required', 'integer'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'nombre' => ['required', 'string', 'min:2', 'max:80'],
            'telefono' => ['required', 'string', 'max:30'],
        ], [
            'corte.required' => 'Elige un corte.',
            'corte.integer' => 'Elige un corte.',
            'barbero.required' => 'Elige un barbero.',
            'barbero.integer' => 'Elige un barbero.',
            'fecha.required' => 'Elige un día.',
            'fecha.date_format' => 'Elige un día válido.',
            'hora.required' => 'Elige una hora.',
            'hora.date_format' => 'Elige una hora válida.',
            'nombre.required' => 'Escribe tu nombre.',
            'nombre.min' => 'Tu nombre es muy corto.',
            'nombre.max' => 'Tu nombre es muy largo (máximo 80 letras).',
            'telefono.required' => 'Escribe tu teléfono.',
            'telefono.max' => 'Ese teléfono no parece válido.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'codigo' => 'validacion',
                'mensaje' => $v->errors()->first(),
                'errores' => $v->errors()->toArray(),
            ], 422)->header('Cache-Control', 'no-store');
        }

        $nombre = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $request->input('nombre'))));

        if (mb_strlen($nombre) < 2) {
            return $this->validacion('nombre', 'Tu nombre es muy corto.');
        }

        $telefono = $this->normalizarTelefono((string) $request->input('telefono'));

        if ($telefono === null) {
            return $this->validacion('telefono', 'Escribe un teléfono de Costa Rica de 8 dígitos, por ejemplo 7084 2740.');
        }

        // ----- Revalidación en el servidor -----
        $corte = Corte::activos()->find($request->integer('corte'));
        $barbero = $corte
            ? $this->barberosDelCorte($corte)->whereKey($request->integer('barbero'))->first()
            : null;

        if (! $corte || ! $barbero) {
            return $this->error('no-disponible', 'Ese corte o barbero ya no está disponible. Actualiza la página e inténtalo de nuevo.', 422);
        }

        $tz = config('app.timezone');
        $hora = (string) $request->input('hora');
        // El "!" evita que Carbon rellene los segundos con los del momento actual.
        $inicio = Carbon::createFromFormat('!Y-m-d H:i', $request->input('fecha') . ' ' . $hora, $tz);
        $fin = $inicio->copy()->addMinutes((int) $corte->duracion_minutos);

        // PIN de 4 dígitos: se guarda hasheado y se devuelve una sola vez.
        $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $pinHash = Hash::make($pin);

        try {
            $resultado = DB::transaction(function () use ($barbero, $corte, $inicio, $fin, $hora, $nombre, $telefono, $pinHash) {
                // Bloquea al barbero: dos clientes a la vez se atienden uno detrás del otro.
                User::whereKey($barbero->id)->lockForUpdate()->first();

                if ($this->pendientesDe($telefono) >= self::MAX_PENDIENTES) {
                    return ['error' => 'limite-citas'];
                }

                // Vuelve a revisar con el barbero bloqueado: ventana de 14 días, margen,
                // pausa, bloqueos y traslape con citas pendientes.
                $libres = $this->disponibilidad->horasLibres($barbero, $corte, $inicio->copy()->startOfDay());

                if (! in_array($hora, $libres, true)) {
                    return ['error' => 'hora-ocupada'];
                }

                $cita = Cita::create([
                    'user_id' => $barbero->id,
                    'corte_id' => $corte->id,
                    'cliente_nombre' => $nombre,
                    'cliente_telefono' => $telefono,
                    'inicio' => $inicio,
                    'fin' => $fin,
                    'estado' => 'pendiente',
                    'pin_hash' => $pinHash,
                ]);

                return ['cita' => $cita];
            });
        } catch (UniqueConstraintViolationException $e) {
            return $this->error('hora-ocupada', 'Alguien acaba de tomar esa hora. Elige otra, por favor.', 409);
        }

        if (isset($resultado['error'])) {
            return $resultado['error'] === 'limite-citas'
                ? $this->error('limite-citas', 'Ya tienes ' . self::MAX_PENDIENTES . ' citas pendientes. Cancela una o espera a que pase para reservar otra.', 422)
                : $this->error('hora-ocupada', 'Esa hora ya no está disponible. Elige otra, por favor.', 409);
        }

               $cita = $resultado['cita'];
        $cita->load(['corte', 'barbero']);
        $this->whatsapp->citaNueva($cita, $pin);

        return response()->json([
            'ok' => true,
            'pin' => $pin,
            'enlace' => $cita->urlEnlace(),
            'cita' => [
                'corte' => [
                    'nombre' => $corte->nombre,
                    'precio' => '₡' . number_format($corte->precio, 0, ',', ' '),
                    'duracion' => (int) $corte->duracion_minutos,
                ],
                                'barbero' => [
                    'nombre' => $barbero->name,
                ],
                'cuando' => $this->presentarMomento($inicio)['texto'],
                'fecha' => $inicio->format('Y-m-d'),
                'hora' => $inicio->format('H:i'),
                'cliente' => [
                    'nombre' => $nombre,
                    'telefono' => $this->formatearTelefono($telefono),
                ],
            ],
        ], 201)->header('Cache-Control', 'no-store');
    }

    // ---------------------------------------------------------------

    /** Barberos visibles (aceptado + activo) que hacen el corte y tienen algún día de trabajo. */
    private function barberosDelCorte(Corte $corte): Builder
    {
        return User::visibles()
            ->whereHas('cortes', fn ($q) => $q->where('cortes.id', $corte->id))
            ->whereHas('horarios', fn ($q) => $q->where('trabaja', true))
            ->with('galeria');
    }

    private function presentarBarbero(User $b): array
    {
        return [
            'id' => $b->id,
            'nombre' => $b->name,
            'descripcion' => $b->bio,
            'foto' => $b->foto_url,
            'galeria' => $b->galeria->sortByDesc('id')->take(6)->map(fn ($f) => [
                'url' => asset('storage/' . $f->imagen_path),
                'descripcion' => $f->descripcion,
                       ])->values()->all(),
        ];
    }

    private function presentarMomento(Carbon $c): array
    {
        return [
            'fecha' => $c->format('Y-m-d'),
            'hora' => $c->format('H:i'),
            'texto' => $this->etiquetaDia($c) . ' ' . $c->format('g:i a'),
        ];
    }

    private function etiquetaDia(Carbon $fecha): string
    {
        if ($fecha->isToday()) {
            return 'Hoy';
        }
        if ($fecha->isTomorrow()) {
            return 'Mañana';
        }

        return ucfirst($fecha->copy()->locale('es')->translatedFormat('D j M'));
    }

    /** Cuántas citas pendientes (que aún no terminan) tiene ese teléfono. */
    private function pendientesDe(string $telefono): int
    {
        return Cita::where('cliente_telefono', $telefono)
            ->where('estado', 'pendiente')
            ->where('fin', '>', now())
            ->count();
    }

    /** Devuelve +506######## o null si no es un teléfono costarricense válido. */
    private function normalizarTelefono(string $crudo): ?string
    {
        $digitos = preg_replace('/\D+/', '', $crudo);

        if (strlen($digitos) === 11 && str_starts_with($digitos, '506')) {
            $digitos = substr($digitos, 3);
        }

        return preg_match('/^[24-8]\d{7}$/', $digitos) ? '+506' . $digitos : null;
    }

    /** +50670842740 → +506 7084 2740 */
    private function formatearTelefono(string $tel): string
    {
        return '+506 ' . substr($tel, 4, 4) . ' ' . substr($tel, 8, 4);
    }

    private function validacion(string $campo, string $mensaje): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'codigo' => 'validacion',
            'mensaje' => $mensaje,
            'errores' => [$campo => [$mensaje]],
        ], 422)->header('Cache-Control', 'no-store');
    }

    private function error(string $codigo, string $mensaje, int $estado): JsonResponse
    {
        return response()->json(['ok' => false, 'codigo' => $codigo, 'mensaje' => $mensaje], $estado)
            ->header('Cache-Control', 'no-store');
    }
}