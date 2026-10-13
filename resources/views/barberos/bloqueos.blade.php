@extends('layout.app')

@section('title', 'Bloqueos · ' . config('app.name'))

@push('styles')
<style>
    /* =========================================================
       Bloqueos: mobile-first
       Base = celular. Los @media (min-width) ensanchan hacia arriba.
       ========================================================= */

    /* ---------- Encabezado ---------- */
    .encabezado {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
    }
    .encabezado h1 { margin: 0 0 6px; }
    .encabezado p { margin: 0; color: var(--gris-4); max-width: 60ch; }
    .encabezado p a { text-underline-offset: 3px; }
    .encabezado .btn { width: 100%; }

    /* ---------- Botones (objetivo táctil ~46px) ---------- */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 10px 20px;
        border: 1px solid var(--negro);
        border-radius: 10px;
        background: var(--negro);
        color: var(--blanco);
        font: inherit;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease, opacity 400ms ease;
    }
    .btn svg { width: 18px; height: 18px; flex-shrink: 0; }
    .btn:hover:not(:disabled) { background: var(--gris-4); border-color: var(--gris-4); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }
    .btn-claro { background: var(--blanco); color: var(--negro); border-color: var(--gris-2); }
    .btn-claro:hover:not(:disabled) { background: var(--gris-1); color: var(--negro); border-color: var(--gris-3); }
    .btn-sm { min-height: 44px; padding: 8px 14px; font-size: .92rem; }
    .btn:focus-visible,
    .icono-btn:focus-visible,
    .modal-x:focus-visible,
    .chip:focus-visible { outline: 2px solid var(--negro); outline-offset: 2px; }

    /* ---------- Lista ---------- */
    .lista { display: grid; gap: 12px; }

    /* Móvil: icono + texto arriba, acciones en una fila propia abajo */
    .bloqueo {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        gap: 12px 14px;
        align-items: center;
        padding: 14px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 14px;
    }

    .bloqueo-icono {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--gris-1);
    }

    .bloqueo-info { min-width: 0; }
    .bloqueo-info h3 { margin: 0 0 2px; font-size: 1.35rem; overflow-wrap: anywhere; }
    .bloqueo-info p { margin: 0; font-size: .92rem; color: var(--gris-4); overflow-wrap: anywhere; }
    .bloqueo-info strong { color: var(--negro); }

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

    .bloqueo-acciones {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--gris-1);
    }
    .bloqueo-acciones form { margin: 0; display: flex; }

    .icono-btn {
        width: 100%;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 12px;
        background: transparent;
        border: 1px solid var(--gris-2);
        border-radius: 10px;
        color: var(--negro);
        font: inherit;
        font-size: .92rem;
        font-weight: 600;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, border-color 400ms ease;
    }
    .icono-btn svg { width: 18px; height: 18px; }
    .icono-btn:hover { background: var(--gris-1); border-color: var(--gris-3); }

    .vacio {
        padding: 36px 18px;
        text-align: center;
        background: var(--blanco);
        border-radius: 14px;
        color: var(--gris-4);
    }
    .vacio [data-lucide],
    .vacio svg { width: 36px; height: 36px; color: var(--gris-3); margin-bottom: 8px; }
    .vacio p { margin: 0 0 16px; }
    .vacio .btn { width: 100%; }

    /* ---------- Modal (hoja inferior en móvil) ---------- */
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
        overscroll-behavior: contain;
        transition: opacity 400ms ease, visibility 400ms ease;
    }
    .modal.abierto { opacity: 1; visibility: visible; }

    .modal-caja {
        width: 100%;
        max-width: 520px;
        max-height: 92vh;
        max-height: 92dvh;
        display: flex;
        flex-direction: column;
        background: var(--blanco);
        border-radius: 18px 18px 0 0;
        outline: none;
        transform: translateY(24px);
        transition: transform 450ms cubic-bezier(.22, .61, .36, 1);
    }
    .modal.abierto .modal-caja { transform: none; }

    .modal-cab {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 16px 12px 18px;
        border-bottom: 1px solid var(--gris-1);
    }

    /* Agarradera visual de la hoja */
    .modal-cab::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        width: 40px;
        height: 4px;
        margin-left: -20px;
        border-radius: 4px;
        background: var(--gris-2);
    }

    .modal-cab h2 { margin: 0; }

    .modal-x {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 0;
        border-radius: 10px;
        color: var(--gris-4);
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease;
    }
    .modal-x:hover { background: var(--gris-1); }

    .modal-form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
    }

    .modal-cuerpo {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        align-content: start;
        gap: 18px;
        padding: 18px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    /* Pie fijo, respeta la barra de gestos de iPhone/Android */
    .modal-pie {
        display: flex;
        gap: 10px;
        padding: 12px 18px;
        padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
        border-top: 1px solid var(--gris-1);
        background: var(--blanco);
    }
    .modal-pie .btn { flex: 1 1 0; }

    .errores {
        padding: 12px 14px;
        border: 1px solid var(--gris-4);
        border-left-width: 4px;
        border-radius: 8px;
        background: var(--blanco);
        font-size: .92rem;
    }
    .errores ul { margin: 0; padding-left: 18px; }
    .errores li { overflow-wrap: anywhere; }

    /* ---------- Campos ---------- */
    .campo { min-width: 0; }
    .campo label.etq { display: block; margin-bottom: 5px; font-size: .85rem; font-weight: 600; color: var(--gris-4); }

    /* 16px evita el zoom de iOS; appearance:none corrige el alto raro de date/time en iPhone */
    .campo input[type="text"],
    .campo input[type="date"],
    .campo input[type="time"] {
        display: block;
        width: 100%;
        min-width: 0;
        min-height: 48px;
        padding: 10px 12px;
        border: 1px solid var(--gris-2);
        border-radius: 10px;
        background: var(--blanco);
        font: inherit;
        font-size: 16px;
        color: var(--negro);
        text-align: left;
        -webkit-appearance: none;
        appearance: none;
    }
    .campo input:focus { outline: 2px solid var(--gris-4); outline-offset: 1px; border-color: var(--gris-4); }

    .chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .chip {
        min-height: 40px;
        padding: 6px 16px;
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        background: var(--blanco);
        font: inherit;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, border-color 400ms ease;
    }
    .chip:hover { background: var(--gris-1); border-color: var(--gris-3); }

    .fila-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    .check-linea {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 44px;
        font-weight: 600;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    .check-linea input { width: 22px; height: 22px; flex-shrink: 0; accent-color: var(--negro); }

    #bloqueHoras { display: grid; }
    #bloqueHoras[hidden] { display: none; }

    /* Celulares muy angostos: un campo por fila */
    @media (max-width: 359px) {
        .fila-2 { grid-template-columns: 1fr; }
    }

    /* ---------- Tablet y escritorio ---------- */
    @media (min-width: 640px) {
        .encabezado {
            grid-template-columns: 1fr auto;
            align-items: end;
            margin-bottom: 20px;
        }
        .encabezado .btn { width: auto; }
        .vacio { padding: 40px 20px; }
        .vacio .btn { width: auto; }

        .bloqueo {
            grid-template-columns: 44px minmax(0, 1fr) auto;
            padding: 16px 18px;
        }
        .bloqueo-acciones {
            grid-column: auto;
            display: flex;
            gap: 6px;
            padding-top: 0;
            border-top: 0;
        }
        .icono-btn { width: 44px; padding: 0; }
        .icono-btn .txt { display: none; }

        .modal { align-items: center; padding: 24px; }
        .modal-caja { border-radius: 16px; max-height: min(92vh, 780px); }
        .modal-cab::before { display: none; }
        .modal-cab { padding: 18px 16px 14px 22px; }
        .modal-cuerpo { padding: 20px 22px; }
        .modal-pie { justify-content: flex-end; padding: 16px 22px; }
        .modal-pie .btn { flex: 0 0 auto; }
    }

    @media (prefers-reduced-motion: reduce) {
        .modal, .modal-caja, .btn, .icono-btn, .modal-x, .chip { transition: none; }
    }
</style>
@endpush

@section('content')
    <div class="encabezado" data-reveal>
        <div>
            <h1>Bloqueos</h1>
            <p>Cierra espacios puntuales de tu agenda: vacaciones, trámites o una salida temprano. Tu almuerzo diario se configura en <a href="{{ route('horario') }}"><strong>Mi horario</strong></a>, no aquí.</p>
        </div>
        <button type="button" class="btn" id="btnNuevo">
            <i data-lucide="plus"></i> Nuevo bloqueo
        </button>
    </div>

    @if ($bloqueos->isEmpty())
        <div class="vacio" data-reveal>
            <i data-lucide="calendar-off"></i>
            <p>No tienes bloqueos vigentes.<br>Tu agenda está abierta según tu horario semanal.</p>
            <button type="button" class="btn btn-claro" id="btnNuevoVacio">
                <i data-lucide="plus"></i> Crear un bloqueo
            </button>
        </div>
    @else
        <div class="lista">
            @foreach ($bloqueos as $b)
                <div class="bloqueo" data-reveal>
                    <div class="bloqueo-icono"><i data-lucide="calendar-off"></i></div>
                    <div class="bloqueo-info">
                        <h3>
                            {{ $b['motivo'] }}
                            @if ($b['en_curso'])<span class="etiqueta">En curso</span>@endif
                        </h3>
                        <p><strong>{{ $b['texto'] }}</strong>@if ($b['detalle']) · {{ $b['detalle'] }}@endif</p>
                    </div>
                    <div class="bloqueo-acciones">
                        <button type="button" class="icono-btn js-editar" aria-label="Editar bloqueo"
                                data-bloqueo="{{ json_encode($b['editar']) }}">
                            <i data-lucide="pencil"></i><span class="txt">Editar</span>
                        </button>
                        <form method="POST" action="{{ route('bloqueos.destroy', $b['id']) }}" class="js-borrar">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icono-btn" aria-label="Eliminar bloqueo">
                                <i data-lucide="trash-2"></i><span class="txt">Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ---------- Modal crear / editar ---------- --}}
    <div class="modal" id="modalBloqueo" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="modalTitulo">Nuevo bloqueo</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('bloqueos.store') }}" id="formBloqueo" class="modal-form" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="metodo" disabled>
                <input type="hidden" name="editar_id" id="editarId" value="{{ old('editar_id') }}">

                <div class="modal-cuerpo">
                    @if ($errors->any())
                        <div class="errores" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="campo">
                        <label class="etq" for="motivo">Motivo (opcional)</label>
                        <input type="text" id="motivo" name="motivo" maxlength="120" value="{{ old('motivo') }}" placeholder="Ej. Trámite bancario" enterkeyhint="done">
                        <div class="chips">
                            <button type="button" class="chip" data-motivo="Trámite" data-todo="0">Trámite</button>
                            <button type="button" class="chip" data-motivo="Salí temprano" data-todo="0">Salí temprano</button>
                            <button type="button" class="chip" data-motivo="Descanso" data-todo="0">Descanso</button>
                            <button type="button" class="chip" data-motivo="Vacaciones" data-todo="1">Vacaciones</button>
                        </div>
                    </div>

                    <label class="check-linea">
                        <input type="checkbox" name="todo_el_dia" id="todoDia" value="1" {{ old('todo_el_dia') ? 'checked' : '' }}>
                        Todo el día
                    </label>

                    <div class="fila-2">
                        <div class="campo">
                            <label class="etq" for="fechaInicio">Desde</label>
                            <input type="date" id="fechaInicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                        </div>
                        <div class="campo">
                            <label class="etq" for="fechaFin">Hasta</label>
                            <input type="date" id="fechaFin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                        </div>
                    </div>

                    <div class="fila-2" id="bloqueHoras">
                        <div class="campo">
                            <label class="etq" for="horaInicio">Hora de inicio</label>
                            <input type="time" id="horaInicio" name="hora_inicio" value="{{ old('hora_inicio', '12:00') }}" step="900">
                        </div>
                        <div class="campo">
                            <label class="etq" for="horaFin">Hora de fin</label>
                            <input type="time" id="horaFin" name="hora_fin" value="{{ old('hora_fin', '13:00') }}" step="900">
                        </div>
                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn btn-claro" data-cerrar>Cancelar</button>
                    <button type="submit" class="btn" id="btnGuardar">
                        <i data-lucide="save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('modalBloqueo');
        const caja = modal.querySelector('.modal-caja');
        const form = document.getElementById('formBloqueo');
        const titulo = document.getElementById('modalTitulo');
        const metodo = document.getElementById('metodo');
        const editarId = document.getElementById('editarId');
        const motivo = document.getElementById('motivo');
        const todo = document.getElementById('todoDia');
        const fIni = document.getElementById('fechaInicio');
        const fFin = document.getElementById('fechaFin');
        const hIni = document.getElementById('horaInicio');
        const hFin = document.getElementById('horaFin');
        const bloqueHoras = document.getElementById('bloqueHoras');
        const btnGuardar = document.getElementById('btnGuardar');

        const URL_CREAR = @json(route('bloqueos.store'));
        const URL_EDITAR = @json(url('/panel/bloqueos')) + '/';

        let disparador = null;

        function hoy() {
            const d = new Date();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return d.getFullYear() + '-' + m + '-' + dd;
        }

        function pintarHoras() {
            bloqueHoras.hidden = todo.checked;
        }

        function modoCrear() {
            titulo.textContent = 'Nuevo bloqueo';
            form.action = URL_CREAR;
            metodo.disabled = true;
            editarId.value = '';
        }

        function modoEditar(id) {
            titulo.textContent = 'Editar bloqueo';
            form.action = URL_EDITAR + id;
            metodo.value = 'PUT';
            metodo.disabled = false;
            editarId.value = id;
        }

        function abrir(origen) {
            disparador = origen || null;
            fIni.min = hoy();
            fFin.min = hoy();
            pintarHoras();
            modal.classList.add('abierto');
            document.documentElement.classList.add('sin-scroll');
            // Se enfoca la caja (no un campo) para no abrir el teclado de golpe en el celular
            caja.focus({ preventScroll: true });
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.documentElement.classList.remove('sin-scroll');
            if (disparador && document.contains(disparador)) {
                disparador.focus({ preventScroll: true });
            }
            disparador = null;
        }

        function limpiarErrores() {
            const err = modal.querySelector('.errores');
            if (err) err.remove();
        }

        function nuevo(origen) {
            modoCrear();
            motivo.value = '';
            todo.checked = false;
            fIni.value = hoy();
            fFin.value = hoy();
            hIni.value = '12:00';
            hFin.value = '13:00';
            btnGuardar.disabled = false;
            limpiarErrores();
            abrir(origen);
        }

        function editar(d, origen) {
            modoEditar(d.id);
            motivo.value = d.motivo || '';
            todo.checked = !!d.todo_el_dia;
            fIni.value = d.fecha_inicio;
            fFin.value = d.fecha_fin;
            hIni.value = d.hora_inicio;
            hFin.value = d.hora_fin;
            btnGuardar.disabled = false;
            limpiarErrores();
            abrir(origen);
        }

        document.getElementById('btnNuevo').addEventListener('click', function (e) { nuevo(e.currentTarget); });
        const nv = document.getElementById('btnNuevoVacio');
        if (nv) nv.addEventListener('click', function (e) { nuevo(e.currentTarget); });

        document.querySelectorAll('.js-editar').forEach(function (b) {
            b.addEventListener('click', function () { editar(JSON.parse(b.dataset.bloqueo), b); });
        });

        modal.querySelectorAll('[data-cerrar]').forEach(function (b) { b.addEventListener('click', cerrar); });
        modal.addEventListener('click', function (e) { if (e.target === modal) cerrar(); });

        document.addEventListener('keydown', function (e) {
            if (!modal.classList.contains('abierto')) return;

            if (e.key === 'Escape') {
                cerrar();
                return;
            }

            // Mantener el foco dentro del modal
            if (e.key === 'Tab') {
                const foco = caja.querySelectorAll('button:not([disabled]), input:not([disabled]):not([type="hidden"]), a[href]');
                if (!foco.length) return;
                const primero = foco[0];
                const ultimo = foco[foco.length - 1];
                if (e.shiftKey && (document.activeElement === primero || document.activeElement === caja)) {
                    e.preventDefault();
                    ultimo.focus();
                } else if (!e.shiftKey && document.activeElement === ultimo) {
                    e.preventDefault();
                    primero.focus();
                }
            }
        });

        // Botones rápidos de motivo
        modal.querySelectorAll('.chip').forEach(function (c) {
            c.addEventListener('click', function () {
                motivo.value = c.dataset.motivo;
                todo.checked = c.dataset.todo === '1';
                pintarHoras();
            });
        });

        todo.addEventListener('change', pintarHoras);

        // La fecha de fin nunca queda antes de la de inicio
        fIni.addEventListener('change', function () {
            fFin.min = fIni.value || hoy();
            if (!fFin.value || fFin.value < fIni.value) fFin.value = fIni.value;
        });

        form.addEventListener('submit', function () { btnGuardar.disabled = true; });

        // Al volver con el botón "atrás" del celular el botón podría quedar bloqueado
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) btnGuardar.disabled = false;
        });

        // Eliminar con confirmación
        document.querySelectorAll('.js-borrar').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar este bloqueo?',
                    text: 'Ese espacio volverá a estar disponible para los clientes.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                }).then(function (r) { if (r.isConfirmed) f.submit(); });
            });
        });

        // Si la validación falló, reabre el modal con lo escrito
        @if ($errors->any())
            (function () {
                const id = editarId.value;
                if (id) modoEditar(id); else modoCrear();
                abrir();
            })();
        @endif
    })();
</script>
@endpush