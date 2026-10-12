@extends('layout.app')

@section('title', 'Inicio')

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
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease, opacity 400ms ease;
    }

    .btn:hover:not(:disabled) { background: var(--gris-4); border-color: var(--gris-4); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }
    .btn-claro { background: transparent; color: var(--negro); border-color: var(--gris-3); }
    .btn-claro:hover:not(:disabled) { background: var(--gris-1); color: var(--negro); border-color: var(--negro); }

    .estado-card {
        display: grid;
        gap: 18px;
        padding: 24px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
    }

    .estado-top { display: flex; align-items: flex-start; gap: 16px; }

    .estado-icono {
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--gris-1);
        color: var(--negro);
    }

    .estado-icono svg { width: 26px; height: 26px; }

    .estado-top h2 { margin: 0 0 4px; }
    .estado-top p { margin: 0; color: var(--gris-4); font-size: .95rem; }

    .etiqueta {
        display: inline-block;
        margin-bottom: 8px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        background: var(--gris-1);
        color: var(--gris-4);
        border: 1px solid var(--gris-3);
    }

    .etiqueta.ok { background: var(--negro); color: var(--gris-1); border-color: var(--negro); }

    .acciones { display: flex; gap: 10px; flex-wrap: wrap; }
    .acciones form { margin: 0; }

    @media (max-width: 480px) {
        .acciones, .acciones form, .acciones .btn { width: 100%; }
    }

    .nota { margin: 0; font-size: .85rem; color: var(--gris-3); }

    /* ---------- Aviso de error ---------- */
    .aviso-error {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
        padding: 14px 16px;
        background: var(--blanco);
        border: 1px solid var(--negro);
        border-left-width: 4px;
        border-radius: 8px;
        font-size: .95rem;
    }
    .aviso-error [data-lucide] { margin-top: 2px; }

    /* ---------- Lista de requisitos ---------- */
    .requisitos { border-top: 1px solid var(--gris-1); padding-top: 18px; display: grid; gap: 10px; }
    .requisitos-titulo {
        margin: 0;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--gris-3);
    }
    .requisitos ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }

    .req {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: var(--gris-1);
        border-radius: 8px;
    }
    .req-icono {
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 2px solid var(--gris-3);
        color: transparent;
        background: var(--blanco);
    }
    .req-icono [data-lucide] { width: 14px; height: 14px; stroke-width: 3; }
    .req.listo .req-icono { background: var(--negro); border-color: var(--negro); color: var(--blanco); }
    .req.listo .req-texto { color: var(--gris-4); }

    .req-texto { flex: 1; font-weight: 600; font-size: .95rem; }
    .req-enlace {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .85rem;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
        white-space: nowrap;
    }
    .req-enlace [data-lucide] { width: 16px; height: 16px; }
    .req-enlace:hover { color: var(--gris-4); }

    @media (max-width: 480px) {
        .req { flex-wrap: wrap; }
        .req-enlace { margin-left: 38px; }
    }

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
        max-width: 480px;
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

    .motivo-texto {
        padding: 14px 16px;
        background: var(--gris-1);
        border-left: 3px solid var(--negro);
        border-radius: 6px;
        color: var(--negro) !important;
        font-size: 1rem !important;
        white-space: pre-line;
        word-break: break-word;
    }

    .modal-pie {
        display: flex;
        justify-content: flex-end;
        padding: 16px 22px;
        border-top: 1px solid var(--gris-1);
    }
</style>
@endpush

