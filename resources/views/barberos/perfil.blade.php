@extends('layout.app')

@section('title', 'Mi perfil')

@push('styles')
<style>
    .cab {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
    }

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
        text-decoration: none;
        cursor: pointer;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }

    .btn:hover { background: var(--gris-4); border-color: var(--gris-4); }
    .btn-claro { background: transparent; color: var(--negro); border-color: var(--gris-3); }
    .btn-claro:hover { background: var(--gris-1); color: var(--negro); border-color: var(--negro); }

    .perfil-card {
        display: grid;
        gap: 24px;
        padding: 24px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
    }

    @media (min-width: 640px) {
        .perfil-card { grid-template-columns: 180px 1fr; align-items: start; }
    }

    .foto {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gris-1);
        border: 1px solid var(--gris-2);
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 3.5rem;
        font-weight: 600;
        color: var(--gris-4);
    }

    .foto img { width: 100%; height: 100%; object-fit: cover; }

    .info h2 { margin: 0 0 2px; font-size: 2rem; }
    .info .correo { margin: 0 0 18px; color: var(--gris-4); word-break: break-word; }

    .datos { display: grid; gap: 12px; font-size: .95rem; }
    .datos div { display: flex; gap: 12px; align-items: flex-start; }
    .datos svg { width: 18px; height: 18px; margin-top: 3px; color: var(--gris-3); }
    .datos span, .datos a { color: var(--negro); word-break: break-word; white-space: pre-line; }
    .datos .falta span { color: var(--gris-3); font-style: italic; }

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
        max-width: 560px;
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

    .modal-cuerpo { padding: 20px 22px; overflow-y: auto; display: grid; gap: 16px; }

    .modal-pie {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding: 16px 22px;
        border-top: 1px solid var(--gris-1);
    }

    .campo { display: grid; gap: 6px; }
    .campo label { font-size: .85rem; font-weight: 600; color: var(--gris-4); }
    .campo small { color: var(--gris-3); font-size: .78rem; }

    .campo input[type="text"],
    .campo input[type="tel"],
    .campo input[type="url"],
    .campo textarea {
        width: 100%;
        padding: 11px 12px;
        font: inherit;
        color: var(--negro);
        background: var(--blanco);
        border: 1px solid var(--gris-3);
        border-radius: 8px;
        transition: border-color 400ms ease;
    }

    .campo textarea { min-height: 110px; resize: vertical; }
    .campo input:focus, .campo textarea:focus { outline: none; border-color: var(--negro); }

    .check { display: flex; align-items: center; gap: 10px; font-size: .92rem; cursor: pointer; }
    .check input { width: 18px; height: 18px; accent-color: var(--negro); }

    .previa { display: flex; align-items: center; gap: 14px; }

    .previa-img {
        width: 72px;
        height: 72px;
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

    .previa-img img { width: 100%; height: 100%; object-fit: cover; }

    .errores {
        padding: 12px 14px;
        background: var(--gris-1);
        border-left: 3px solid var(--negro);
        border-radius: 6px;
        font-size: .9rem;
    }

    .errores p { margin: 0 0 4px; }
    .errores p:last-child { margin: 0; }
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
        <div class="modal-caja">
            <div class="modal-cab">
                <h2 id="perfilTitulo">Editar perfil</h2>
                <button type="button" class="modal-x" id="perfilCerrar" aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data" id="formPerfil" style="display:contents">
                @csrf
                @method('PUT')

                <div class="modal-cuerpo">
                    @if ($errors->any())
                        <div class="errores">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="campo">
                        <label for="fFoto">Foto de perfil (JPG, PNG o WEBP, máx. 2 MB)</label>
                        <div class="previa">
                            <div class="previa-img" id="previaCaja" data-original="{{ $user->foto_url }}">
                                @if ($user->foto_url)
                                    <img src="{{ $user->foto_url }}" alt="Vista previa">
                                @else
                                    <i data-lucide="user-round"></i>
                                @endif
                            </div>
                            <input type="file" id="fFoto" name="foto" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    @if ($user->photo_path)
                        <label class="check">
                            <input type="checkbox" name="quitar_foto" value="1" id="fQuitar"> Quitar la foto actual
                        </label>
                    @endif

                    <div class="campo">
                        <label for="fNombre">Nombre</label>
                        <input type="text" id="fNombre" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required>
                    </div>

                    <div class="campo">
                        <label for="fTelefono">Teléfono (WhatsApp)</label>
                        <input type="tel" id="fTelefono" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="tel" placeholder="8888 8888">
                        <small>8 dígitos de Costa Rica. Se guarda con el prefijo +506.</small>
                    </div>

                    <div class="campo">
                        <label for="fBio">Descripción</label>
                        <textarea id="fBio" name="bio" maxlength="1000" placeholder="Cuéntales a los clientes sobre tu experiencia y estilo.">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="campo">
                        <label for="fDireccion">Dirección de la barbería</label>
                        <input type="text" id="fDireccion" name="location_address" value="{{ old('location_address', $user->location_address) }}" maxlength="255">
                    </div>

                    <div class="campo">
                        <label for="fUrl">Enlace de Google Maps</label>
                        <input type="url" id="fUrl" name="location_url" value="{{ old('location_url', $user->location_url) }}" maxlength="255" placeholder="https://maps.app.goo.gl/...">
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
        const modal = document.getElementById('modalPerfil');
        const form = document.getElementById('formPerfil');
        const fFoto = document.getElementById('fFoto');
        const fQuitar = document.getElementById('fQuitar');
        const previaCaja = document.getElementById('previaCaja');
        const original = previaCaja.dataset.original;
        const iconoVacio = '<i data-lucide="user-round"></i>';

        function mostrarPrevia(url) {
            if (url) {
                previaCaja.innerHTML = '<img src="' + url + '" alt="Vista previa">';
            } else {
                previaCaja.innerHTML = iconoVacio;
                if (window.lucide) lucide.createIcons();
            }
        }

        function abrir() {
            modal.classList.add('abierto');
            document.body.style.overflow = 'hidden';
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.body.style.overflow = '';
        }

        function cancelar() {
            form.reset();
            mostrarPrevia(original || null);
            cerrar();
        }

        document.getElementById('btnEditar').addEventListener('click', abrir);
        document.getElementById('perfilCerrar').addEventListener('click', cancelar);
        document.getElementById('perfilCancelar').addEventListener('click', cancelar);
        modal.addEventListener('click', function (e) { if (e.target === modal) cancelar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('abierto')) cancelar();
        });

        fFoto.addEventListener('change', function () {
            const archivo = fFoto.files[0];
            if (archivo) {
                mostrarPrevia(URL.createObjectURL(archivo));
                if (fQuitar) fQuitar.checked = false;
            }
        });

        if (fQuitar) {
            fQuitar.addEventListener('change', function () {
                if (fQuitar.checked) {
                    fFoto.value = '';
                    mostrarPrevia(null);
                } else {
                    mostrarPrevia(original || null);
                }
            });
        }

        form.addEventListener('submit', function () {
            const b = document.getElementById('perfilGuardar');
            b.disabled = true;
            b.style.opacity = '.6';
        });

        // Si falló la validación, reabrir el modal con lo escrito
        @if ($errors->any())
            abrir();
        @endif
    })();
</script>
@endpush