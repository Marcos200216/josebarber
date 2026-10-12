@extends('layout.app')

@section('title', 'Solicitudes')

@push('styles')
<style>
    .cab { margin-bottom: 28px; }
    .cab p { margin: 0; color: var(--gris-4); }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border: 1px solid var(--negro);
        border-radius: 8px;
        background: var(--negro);
        color: var(--gris-1);
        font: inherit;
        font-weight: 600;
        font-size: .92rem;
        cursor: pointer;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }

    .btn:hover { background: var(--gris-4); border-color: var(--gris-4); }
    .btn-claro { background: transparent; color: var(--negro); border-color: var(--gris-3); }
    .btn-claro:hover { background: var(--gris-1); color: var(--negro); border-color: var(--negro); }

    .lista { display: grid; gap: 20px; }

    .solicitud {
        display: grid;
        gap: 18px;
        padding: 20px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
    }

    .persona { display: flex; align-items: center; gap: 16px; }

    .avatar {
        width: 72px;
        height: 72px;
        flex-shrink: 0;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gris-1);
        border: 1px solid var(--gris-2);
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--gris-4);
    }

    .avatar img { width: 100%; height: 100%; object-fit: cover; }

    .persona h3 { margin: 0 0 2px; font-size: 1.5rem; }
    .persona p { margin: 0; font-size: .9rem; color: var(--gris-4); word-break: break-word; }

    .datos { display: grid; gap: 8px; font-size: .92rem; }
    .datos div { display: flex; gap: 10px; align-items: flex-start; color: var(--gris-4); }
    .datos svg { width: 18px; height: 18px; margin-top: 2px; color: var(--gris-3); }
    .datos span { color: var(--negro); }
    .datos .falta span { color: var(--gris-3); font-style: italic; }

    .acciones { display: flex; gap: 10px; flex-wrap: wrap; }
    .acciones form { margin: 0; }

    @media (max-width: 480px) {
        .acciones, .acciones form, .acciones .btn { width: 100%; }
    }

    .vacio {
        text-align: center;
        padding: 56px 20px;
        background: var(--blanco);
        border: 1px dashed var(--gris-3);
        border-radius: 12px;
        color: var(--gris-4);
    }

    .vacio svg { width: 40px; height: 40px; margin-bottom: 12px; color: var(--gris-3); }
    .vacio h2 { color: var(--negro); }

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

    .modal-cuerpo { padding: 20px 22px; overflow-y: auto; display: grid; gap: 14px; }
    .modal-cuerpo p { margin: 0; color: var(--gris-4); font-size: .92rem; }

    .modal-pie {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding: 16px 22px;
        border-top: 1px solid var(--gris-1);
    }

    .campo { display: grid; gap: 6px; }
    .campo label { font-size: .85rem; font-weight: 600; color: var(--gris-4); }

    .campo textarea {
        width: 100%;
        min-height: 110px;
        padding: 11px 12px;
        font: inherit;
        color: var(--negro);
        border: 1px solid var(--gris-3);
        border-radius: 8px;
        resize: vertical;
        transition: border-color 400ms ease;
    }

    .campo textarea:focus { outline: none; border-color: var(--negro); }

    .errores {
        padding: 12px 14px;
        background: var(--gris-1);
        border-left: 3px solid var(--negro);
        border-radius: 6px;
        font-size: .9rem;
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
        <div class="modal-caja">
            <div class="modal-cab">
                <h2 id="rechazoTitulo">Rechazar solicitud</h2>
                <button type="button" class="modal-x" id="rechazoCerrar" aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="#" id="formRechazo" style="display:contents">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_id" id="rechazoId" value="{{ old('_id') }}">

                <div class="modal-cuerpo">
                    <p id="rechazoTexto">El barbero verá este motivo una sola vez en su panel y podrá reenviar la solicitud.</p>

                    @if ($errors->any())
                        <div class="errores">
                            @foreach ($errors->all() as $error)
                                <p style="margin:0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="campo">
                        <label for="fMotivo">Motivo del rechazo</label>
                        <textarea id="fMotivo" name="motivo" maxlength="500" required>{{ old('motivo') }}</textarea>
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
        const form = document.getElementById('formRechazo');
        const campoId = document.getElementById('rechazoId');
        const texto = document.getElementById('rechazoTexto');
        const motivo = document.getElementById('fMotivo');

        function abrir() {
            modal.classList.add('abierto');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { motivo.focus(); }, 300);
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.body.style.overflow = '';
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
                abrir();
            });
        });

        document.getElementById('rechazoCerrar').addEventListener('click', cerrar);
        document.getElementById('rechazoCancelar').addEventListener('click', cerrar);
        modal.addEventListener('click', function (e) { if (e.target === modal) cerrar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('abierto')) cerrar();
        });

        form.addEventListener('submit', function () {
            const b = document.getElementById('rechazoEnviar');
            b.disabled = true;
            b.style.opacity = '.6';
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
                    if (r.isConfirmed) f.submit();
                });
            });
        });
    })();
</script>
@endpush