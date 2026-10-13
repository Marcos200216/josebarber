@extends('layout.app')

@section('title', 'Solicitudes')

@push('styles')
<style>
    /* =========================================================
       Solicitudes (admin): mobile-first
       Base = celular. Los @media (min-width) ensanchan hacia arriba.
       ========================================================= */

    /* ---------- Encabezado ---------- */
    .cab { margin-bottom: 20px; }
    .cab h1 { margin: 0 0 6px; }
    .cab p { margin: 0; color: var(--gris-4); }

    /* ---------- Botones (objetivo táctil ~46px) ---------- */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 10px 18px;
        border: 1px solid var(--negro);
        border-radius: 10px;
        background: var(--negro);
        color: var(--gris-1);
        font: inherit;
        font-weight: 600;
        font-size: .95rem;
        line-height: 1.2;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease, opacity 400ms ease;
    }
    .btn svg { width: 18px; height: 18px; flex-shrink: 0; }
    .btn:hover:not(:disabled) { background: var(--gris-4); border-color: var(--gris-4); }
    .btn:disabled { opacity: .5; cursor: not-allowed; }
    .btn-claro { background: transparent; color: var(--negro); border-color: var(--gris-3); }
    .btn-claro:hover:not(:disabled) { background: var(--gris-1); color: var(--negro); border-color: var(--negro); }

    .btn:focus-visible,
    .modal-x:focus-visible { outline: 2px solid var(--negro); outline-offset: 2px; }

    /* ---------- Lista ---------- */
    .lista { display: grid; gap: 14px; }

    .solicitud {
        display: grid;
        gap: 16px;
        min-width: 0;
        padding: 16px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 14px;
    }

    .persona { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .persona > div:last-child { min-width: 0; }

    .avatar {
        width: 64px;
        height: 64px;
        flex-shrink: 0;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gris-1);
        border: 1px solid var(--gris-2);
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 1.7rem;
        font-weight: 600;
        color: var(--gris-4);
    }

    .avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .persona h3 { margin: 0 0 2px; font-size: 1.45rem; overflow-wrap: anywhere; }
    .persona p { margin: 0; font-size: .9rem; color: var(--gris-4); overflow-wrap: anywhere; }

    /* Datos como filas separadas por línea */
    .datos { display: grid; font-size: .95rem; }
    .datos > div {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 12px 0;
        border-top: 1px solid var(--gris-1);
        color: var(--gris-4);
    }
    .datos svg { width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; color: var(--gris-3); }
    .datos span { min-width: 0; color: var(--negro); overflow-wrap: anywhere; white-space: pre-line; }
    .datos .falta span { color: var(--gris-3); font-style: italic; }

    /* Móvil: Aceptar y Rechazar lado a lado, a todo el ancho */
    .acciones { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .acciones form { margin: 0; display: flex; }
    .acciones .btn { width: 100%; }

    .vacio {
        text-align: center;
        padding: 44px 20px;
        background: var(--blanco);
        border: 1px dashed var(--gris-3);
        border-radius: 14px;
        color: var(--gris-4);
    }

    .vacio svg { width: 40px; height: 40px; margin-bottom: 12px; color: var(--gris-3); }
    .vacio h2 { color: var(--negro); }
    .vacio p { margin: 0; }

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

    /* El formulario ocupa el resto de la hoja y reparte cuerpo + pie */
    .modal-caja form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
    }

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

    .modal-cuerpo {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        align-content: start;
        gap: 16px;
        padding: 18px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    .modal-cuerpo p { margin: 0; color: var(--gris-4); font-size: .95rem; line-height: 1.5; overflow-wrap: anywhere; }

    /* Pie fijo, respeta la barra de gestos. En móvil se apila: la acción principal arriba */
    .modal-pie {
        display: flex;
        flex-direction: column-reverse;
        gap: 10px;
        padding: 12px 18px;
        padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
        border-top: 1px solid var(--gris-1);
        background: var(--blanco);
    }

    .modal-pie .btn { width: 100%; }

    /* ---------- Campos ---------- */
    .campo { display: grid; gap: 6px; min-width: 0; }
    .campo label { font-size: .88rem; font-weight: 600; color: var(--gris-4); }
    .campo small { color: var(--gris-3); font-size: .8rem; text-align: right; }

    /* 16px evita el zoom de iOS al enfocar el campo */
    .campo textarea {
        display: block;
        width: 100%;
        min-height: 120px;
        padding: 12px 14px;
        font: inherit;
        font-size: 16px;
        color: var(--negro);
        background: var(--blanco);
        border: 1px solid var(--gris-3);
        border-radius: 10px;
        resize: vertical;
        -webkit-appearance: none;
        appearance: none;
        transition: border-color 400ms ease;
    }

    .campo textarea:focus { outline: none; border-color: var(--negro); }

    .errores {
        padding: 12px 14px;
        background: var(--gris-1);
        border-left: 3px solid var(--negro);
        border-radius: 6px;
        font-size: .92rem;
    }

    .errores p { overflow-wrap: anywhere; }

    /* Celulares muy angostos: botones de la tarjeta en una columna */
    @media (max-width: 359px) {
        .acciones { grid-template-columns: 1fr; }
    }

    /* ---------- Tablet y escritorio ---------- */
    @media (min-width: 640px) {
        .cab { margin-bottom: 28px; }
        .lista { gap: 20px; }
        .vacio { padding: 56px 20px; }

        .solicitud { gap: 18px; padding: 20px; }
        .avatar { width: 72px; height: 72px; font-size: 1.8rem; }
        .persona { gap: 16px; }
        .persona h3 { font-size: 1.5rem; }

        .datos { gap: 0; }
        .acciones { display: flex; flex-wrap: wrap; }
        .acciones .btn { width: auto; }

        .modal { align-items: center; padding: 24px; }
        .modal-caja { border-radius: 16px; max-height: min(92vh, 780px); }
        .modal-cab::before { display: none; }
        .modal-cab { padding: 18px 16px 14px 22px; }
        .modal-cuerpo { padding: 20px 22px; }
        .modal-pie { flex-direction: row; justify-content: flex-end; padding: 16px 22px; }
        .modal-pie .btn { width: auto; }
    }

    @media (prefers-reduced-motion: reduce) {
        .modal, .modal-caja, .btn, .modal-x { transition: none; }
    }
</style>
@endpush

@section('content')
    <div class="cab" data-reveal>
        <h1>Solicitudes</h1>
        <p>Barberos que enviaron su perfil para aparecer a los clientes.</p>
    </div>

    @if ($solicitudes->isEmpty())
        <div class="vacio" data-reveal>
            <i data-lucide="inbox"></i>
            <h2>No hay solicitudes pendientes</h2>
            <p>Cuando un barbero envíe su perfil, aparecerá aquí.</p>
        </div>
    @else
        <div class="lista">
            @foreach ($solicitudes as $s)
                <article class="solicitud" data-reveal>
                    <div class="persona">
                        <div class="avatar">
                            @if ($s->photo_path)
                                <img src="{{ asset('storage/' . $s->photo_path) }}" alt="{{ $s->name }}" loading="lazy">
                            @else
                                {{ mb_strtoupper(mb_substr($s->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <h3>{{ $s->name }}</h3>
                            <p>{{ $s->email }}</p>
                        </div>
                    </div>

                    <div class="datos">
                        <div class="{{ $s->phone ? '' : 'falta' }}">
                            <i data-lucide="phone"></i>
                            <span>{{ $s->phone ?: 'Sin teléfono' }}</span>
                        </div>
                        <div class="{{ $s->location_address ? '' : 'falta' }}">
                            <i data-lucide="map-pin"></i>
                            <span>{{ $s->location_address ?: 'Sin dirección' }}</span>
                        </div>
                        <div class="{{ $s->bio ? '' : 'falta' }}">
                            <i data-lucide="text"></i>
                            <span>{{ $s->bio ?: 'Sin descripción' }}</span>
                        </div>
                        <div>
                            <i data-lucide="scissors"></i>
                            <span>{{ $s->cortes_count }} {{ $s->cortes_count === 1 ? 'corte seleccionado' : 'cortes seleccionados' }}</span>
                        </div>
                    </div>

                    <div class="acciones">
                        <form method="POST" action="{{ route('admin.solicitudes.aceptar', $s) }}"
                              class="js-aceptar" data-nombre="{{ $s->name }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn"><i data-lucide="check"></i> Aceptar</button>
                        </form>
                        <button type="button" class="btn btn-claro js-rechazar"
                                data-id="{{ $s->id }}" data-nombre="{{ $s->name }}">
                            <i data-lucide="x"></i> Rechazar
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Modal rechazar --}}
    <div class="modal" id="modalRechazo" role="dialog" aria-modal="true" aria-labelledby="rechazoTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="rechazoTitulo">Rechazar solicitud</h2>
                <button type="button" class="modal-x" id="rechazoCerrar" aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="#" id="formRechazo">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_id" id="rechazoId" value="{{ old('_id') }}">

                <div class="modal-cuerpo">
                    <p id="rechazoTexto">El barbero verá este motivo una sola vez en su panel y podrá reenviar la solicitud.</p>

                    @if ($errors->any())
                        <div class="errores" role="alert">
                            @foreach ($errors->all() as $error)
                                <p style="margin:0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="campo">
                        <label for="fMotivo">Motivo del rechazo</label>
                        <textarea id="fMotivo" name="motivo" maxlength="500" required>{{ old('motivo') }}</textarea>
                        <small id="motivoContador" aria-live="off">0 / 500</small>
                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn btn-claro" id="rechazoCancelar">Cancelar</button>
                    <button type="submit" class="btn" id="rechazoEnviar">
                        <i data-lucide="send"></i> Rechazar y enviar motivo
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const BASE = @json(url('panel/admin/solicitudes'));

        const modal = document.getElementById('modalRechazo');
        const caja = modal.querySelector('.modal-caja');
        const form = document.getElementById('formRechazo');
        const campoId = document.getElementById('rechazoId');
        const texto = document.getElementById('rechazoTexto');
        const motivo = document.getElementById('fMotivo');
        const contador = document.getElementById('motivoContador');
        const btnEnviar = document.getElementById('rechazoEnviar');

        let disparador = null;

        function actualizarContador() {
            contador.textContent = motivo.value.length + ' / ' + motivo.maxLength;
        }

        function abrir(origen) {
            disparador = origen || null;
            actualizarContador();
            modal.classList.add('abierto');
            document.documentElement.classList.add('sin-scroll');
            // Con mouse se enfoca el motivo; en el celular se enfoca la caja para no abrir el teclado de golpe
            const conMouse = window.matchMedia('(pointer: fine)').matches;
            setTimeout(function () {
                (conMouse ? motivo : caja).focus({ preventScroll: true });
            }, 60);
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.documentElement.classList.remove('sin-scroll');
            if (disparador && document.contains(disparador)) {
                disparador.focus({ preventScroll: true });
            }
            disparador = null;
        }

        function preparar(id, nombre) {
            campoId.value = id;
            form.action = BASE + '/' + id + '/rechazar';
            texto.textContent = 'Vas a rechazar a ' + nombre + '. Verá el motivo una sola vez en su panel y podrá reenviar la solicitud.';
        }

        document.querySelectorAll('.js-rechazar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                preparar(btn.dataset.id, btn.dataset.nombre);
                motivo.value = '';
                btnEnviar.disabled = false;
                abrir(btn);
            });
        });

        document.getElementById('rechazoCerrar').addEventListener('click', cerrar);
        document.getElementById('rechazoCancelar').addEventListener('click', cerrar);
        modal.addEventListener('click', function (e) { if (e.target === modal) cerrar(); });

        document.addEventListener('keydown', function (e) {
            if (!modal.classList.contains('abierto')) return;

            if (e.key === 'Escape') {
                cerrar();
                return;
            }

            // Mantener el foco dentro del modal
            if (e.key === 'Tab') {
                const foco = caja.querySelectorAll('button:not([disabled]), textarea:not([disabled])');
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

        motivo.addEventListener('input', actualizarContador);

        form.addEventListener('submit', function () {
            btnEnviar.disabled = true;
        });

        // Al volver con el botón "atrás" del celular los botones podrían quedar bloqueados
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            btnEnviar.disabled = false;
            document.querySelectorAll('.js-aceptar button[type="submit"]').forEach(function (b) { b.disabled = false; });
        });

        // Si falló la validación, reabrir el modal con lo escrito
        @if ($errors->any())
            if (campoId.value) {
                preparar(campoId.value, 'este barbero');
                abrir();
            }
        @endif

        // Confirmar antes de aceptar
        document.querySelectorAll('.js-aceptar').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'question',
                    title: 'Aceptar barbero',
                    text: f.dataset.nombre + ' aparecerá a los clientes y podrá recibir citas.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, aceptar',
                    cancelButtonText: 'Cancelar',
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
    })();
</script>
@endpush