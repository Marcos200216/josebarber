@extends('layout.app')

@section('title', 'Mi perfil')

@push('styles')
<style>
    /* =========================================================
       Mi perfil: mobile-first
       Base = celular. Los @media (min-width) ensanchan hacia arriba.
       ========================================================= */

    /* ---------- Utilidades ---------- */
    .sr {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    html.sin-scroll,
    html.sin-scroll body { overflow: hidden; }

    /* ---------- Encabezado ---------- */
    .cab {
        display: grid;
        gap: 14px;
        margin-bottom: 20px;
    }

    .cab h1 { margin: 0 0 4px; }
    .cab p { margin: 0; color: var(--gris-4); }

    /* ---------- Botones (objetivo táctil mínimo ~46px) ---------- */
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
        text-decoration: none;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }

    .btn svg { width: 18px; height: 18px; flex-shrink: 0; }
    .btn:hover { background: var(--gris-4); border-color: var(--gris-4); }
    .btn:disabled { opacity: .6; cursor: not-allowed; }
    .btn-claro { background: transparent; color: var(--negro); border-color: var(--gris-3); }
    .btn-claro:hover { background: var(--gris-1); color: var(--negro); border-color: var(--negro); }

    .btn:focus-visible,
    .modal-x:focus-visible,
    .sr:focus-visible + .btn {
        outline: 2px solid var(--negro);
        outline-offset: 2px;
    }

    .cab .btn { width: 100%; }

    /* ---------- Tarjeta de perfil ---------- */
    .perfil-card {
        display: grid;
        gap: 20px;
        padding: 20px 16px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 14px;
    }

    .foto {
        width: 132px;
        height: 132px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gris-1);
        border: 1px solid var(--gris-2);
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 3.25rem;
        font-weight: 600;
        color: var(--gris-4);
    }

    .foto img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .info { min-width: 0; }

    .info h2 {
        margin: 0 0 2px;
        font-size: clamp(1.6rem, 7vw, 2rem);
        text-align: center;
        overflow-wrap: anywhere;
    }

    .info .correo {
        margin: 0 0 14px;
        color: var(--gris-4);
        text-align: center;
        overflow-wrap: anywhere;
    }

    .datos { display: grid; font-size: .95rem; }

    .datos > div {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 13px 0;
        border-top: 1px solid var(--gris-1);
    }

    .datos svg {
        width: 18px;
        height: 18px;
        margin-top: 2px;
        flex-shrink: 0;
        color: var(--gris-3);
    }

    .datos span,
    .datos a {
        min-width: 0;
        color: var(--negro);
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .datos a {
        display: inline-block;
        padding: 2px 0;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .datos .falta span { color: var(--gris-3); font-style: italic; }

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
        max-width: 560px;
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

    /* ---------- Campos ---------- */
    .campo { display: grid; gap: 6px; min-width: 0; }
    .campo > label { font-size: .88rem; font-weight: 600; color: var(--gris-4); }
    .campo small { color: var(--gris-3); font-size: .8rem; line-height: 1.35; }

    /* 16px evita el zoom automático de iOS al enfocar un campo */
    .campo input[type="text"],
    .campo input[type="tel"],
    .campo input[type="url"],
    .campo textarea {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        font: inherit;
        font-size: 16px;
        color: var(--negro);
        background: var(--blanco);
        border: 1px solid var(--gris-3);
        border-radius: 10px;
        -webkit-appearance: none;
        appearance: none;
        transition: border-color 400ms ease;
    }

    .campo textarea { min-height: 120px; resize: vertical; }
    .campo input:focus, .campo textarea:focus { outline: none; border-color: var(--negro); }

    .contador { text-align: right; }

    .check {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 44px;
        font-size: .95rem;
        cursor: pointer;
    }

    .check input { width: 22px; height: 22px; flex-shrink: 0; accent-color: var(--negro); }

    /* ---------- Foto en el modal ---------- */
    .previa { display: flex; align-items: center; gap: 16px; }

    .previa-img {
        width: 88px;
        height: 88px;
        flex-shrink: 0;
        border-radius: 50%;
        background: var(--gris-1);
        border: 1px solid var(--gris-2);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gris-3);
    }

    .previa-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .previa-img svg { width: 32px; height: 32px; }

    .previa-acciones { display: grid; gap: 6px; min-width: 0; justify-items: start; }

    .error-campo { color: var(--negro); font-weight: 600; }

    .errores {
        padding: 12px 14px;
        background: var(--gris-1);
        border-left: 3px solid var(--negro);
        border-radius: 6px;
        font-size: .92rem;
    }

    .errores p { margin: 0 0 4px; overflow-wrap: anywhere; }
    .errores p:last-child { margin: 0; }

    /* ---------- Tablet y escritorio ---------- */
    @media (min-width: 640px) {
        .cab {
            grid-template-columns: 1fr auto;
            align-items: end;
            gap: 16px;
            margin-bottom: 28px;
        }

        .cab .btn { width: auto; }

        .perfil-card {
            grid-template-columns: 180px 1fr;
            align-items: start;
            gap: 24px;
            padding: 24px;
        }

        .foto { width: 160px; height: 160px; margin: 0; font-size: 3.5rem; }
        .info h2 { text-align: left; }
        .info .correo { text-align: left; margin-bottom: 18px; }

        .modal { align-items: center; padding: 24px; }
        .modal-caja { border-radius: 16px; max-height: min(92vh, 780px); }
        .modal-cab::before { display: none; }
        .modal-cab { padding: 18px 16px 14px 22px; }
        .modal-cuerpo { padding: 20px 22px; }
        .modal-pie { justify-content: flex-end; padding: 16px 22px; }
        .modal-pie .btn { flex: 0 0 auto; }
    }

    @media (prefers-reduced-motion: reduce) {
        .modal, .modal-caja, .btn, .modal-x { transition: none; }
    }
</style>
@endpush

@section('content')
    <div class="cab" data-reveal>
        <div>
            <h1>Mi perfil</h1>
            <p>Esto es lo que verán los clientes al elegirte.</p>
        </div>
        <button type="button" class="btn" id="btnEditar">
            <i data-lucide="pencil"></i> Editar perfil
        </button>
    </div>

    <section class="perfil-card" data-reveal>
        <div class="foto">
            @if ($user->foto_url)
                <img src="{{ $user->foto_url }}" alt="{{ $user->name }}">
            @else
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            @endif
        </div>

        <div class="info">
            <h2>{{ $user->name }}</h2>
            <p class="correo">{{ $user->email }}</p>

            <div class="datos">
                <div class="{{ $user->phone ? '' : 'falta' }}">
                    <i data-lucide="phone"></i>
                    <span>{{ $user->phone ?: 'Sin teléfono' }}</span>
                </div>
                <div class="{{ $user->location_address ? '' : 'falta' }}">
                    <i data-lucide="map-pin"></i>
                    <span>{{ $user->location_address ?: 'Sin dirección' }}</span>
                </div>
                <div class="{{ $user->location_url ? '' : 'falta' }}">
                    <i data-lucide="navigation"></i>
                    @if ($user->location_url)
                        <a href="{{ $user->location_url }}" target="_blank" rel="noopener noreferrer">Ver en Google Maps</a>
                    @else
                        <span>Sin enlace de ubicación</span>
                    @endif
                </div>
                <div class="{{ $user->bio ? '' : 'falta' }}">
                    <i data-lucide="text"></i>
                    <span>{{ $user->bio ?: 'Sin descripción' }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal editar --}}
    <div class="modal" id="modalPerfil" role="dialog" aria-modal="true" aria-labelledby="perfilTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="perfilTitulo">Editar perfil</h2>
                <button type="button" class="modal-x" id="perfilCerrar" aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" id="formPerfil">
                @csrf
                @method('PUT')

                <div class="modal-cuerpo">
                    @if ($errors->any())
                        <div class="errores" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="campo">
                        <span style="font-size:.88rem;font-weight:600;color:var(--gris-4)">Foto de perfil</span>
                        <div class="previa">
                            <div class="previa-img" id="previaCaja" data-original="{{ $user->foto_url }}">
                                @if ($user->foto_url)
                                    <img src="{{ $user->foto_url }}" alt="Vista previa">
                                @else
                                    <i data-lucide="user-round"></i>
                                @endif
                            </div>
                            <div class="previa-acciones">
                                <input type="file" id="fFoto" name="foto" class="sr" accept="image/jpeg,image/png,image/webp">
                                <label for="fFoto" class="btn btn-claro">
                                    <i data-lucide="camera"></i> Elegir foto
                                </label>
                                <small>JPG, PNG o WEBP, máx. 2 MB</small>
                                <small class="error-campo" id="fotoError" role="alert" hidden></small>
                            </div>
                        </div>
                    </div>

                    @if ($user->photo_path)
                        <label class="check">
                            <input type="checkbox" name="quitar_foto" value="1" id="fQuitar"> Quitar la foto actual
                        </label>
                    @endif

                    <div class="campo">
                        <label for="fNombre">Nombre</label>
                        <input type="text" id="fNombre" name="name" value="{{ old('name', $user->name) }}" maxlength="100" autocomplete="name" enterkeyhint="next" required>
                    </div>

                    <div class="campo">
                        <label for="fTelefono">Teléfono (WhatsApp)</label>
                        <input type="tel" id="fTelefono" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="tel" autocomplete="tel-national" enterkeyhint="next" placeholder="8888 8888">
                        <small>8 dígitos de Costa Rica. Se guarda con el prefijo +506.</small>
                    </div>

                    <div class="campo">
                        <label for="fBio">Descripción</label>
                        <textarea id="fBio" name="bio" maxlength="1000" placeholder="Cuéntales a los clientes sobre tu experiencia y estilo.">{{ old('bio', $user->bio) }}</textarea>
                        <small class="contador" id="bioContador" aria-live="off">0 / 1000</small>
                    </div>

                    <div class="campo">
                        <label for="fDireccion">Dirección de la barbería</label>
                        <input type="text" id="fDireccion" name="location_address" value="{{ old('location_address', $user->location_address) }}" maxlength="255" autocomplete="street-address" enterkeyhint="next">
                    </div>

                    <div class="campo">
                        <label for="fUrl">Enlace de Google Maps</label>
                        <input type="url" id="fUrl" name="location_url" value="{{ old('location_url', $user->location_url) }}" maxlength="255" inputmode="url" autocapitalize="off" autocorrect="off" enterkeyhint="done" placeholder="https://maps.app.goo.gl/...">
                        <small>En Google Maps toca Compartir y copia el enlace. Se usa para el "cómo llegar" en WhatsApp.</small>
                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn btn-claro" id="perfilCancelar">Cancelar</button>
                    <button type="submit" class="btn" id="perfilGuardar">
                        <i data-lucide="check"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const MAX_BYTES = 2 * 1024 * 1024;

        const modal = document.getElementById('modalPerfil');
        const caja = modal.querySelector('.modal-caja');
        const form = document.getElementById('formPerfil');
        const btnEditar = document.getElementById('btnEditar');
        const btnGuardar = document.getElementById('perfilGuardar');
        const fFoto = document.getElementById('fFoto');
        const fQuitar = document.getElementById('fQuitar');
        const fBio = document.getElementById('fBio');
        const bioContador = document.getElementById('bioContador');
        const fotoError = document.getElementById('fotoError');
        const previaCaja = document.getElementById('previaCaja');
        const original = previaCaja.dataset.original;
        const iconoVacio = '<i data-lucide="user-round"></i>';
        let urlTemporal = null;

        function liberarUrl() {
            if (urlTemporal) {
                URL.revokeObjectURL(urlTemporal);
                urlTemporal = null;
            }
        }

        function mostrarPrevia(url) {
            previaCaja.innerHTML = '';
            if (url) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'Vista previa';
                previaCaja.appendChild(img);
            } else {
                previaCaja.innerHTML = iconoVacio;
                if (window.lucide) lucide.createIcons();
            }
        }

        function errorFoto(texto) {
            fotoError.textContent = texto || '';
            fotoError.hidden = !texto;
        }

        function actualizarContador() {
            bioContador.textContent = fBio.value.length + ' / ' + fBio.maxLength;
        }

        function abrir() {
            modal.classList.add('abierto');
            document.documentElement.classList.add('sin-scroll');
            // Se enfoca la caja (no un campo) para no abrir el teclado de golpe en el celular
            caja.focus({ preventScroll: true });
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.documentElement.classList.remove('sin-scroll');
            btnEditar.focus({ preventScroll: true });
        }

        function cancelar() {
            form.reset();
            liberarUrl();
            errorFoto('');
            mostrarPrevia(original || null);
            actualizarContador();
            cerrar();
        }

        btnEditar.addEventListener('click', abrir);
        document.getElementById('perfilCerrar').addEventListener('click', cancelar);
        document.getElementById('perfilCancelar').addEventListener('click', cancelar);
        modal.addEventListener('click', function (e) { if (e.target === modal) cancelar(); });

        document.addEventListener('keydown', function (e) {
            if (!modal.classList.contains('abierto')) return;

            if (e.key === 'Escape') {
                cancelar();
                return;
            }

            // Mantener el foco dentro del modal
            if (e.key === 'Tab') {
                const foco = caja.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled]), a[href]');
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

        fFoto.addEventListener('change', function () {
            const archivo = fFoto.files[0];
            errorFoto('');
            if (!archivo) return;

            if (archivo.size > MAX_BYTES) {
                fFoto.value = '';
                errorFoto('La foto pesa más de 2 MB. Elegí una más liviana.');
                return;
            }

            liberarUrl();
            urlTemporal = URL.createObjectURL(archivo);
            mostrarPrevia(urlTemporal);
            if (fQuitar) fQuitar.checked = false;
        });

        if (fQuitar) {
            fQuitar.addEventListener('change', function () {
                errorFoto('');
                if (fQuitar.checked) {
                    fFoto.value = '';
                    liberarUrl();
                    mostrarPrevia(null);
                } else {
                    mostrarPrevia(original || null);
                }
            });
        }

        fBio.addEventListener('input', actualizarContador);
        actualizarContador();

        form.addEventListener('submit', function () {
            btnGuardar.disabled = true;
        });

        // Al volver con el botón "atrás" del celular el botón podría quedar bloqueado
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) btnGuardar.disabled = false;
        });

        // Si falló la validación, reabrir el modal con lo escrito
        @if ($errors->any())
            abrir();
        @endif
    })();
</script>
@endpush