@extends('layout.app')

@section('title', 'Bloqueos · ' . config('app.name'))

@push('styles')
<style>
    .encabezado {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 20px;
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

    /* ---------- Lista ---------- */
    .lista { display: grid; gap: 12px; }

    .bloqueo {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
    }
    .bloqueo-icono {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--gris-1);
    }
    .bloqueo-info { flex: 1; min-width: 0; }
    .bloqueo-info h3 { margin: 0 0 2px; font-size: 1.35rem; overflow-wrap: anywhere; }
    .bloqueo-info p { margin: 0; font-size: .92rem; color: var(--gris-4); }
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

    .bloqueo-acciones { display: flex; gap: 6px; flex-shrink: 0; }
    .bloqueo-acciones form { margin: 0; }
    .icono-btn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 1px solid var(--gris-2);
        border-radius: 8px;
        color: var(--negro);
        cursor: pointer;
        transition: background 400ms ease, border-color 400ms ease;
    }
    .icono-btn:hover { background: var(--gris-1); border-color: var(--gris-3); }

    @media (max-width: 480px) {
        .bloqueo { flex-wrap: wrap; }
        .bloqueo-acciones { width: 100%; justify-content: flex-end; }
    }

    .vacio {
        padding: 40px 20px;
        text-align: center;
        background: var(--blanco);
        border-radius: 12px;
        color: var(--gris-4);
    }
    .vacio [data-lucide] { width: 36px; height: 36px; color: var(--gris-3); margin-bottom: 8px; }
    .vacio p { margin: 0 0 16px; }

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

    .campo label.etq { display: block; margin-bottom: 4px; font-size: .8rem; font-weight: 600; color: var(--gris-4); }
    .campo input[type="text"],
    .campo input[type="date"],
    .campo input[type="time"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--gris-2);
        border-radius: 8px;
        background: var(--blanco);
        font: inherit;
        color: var(--negro);
    }
    .campo input:focus { outline: 2px solid var(--gris-4); outline-offset: 1px; border-color: var(--gris-4); }

    .chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .chip {
        padding: 6px 14px;
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        background: var(--blanco);
        font: inherit;
        font-size: .88rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 400ms ease, border-color 400ms ease;
    }
    .chip:hover { background: var(--gris-1); border-color: var(--gris-3); }

    .fila-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    .check-linea { display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer; }
    .check-linea input { width: 20px; height: 20px; accent-color: var(--negro); }

    #bloqueHoras { display: grid; }
    #bloqueHoras[hidden] { display: none; }
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
                            <i data-lucide="pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('bloqueos.destroy', $b['id']) }}" class="js-borrar">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icono-btn" aria-label="Eliminar bloqueo">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ---------- Modal crear / editar ---------- --}}
    <div class="modal" id="modalBloqueo" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal-caja">
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
                        <input type="text" id="motivo" name="motivo" maxlength="120" value="{{ old('motivo') }}" placeholder="Ej. Trámite bancario">
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

        function abrir() {
            fIni.min = hoy();
            fFin.min = hoy();
            pintarHoras();
            modal.classList.add('abierto');
            document.body.style.overflow = 'hidden';
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.body.style.overflow = '';
        }

        function nuevo() {
            modoCrear();
            motivo.value = '';
            todo.checked = false;
            fIni.value = hoy();
            fFin.value = hoy();
            hIni.value = '12:00';
            hFin.value = '13:00';
            btnGuardar.disabled = false;
            // Limpia errores de una validación anterior
            const err = modal.querySelector('.errores');
            if (err) err.remove();
            abrir();
        }

        function editar(d) {
            modoEditar(d.id);
            motivo.value = d.motivo || '';
            todo.checked = !!d.todo_el_dia;
            fIni.value = d.fecha_inicio;
            fFin.value = d.fecha_fin;
            hIni.value = d.hora_inicio;
            hFin.value = d.hora_fin;
            btnGuardar.disabled = false;
            const err = modal.querySelector('.errores');
            if (err) err.remove();
            abrir();
        }

        document.getElementById('btnNuevo').addEventListener('click', nuevo);
        const nv = document.getElementById('btnNuevoVacio');
        if (nv) nv.addEventListener('click', nuevo);

        document.querySelectorAll('.js-editar').forEach(function (b) {
            b.addEventListener('click', function () { editar(JSON.parse(b.dataset.bloqueo)); });
        });

        modal.querySelectorAll('[data-cerrar]').forEach(function (b) { b.addEventListener('click', cerrar); });
        modal.addEventListener('click', function (e) { if (e.target === modal) cerrar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('abierto')) cerrar();
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