@section('content')
    <div class="cab" data-reveal>
        <h1>Hola, {{ $user->name }}</h1>
        <p>{{ $user->isAdmin() ? 'Administrador' : 'Barbero' }}</p>
    </div>

    @if ($errors->has('solicitud'))
        <div class="aviso-error" role="alert">
            <i data-lucide="circle-alert"></i>
            <span>{{ $errors->first('solicitud') }}</span>
        </div>
    @endif

    @if ($user->isAdmin())
        <div class="estado-card" data-reveal>
            <div class="estado-top">
                <div class="estado-icono"><i data-lucide="shield-check"></i></div>
                <div>
                    <span class="etiqueta ok">Administrador</span>
                    <h2>Tu perfil aparece a los clientes</h2>
                    <p>Como administrador también eres barbero. Desde el menú gestionas los cortes y las solicitudes.</p>
                </div>
            </div>
        </div>
    @else
        @php $puedeEnviar = in_array($user->status, ['borrador', 'rechazado'], true); @endphp

        <div class="estado-card" data-reveal>
            @if ($user->status === 'borrador')
                <div class="estado-top">
                    <div class="estado-icono"><i data-lucide="file-pen-line"></i></div>
                    <div>
                        <span class="etiqueta">Borrador</span>
                        <h2>Tu perfil aún no está enviado</h2>
                        <p>Completa los requisitos y envía la solicitud al administrador para aparecer a los clientes.</p>
                    </div>
                </div>

            @elseif ($user->status === 'pendiente')
                <div class="estado-top">
                    <div class="estado-icono"><i data-lucide="hourglass"></i></div>
                    <div>
                        <span class="etiqueta">Pendiente</span>
                        <h2>Solicitud en revisión</h2>
                        <p>El administrador está revisando tu perfil. Puedes seguir editándolo mientras tanto.</p>
                    </div>
                </div>

            @elseif ($user->status === 'aceptado')
                <div class="estado-top">
                    <div class="estado-icono"><i data-lucide="badge-check"></i></div>
                    <div>
                        <span class="etiqueta ok">Aceptado</span>
                        <h2>Ya apareces a los clientes</h2>
                        <p>Tu perfil es visible y puedes recibir citas.</p>
                    </div>
                </div>

            @else
                <div class="estado-top">
                    <div class="estado-icono"><i data-lucide="circle-alert"></i></div>
                    <div>
                        <span class="etiqueta">Rechazado</span>
                        <h2>Tu solicitud fue rechazada</h2>
                        <p>Ajusta tu perfil y vuelve a enviar la solicitud cuando estés listo.</p>
                    </div>
                </div>
            @endif

            @if ($puedeEnviar)
                <div class="requisitos">
                    <p class="requisitos-titulo">Requisitos para enviar</p>
                    <ul>
                        @foreach ($requisitos as $req)
                            <li class="req {{ $req['ok'] ? 'listo' : '' }}">
                                <span class="req-icono"><i data-lucide="check"></i></span>
                                <span class="req-texto">{{ $req['texto'] }}</span>
                                @unless ($req['ok'])
                                    <a href="{{ $req['url'] }}" class="req-enlace">
                                        {{ $req['accion'] }} <i data-lucide="arrow-right"></i>
                                    </a>
                                @endunless
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="acciones">
                    @if ($user->status === 'rechazado' && $motivo)
                        <button type="button" class="btn btn-claro" id="btnMotivo">
                            <i data-lucide="message-square-text"></i> Ver motivo
                        </button>
                    @endif
                    <form method="POST" action="{{ route('panel.solicitud') }}" class="js-enviar">
                        @csrf
                        <button type="submit" class="btn" {{ $completo ? '' : 'disabled' }}>
                            <i data-lucide="send"></i>
                            {{ $user->status === 'rechazado' ? 'Reenviar solicitud' : 'Enviar solicitud' }}
                        </button>
                    </form>
                </div>

                @if (! $completo)
                    <p class="nota">El botón se activará cuando completes todos los requisitos.</p>
                @endif

                @if ($user->status === 'rechazado' && $motivo)
                    <p class="nota" id="notaMotivo">El motivo se muestra una sola vez: se borra cuando lo cierres.</p>
                @endif
            @endif
        </div>
    @endif

    @if (! $user->isAdmin() && $user->status === 'rechazado' && $motivo)
        <div class="modal" id="modalMotivo" role="dialog" aria-modal="true" aria-labelledby="motivoTitulo">
            <div class="modal-caja">
                <div class="modal-cab">
                    <h2 id="motivoTitulo">Motivo del rechazo</h2>
                    <button type="button" class="modal-x" data-cerrar-motivo aria-label="Cerrar">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="modal-cuerpo">
                    <p class="motivo-texto">{{ $motivo }}</p>
                    <p>Al cerrar esta ventana el motivo se borrará y no podrás volver a verlo.</p>
                </div>
                <div class="modal-pie">
                    <button type="button" class="btn" data-cerrar-motivo>Entendido</button>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        // Confirmar antes de enviar la solicitud
        document.querySelectorAll('.js-enviar').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'question',
                    title: 'Enviar solicitud',
                    text: 'El administrador revisará tu perfil. ¿Quieres enviarla ahora?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                }).then(function (r) {
                    if (r.isConfirmed) {
                        f.querySelector('button[type="submit"]').disabled = true;
                        f.submit();
                    }
                });
            });
        });

        // Motivo de rechazo: se muestra una vez y se borra al cerrar
        const modal = document.getElementById('modalMotivo');
        if (!modal) return;

        const btnMotivo = document.getElementById('btnMotivo');
        const nota = document.getElementById('notaMotivo');
        const URL_VISTO = @json(route('panel.motivo'));
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let borrado = false;

        function abrir() {
            modal.classList.add('abierto');
            document.body.style.overflow = 'hidden';
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.body.style.overflow = '';

            if (borrado) return;
            borrado = true;

            fetch(URL_VISTO, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                credentials: 'same-origin',
            }).catch(function () { borrado = false; });

            if (btnMotivo) btnMotivo.remove();
            if (nota) nota.textContent = 'Ya viste el motivo. Ajusta tu perfil y reenvía la solicitud.';
        }

        if (btnMotivo) btnMotivo.addEventListener('click', abrir);
        modal.querySelectorAll('[data-cerrar-motivo]').forEach(function (b) {
            b.addEventListener('click', cerrar);
        });
        modal.addEventListener('click', function (e) { if (e.target === modal) cerrar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('abierto')) cerrar();
        });
    })();
</script>
@endpush