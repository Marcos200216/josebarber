@extends('layout.app')

@section('title', 'Citas · ' . config('app.name'))

@push('styles')
<style>
    .encabezado {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }
    .encabezado p { margin: 0; color: var(--gris-4); max-width: 60ch; }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 20px;
        border: 1px solid var(--negro);
        border-radius: 8px;
        background: var(--negro);
        color: var(--blanco);
        font: inherit;
        font-weight: 600;
        cursor: pointer;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease, opacity 400ms ease;
    }
    .btn:hover:not(:disabled) { background: var(--gris-4); border-color: var(--gris-4); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }
    .btn-claro { background: var(--blanco); color: var(--negro); border-color: var(--gris-2); }
    .btn-claro:hover:not(:disabled) { background: var(--gris-1); color: var(--negro); border-color: var(--gris-3); }
    .btn-sm { padding: 8px 14px; font-size: .9rem; }

    .resumen { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
    .dato {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        font-size: .9rem;
        font-weight: 600;
    }
    .dato strong { font-family: 'Barlow Condensed', sans-serif; font-size: 1.25rem; line-height: 1; }

    /* ---------- Grupos y tarjetas ---------- */
    .grupo { margin-bottom: 26px; }
    .grupo-titulo { display: flex; align-items: center; gap: 10px; margin: 0 0 10px; font-size: 1.5rem; }
    .grupo-nota { font-family: 'Manrope', sans-serif; font-size: .82rem; font-weight: 500; color: var(--gris-4); }

    .lista { display: grid; gap: 12px; }

    .cita {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
        padding: 16px 18px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
    }
    .cita.por-cerrar { border: 2px solid var(--negro); }

    .cita-hora { font-family: 'Barlow Condensed', sans-serif; font-size: 1.9rem; font-weight: 600; line-height: 1; }
    .cita-hora small {
        display: block;
        margin-top: 4px;
        font-family: 'Manrope', sans-serif;
        font-size: .8rem;
        font-weight: 600;
        color: var(--gris-4);
    }

    .cita-info h3 { margin: 0 0 2px; font-size: 1.4rem; overflow-wrap: anywhere; }
    .cita-info p { margin: 0; font-size: .92rem; color: var(--gris-4); }
    .cita-tel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        font-size: .9rem;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .cita-tel [data-lucide] { width: 16px; height: 16px; }
    .cita-tel:hover { color: var(--gris-4); }

    .cita-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
    .cita-acciones form { margin: 0; }

    @media (min-width: 640px) {
        .cita { grid-template-columns: 130px 1fr; }
        .cita-acciones { grid-column: 1 / -1; padding-top: 12px; border-top: 1px solid var(--gris-1); }
    }

    .etiqueta {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 9px;
        border-radius: 999px;
        background: var(--negro);
        color: var(--gris-1);
        font-family: 'Manrope', sans-serif;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .vacio {
        padding: 40px 20px;
        text-align: center;
        background: var(--blanco);
        border-radius: 12px;
        color: var(--gris-4);
    }
    .vacio [data-lucide] { width: 36px; height: 36px; color: var(--gris-3); margin-bottom: 8px; }
    .vacio p { margin: 0; }

    /* ---------- Modal ---------- */
    .modal {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background: rgba(0, 0, 0, .6);
        opacity: 0;
        visibility: hidden;
        transition: opacity 400ms ease, visibility 400ms ease;
    }
    .modal.abierto { opacity: 1; visibility: visible; }

    .modal-caja {
        width: 100%;
        max-width: 520px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        background: var(--blanco);
        border-radius: 16px 16px 0 0;
        transform: translateY(24px);
        transition: transform 450ms cubic-bezier(.22, .61, .36, 1);
    }
    .modal.abierto .modal-caja { transform: none; }

    @media (min-width: 640px) {
        .modal { align-items: center; padding: 24px; }
        .modal-caja { border-radius: 16px; }
    }

    .modal-cab {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px;
        border-bottom: 1px solid var(--gris-1);
    }
    .modal-cab h2 { margin: 0; }
    .modal-x {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 0;
        border-radius: 8px;
        color: var(--gris-4);
        cursor: pointer;
        transition: background 400ms ease;
    }
    .modal-x:hover { background: var(--gris-1); }

    .modal-form { display: flex; flex-direction: column; min-height: 0; }
    .modal-cuerpo { padding: 20px 22px; overflow-y: auto; display: grid; gap: 16px; }
    .modal-pie {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px;
        border-top: 1px solid var(--gris-1);
    }

    .errores {
        padding: 12px 16px;
        border: 1px solid var(--gris-4);
        border-left-width: 4px;
        border-radius: 8px;
        background: var(--blanco);
        font-size: .92rem;
    }
    .errores ul { margin: 0; padding-left: 18px; }

    .info-cita { margin: 0; padding: 12px 14px; background: var(--gris-1); border-radius: 8px; font-size: .92rem; }
    .nota { margin: 0; font-size: .85rem; color: var(--gris-4); }

    .fila-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .campo label.etq { display: block; margin-bottom: 4px; font-size: .8rem; font-weight: 600; color: var(--gris-4); }
    .campo input[type="date"], .campo input[type="time"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--gris-2);
        border-radius: 8px;
        background: var(--blanco);
        font: inherit;
        color: var(--negro);
    }
    .campo input:focus { outline: 2px solid var(--gris-4); outline-offset: 1px; border-color: var(--gris-4); }

    /* Historial */
    .chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .chip {
        padding: 6px 14px;
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        background: var(--blanco);
        font: inherit;
        font-size: .88rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }
    .chip:hover { background: var(--gris-1); border-color: var(--gris-3); }
    .chip.activo { background: var(--negro); color: var(--blanco); border-color: var(--negro); }

    .hist { display: grid; gap: 8px; }
    .hist-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: var(--gris-1);
        border-radius: 8px;
    }
    .hist-item[hidden] { display: none; }
    .hist-item strong { display: block; overflow-wrap: anywhere; }
    .hist-item span { font-size: .85rem; color: var(--gris-4); }
    .hist-estado {
        flex-shrink: 0;
        padding: 3px 10px;
        border: 1px solid var(--gris-3);
        border-radius: 999px;
        background: var(--blanco);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--gris-4);
    }
    .hist-vacio { margin: 0; padding: 20px 0; text-align: center; color: var(--gris-4); }
    .hist-vacio[hidden] { display: none; }
</style>
@endpush

@section('content')
    <div class="encabezado" data-reveal>
        <div>
            <h1>Citas</h1>
            <p>Tus citas pendientes. Marca cada una como terminada cuando el cliente se atienda.</p>
        </div>
        <button type="button" class="btn btn-claro" id="btnHistorial">
            <i data-lucide="history"></i> Terminadas
        </button>
    </div>

    <div class="resumen" data-reveal>
        <span class="dato"><strong>{{ $hoy }}</strong> {{ $hoy === 1 ? 'cita hoy' : 'citas hoy' }}</span>
        <span class="dato"><strong>{{ $total }}</strong> {{ $total === 1 ? 'pendiente en total' : 'pendientes en total' }}</span>
    </div>

    @if (count($grupos) === 0)
        <div class="vacio" data-reveal>
            <i data-lucide="calendar-check"></i>
            <p>No tienes citas pendientes.<br>Cuando un cliente reserve contigo, aparecerá aquí.</p>
        </div>
    @else
        @foreach ($grupos as $g)
            <section class="grupo" data-reveal>
                <h2 class="grupo-titulo">
                    {{ $g['titulo'] }}
                    @if ($g['por_cerrar'])
                        <span class="grupo-nota">Ya pasó su hora: márcalas como terminadas o no asistió.</span>
                    @endif
                </h2>

                <div class="lista">
                    @foreach ($g['citas'] as $c)
                        <article class="cita {{ $g['por_cerrar'] ? 'por-cerrar' : '' }}">
                            <div class="cita-hora">
                                {{ $c['hora_ini'] }}
                                <small>
                                    {{ $g['por_cerrar'] ? $c['dia'] . ' · ' : '' }}hasta {{ $c['hora_fin'] }}
                                </small>
                            </div>

                            <div class="cita-info">
                                <h3>{{ $c['cliente'] }}</h3>
                                <p>
                                    {{ $c['corte'] }} · {{ $c['minutos'] }} min
                                    @if ($c['precio']) · {{ $c['precio'] }} @endif
                                </p>
                                <a class="cita-tel" href="https://wa.me/{{ $c['wa'] }}" target="_blank" rel="noopener">
                                    <i data-lucide="message-circle"></i> {{ $c['telefono'] }}
                                </a>
                            </div>

                            <div class="cita-acciones">
                                @if ($c['ya_inicio'])
                                    <form method="POST" action="{{ route('citas.completar', $c['id']) }}" class="js-accion"
                                          data-titulo="¿Marcar como terminada?"
                                          data-texto="Se moverá a Terminadas."
                                          data-boton="Sí, terminada">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm"><i data-lucide="check"></i> Terminada</button>
                                    </form>

                                    <form method="POST" action="{{ route('citas.no-asistio', $c['id']) }}" class="js-accion"
                                          data-titulo="¿El cliente no asistió?"
                                          data-texto="La cita quedará registrada como &quot;no asistió&quot;."
                                          data-boton="Sí, no asistió">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-claro btn-sm"><i data-lucide="user-x"></i> No asistió</button>
                                    </form>
                                @endif

                                <button type="button" class="btn btn-claro btn-sm js-editar" data-cita="{{ json_encode($c['editar']) }}">
                                    <i data-lucide="calendar-clock"></i> Editar hora
                                </button>

                                <form method="POST" action="{{ route('citas.cancelar', $c['id']) }}" class="js-accion"
                                      data-titulo="¿Cancelar esta cita?"
                                      data-texto="El espacio quedará libre para otros clientes."
                                      data-boton="Sí, cancelar">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-claro btn-sm"><i data-lucide="x"></i> Cancelar</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    {{-- ---------- Modal editar hora ---------- --}}
    <div class="modal" id="modalHora" role="dialog" aria-modal="true" aria-labelledby="horaTitulo">
        <div class="modal-caja">
            <div class="modal-cab">
                <h2 id="horaTitulo">Editar hora</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="#" id="formHora" class="modal-form" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="editar_id" id="editarId" value="{{ old('editar_id') }}">

                <div class="modal-cuerpo">
                    @if ($errors->hasAny(['fecha', 'hora']))
                        <div class="errores" role="alert">
                            <ul>
                                @foreach (['fecha', 'hora'] as $k)
                                    @foreach ($errors->get($k) as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="info-cita" id="infoCita"></p>

                    <div class="fila-2">
                        <div class="campo">
                            <label class="etq" for="fecha">Nueva fecha</label>
                            <input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}" required>
                        </div>
                        <div class="campo">
                            <label class="etq" for="hora">Nueva hora</label>
                            <input type="time" id="hora" name="hora" value="{{ old('hora') }}" step="900" required>
                        </div>
                    </div>

                    <p class="nota" id="notaDuracion"></p>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn btn-claro" data-cerrar>Cancelar</button>
                    <button type="submit" class="btn" id="btnGuardarHora">
                        <i data-lucide="save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------- Modal terminadas / historial ---------- --}}
    <div class="modal" id="modalHistorial" role="dialog" aria-modal="true" aria-labelledby="histTitulo">
        <div class="modal-caja">
            <div class="modal-cab">
                <h2 id="histTitulo">Historial de citas</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="modal-cuerpo">
                <div class="chips">
                    <button type="button" class="chip activo" data-filtro="completada">Terminadas</button>
                    <button type="button" class="chip" data-filtro="cancelada">Canceladas</button>
                    <button type="button" class="chip" data-filtro="no_asistio">No asistió</button>
                </div>

                <div class="hist">
                    @foreach ($historial as $h)
                        <div class="hist-item" data-estado="{{ $h['estado'] }}">
                            <div>
                                <strong>{{ $h['cliente'] }}</strong>
                                <span>{{ $h['corte'] }} · {{ $h['fecha'] }}</span>
                            </div>
                            <span class="hist-estado">{{ $h['etiqueta'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="hist-vacio" id="histVacio" hidden>No hay citas en esta categoría.</p>
                <p class="nota">Se muestran las 60 citas más recientes.</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const URL_BASE = @json(url('/panel/citas')) + '/';

        // ---------- Modales (apertura y cierre) ----------
        function abrir(m) { m.classList.add('abierto'); document.body.style.overflow = 'hidden'; }
        function cerrar(m) { m.classList.remove('abierto'); document.body.style.overflow = ''; }

        document.querySelectorAll('.modal').forEach(function (m) {
            m.querySelectorAll('[data-cerrar]').forEach(function (b) {
                b.addEventListener('click', function () { cerrar(m); });
            });
            m.addEventListener('click', function (e) { if (e.target === m) cerrar(m); });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.modal.abierto').forEach(cerrar);
        });

        // ---------- Acciones con confirmación ----------
        document.querySelectorAll('.js-accion').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'question',
                    title: f.dataset.titulo,
                    text: f.dataset.texto,
                    showCancelButton: true,
                    confirmButtonText: f.dataset.boton,
                    cancelButtonText: 'Volver',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                }).then(function (r) {
                    if (r.isConfirmed) {
                        const b = f.querySelector('button[type="submit"]');
                        if (b) b.disabled = true;
                        f.submit();
                    }
                });
            });
        });

        // ---------- Editar hora ----------
        const modalHora = document.getElementById('modalHora');
        const formHora = document.getElementById('formHora');
        const editarId = document.getElementById('editarId');
        const infoCita = document.getElementById('infoCita');
        const notaDuracion = document.getElementById('notaDuracion');
        const fecha = document.getElementById('fecha');
        const hora = document.getElementById('hora');
        const btnGuardarHora = document.getElementById('btnGuardarHora');

        const CITAS = {};
        document.querySelectorAll('.js-editar').forEach(function (b) {
            const d = JSON.parse(b.dataset.cita);
            CITAS[d.id] = d;
            b.addEventListener('click', function () { abrirEdicion(d.id, true); });
        });

        function hoyISO() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function abrirEdicion(id, conDatos) {
            const d = CITAS[id];
            if (!d) return;

            formHora.action = URL_BASE + id + '/hora';
            editarId.value = id;
            infoCita.textContent = d.cliente + ' · ' + d.corte;
            notaDuracion.textContent = 'La duración de la cita (' + d.minutos + ' min) se mantiene.';
            fecha.min = hoyISO();

            if (conDatos) {
                fecha.value = d.fecha;
                hora.value = d.hora;
                const err = modalHora.querySelector('.errores');
                if (err) err.remove();
            }
            btnGuardarHora.disabled = false;
            abrir(modalHora);
        }

        formHora.addEventListener('submit', function () { btnGuardarHora.disabled = true; });

        // ---------- Historial ----------
        const modalHist = document.getElementById('modalHistorial');
        const chips = Array.from(modalHist.querySelectorAll('.chip'));
        const items = Array.from(modalHist.querySelectorAll('.hist-item'));
        const histVacio = document.getElementById('histVacio');

        function filtrar(estado) {
            chips.forEach(function (c) { c.classList.toggle('activo', c.dataset.filtro === estado); });
            let visibles = 0;
            items.forEach(function (i) {
                const mostrar = i.dataset.estado === estado;
                i.hidden = !mostrar;
                if (mostrar) visibles++;
            });
            histVacio.hidden = visibles > 0;
        }

        chips.forEach(function (c) { c.addEventListener('click', function () { filtrar(c.dataset.filtro); }); });
        document.getElementById('btnHistorial').addEventListener('click', function () {
            filtrar('completada');
            abrir(modalHist);
        });
        filtrar('completada');

        // ---------- Errores ----------
        @if ($errors->has('accion'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: @json($errors->first('accion')),
                showConfirmButton: false,
                timer: 6000,
                timerProgressBar: true,
            });
        @endif

        @if ($errors->hasAny(['fecha', 'hora']))
            (function () {
                const id = editarId.value;
                if (id && CITAS[id]) abrirEdicion(id, false);
            })();
        @endif
    })();
</script>
@endpush