@extends('layout.app')

@section('title', 'Mi galería · ' . config('app.name'))

@push('styles')
<style>
    /* =========================================================
       Mi galería: mobile-first
       Base = celular. Los @media (min-width) ensanchan hacia arriba.
       ========================================================= */

    /* ---------- Encabezado ---------- */
    .encabezado {
        display: grid;
        gap: 14px;
        margin-bottom: 16px;
    }
    .encabezado h1 { margin: 0 0 6px; }
    .encabezado p { margin: 0; color: var(--gris-4); max-width: 60ch; }
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

    .btn-cambiar { width: 100%; margin-top: 10px; }
    .btn-cambiar[hidden] { display: none; }

    .btn:focus-visible,
    .icono-btn:focus-visible,
    .modal-x:focus-visible { outline: 2px solid var(--negro); outline-offset: 2px; }

    .contador {
        margin: 0 0 14px;
        font-size: .9rem;
        font-weight: 600;
        line-height: 1.4;
        color: var(--gris-4);
    }

    /* ---------- Rejilla ---------- */
    .rejilla {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .foto {
        position: relative;
        min-width: 0;
        background: var(--blanco);
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid var(--gris-2);
    }

    .foto-img { position: relative; aspect-ratio: 1 / 1; background: var(--gris-1); overflow: hidden; }
    .foto-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        opacity: 0;
        transition: opacity 500ms ease;
    }
    .foto-img img.cargada { opacity: 1; }

    .esqueleto {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, var(--gris-1) 25%, #f3f3f3 50%, var(--gris-1) 75%);
        background-size: 200% 100%;
        animation: brillo 1.4s linear infinite;
        transition: opacity 400ms ease;
    }
    .esqueleto.oculto { opacity: 0; pointer-events: none; }
    @keyframes brillo { to { background-position: -200% 0; } }

    /* Botones sobre la foto: 42px, fáciles de tocar con el pulgar */
    .foto-acciones {
        position: absolute;
        top: 6px;
        right: 6px;
        display: flex;
        gap: 6px;
    }
    .foto-acciones form { margin: 0; }

    .icono-btn {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 10px;
        color: var(--negro);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, border-color 400ms ease;
    }
    .icono-btn svg { width: 18px; height: 18px; }
    .icono-btn:hover { background: var(--gris-1); border-color: var(--gris-3); }

    .foto-pie {
        padding: 10px 12px;
        font-size: .88rem;
        line-height: 1.4;
        color: var(--gris-4);
        min-height: 3.1em;
        box-sizing: content-box;
        overflow-wrap: anywhere;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .foto-pie.vacia { color: var(--gris-3); font-style: italic; }

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
        max-width: 480px;
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

    /* ---------- Zona de foto ---------- */
    .zona-foto {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        aspect-ratio: 4 / 3;
        max-height: 42vh;
        max-height: 42dvh;
        width: 100%;
        border: 2px dashed var(--gris-2);
        border-radius: 14px;
        background: var(--gris-1);
        color: var(--gris-4);
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        overflow: hidden;
        -webkit-tap-highlight-color: transparent;
        transition: border-color 400ms ease, background 400ms ease;
    }
    .zona-foto:hover { border-color: var(--gris-4); }
    .zona-foto:focus-within { outline: 2px solid var(--negro); outline-offset: 2px; }
    .zona-foto input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .zona-foto img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: none; }
    .zona-foto.con-imagen { border-style: solid; }
    .zona-foto.con-imagen img { display: block; }
    .zona-foto.con-imagen .zona-texto { display: none; }
    .zona-foto.bloqueada { cursor: default; border-style: solid; border-color: var(--gris-2); }
    .zona-foto.bloqueada:hover { border-color: var(--gris-2); }
    .zona-texto { display: grid; justify-items: center; gap: 4px; padding: 12px; }
    .zona-texto svg { width: 32px; height: 32px; }
    .zona-texto small { font-weight: 400; color: var(--gris-3); }

    /* ---------- Campos ---------- */
    .campo { min-width: 0; }
    .campo label.etq { display: block; margin-bottom: 5px; font-size: .85rem; font-weight: 600; color: var(--gris-4); }

    /* 16px evita el zoom de iOS al enfocar el campo */
    .campo input[type="text"] {
        display: block;
        width: 100%;
        min-height: 48px;
        padding: 10px 12px;
        border: 1px solid var(--gris-2);
        border-radius: 10px;
        background: var(--blanco);
        font: inherit;
        font-size: 16px;
        color: var(--negro);
        -webkit-appearance: none;
        appearance: none;
    }
    .campo input:focus { outline: 2px solid var(--gris-4); outline-offset: 1px; border-color: var(--gris-4); }

    /* ---------- Tablet y escritorio ---------- */
    @media (min-width: 640px) {
        .encabezado {
            grid-template-columns: 1fr auto;
            align-items: end;
            margin-bottom: 20px;
        }
        .encabezado .btn { width: auto; }
        .contador { margin-bottom: 16px; }
        .vacio { padding: 40px 20px; }
        .vacio .btn { width: auto; }

        .rejilla { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }

        .foto-acciones { top: 8px; right: 8px; }
        .icono-btn { width: 40px; height: 40px; }

        .modal { align-items: center; padding: 24px; }
        .modal-caja { border-radius: 16px; max-height: min(92vh, 780px); }
        .modal-cab::before { display: none; }
        .modal-cab { padding: 18px 16px 14px 22px; }
        .modal-cuerpo { padding: 20px 22px; }
        .modal-pie { justify-content: flex-end; padding: 16px 22px; }
        .modal-pie .btn { flex: 0 0 auto; }
    }

    @media (min-width: 1200px) {
        .rejilla { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }

    @media (prefers-reduced-motion: reduce) {
        .esqueleto { animation: none; }
        .modal, .modal-caja, .btn, .icono-btn, .modal-x, .foto-img img { transition: none; }
    }
</style>
@endpush

@section('content')
    @php $lleno = $fotos->count() >= $max; @endphp

    <div class="encabezado" data-reveal>
        <div>
            <h1>Mi galería</h1>
            <p>Sube fotos de tus mejores cortes. Los clientes las verán en tu perfil antes de reservar contigo.</p>
        </div>
        <button type="button" class="btn" id="btnNueva" {{ $lleno ? 'disabled' : '' }}>
            <i data-lucide="plus"></i> Agregar foto
        </button>
    </div>

    <p class="contador" data-reveal>
        {{ $fotos->count() }} de {{ $max }} fotos
        @if ($lleno) · Llegaste al máximo: elimina una para agregar otra. @endif
    </p>

    @if ($fotos->isEmpty())
        <div class="vacio" data-reveal>
            <i data-lucide="images"></i>
            <p>Todavía no tienes fotos.<br>Una buena galería ayuda a que los clientes te elijan.</p>
            <button type="button" class="btn btn-claro" id="btnNuevaVacio">
                <i data-lucide="plus"></i> Subir mi primera foto
            </button>
        </div>
    @else
        <div class="rejilla">
            @foreach ($fotos as $f)
                <div class="foto" data-reveal>
                    <div class="foto-img">
                        <div class="esqueleto"></div>
                        <img src="{{ $f['url'] }}" alt="{{ $f['descripcion'] ?: 'Foto de la galería' }}" loading="lazy">
                        <div class="foto-acciones">
                            <button type="button" class="icono-btn js-editar" data-id="{{ $f['id'] }}" aria-label="Editar descripción">
                                <i data-lucide="pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('galeria.destroy', $f['id']) }}" class="js-borrar" data-id="{{ $f['id'] }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icono-btn" aria-label="Eliminar foto">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="foto-pie {{ $f['descripcion'] ? '' : 'vacia' }}">
                        {{ $f['descripcion'] ?: 'Sin descripción' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ---------- Modal agregar / editar ---------- --}}
    <div class="modal" id="modalFoto" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="modalTitulo">Agregar foto</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('galeria.store') }}" id="formFoto" class="modal-form" enctype="multipart/form-data" novalidate>
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

                    <div>
                        <label class="zona-foto" id="zonaFoto">
                            <input type="file" name="imagen" id="inputImagen" accept="image/jpeg,image/png,image/webp">
                            <img id="vistaPrevia" alt="Vista previa">
                            <span class="zona-texto">
                                <i data-lucide="image-plus"></i>
                                <span>Toca para elegir una foto</span>
                                <small>JPG, PNG o WebP</small>
                            </span>
                        </label>
                        <button type="button" class="btn btn-claro btn-cambiar" id="textoCambiar" hidden>
                            <i data-lucide="refresh-cw"></i> Cambiar foto
                        </button>
                    </div>

                    <div class="campo">
                        <label class="etq" for="descripcion">Descripción (opcional)</label>
                        <input type="text" id="descripcion" name="descripcion" maxlength="120" value="{{ old('descripcion') }}" placeholder="Ej. Fade bajo con barba perfilada" enterkeyhint="done">
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
        const modal = document.getElementById('modalFoto');
        const caja = modal.querySelector('.modal-caja');
        const form = document.getElementById('formFoto');
        const titulo = document.getElementById('modalTitulo');
        const metodo = document.getElementById('metodo');
        const editarId = document.getElementById('editarId');
        const zona = document.getElementById('zonaFoto');
        const input = document.getElementById('inputImagen');
        const previa = document.getElementById('vistaPrevia');
        const textoCambiar = document.getElementById('textoCambiar');
        const desc = document.getElementById('descripcion');
        const btnGuardar = document.getElementById('btnGuardar');

        const URL_CREAR = @json(route('galeria.store'));
        const URL_EDITAR = @json(url('/panel/galeria')) + '/';
        const FOTOS = @json($fotos->keyBy('id'));
        const MAX_LADO = 1600;

        let disparador = null;
        let urlTemporal = null;

        function liberarUrl() {
            if (urlTemporal) {
                URL.revokeObjectURL(urlTemporal);
                urlTemporal = null;
            }
        }

        function abrir(origen) {
            disparador = origen || null;
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

        function modoCrear() {
            liberarUrl();
            titulo.textContent = 'Agregar foto';
            form.action = URL_CREAR;
            metodo.disabled = true;
            editarId.value = '';
            input.disabled = false;
            zona.classList.remove('bloqueada', 'con-imagen');
            previa.removeAttribute('src');
            textoCambiar.hidden = true;
        }

        function modoEditar(id) {
            liberarUrl();
            const f = FOTOS[id];
            titulo.textContent = 'Editar foto';
            form.action = URL_EDITAR + id;
            metodo.value = 'PUT';
            metodo.disabled = false;
            editarId.value = id;
            input.value = '';
            input.disabled = false;
            zona.classList.remove('bloqueada');
            zona.classList.add('con-imagen');
            previa.src = f ? f.url : '';
            textoCambiar.hidden = false;
        }

        function nueva(origen) {
            modoCrear();
            input.value = '';
            desc.value = '';
            btnGuardar.disabled = false;
            limpiarErrores();
            abrir(origen);
        }

        function editar(id, origen) {
            modoEditar(id);
            desc.value = (FOTOS[id] && FOTOS[id].descripcion) || '';
            btnGuardar.disabled = false;
            limpiarErrores();
            abrir(origen);
        }

        const btnNueva = document.getElementById('btnNueva');
        if (btnNueva) btnNueva.addEventListener('click', function (e) { nueva(e.currentTarget); });
        const btnVacia = document.getElementById('btnNuevaVacio');
        if (btnVacia) btnVacia.addEventListener('click', function (e) { nueva(e.currentTarget); });

        document.querySelectorAll('.js-editar').forEach(function (b) {
            b.addEventListener('click', function () { editar(b.dataset.id, b); });
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
                const foco = caja.querySelectorAll('button:not([disabled]):not([hidden]), input:not([disabled]):not([type="hidden"]), a[href]');
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

        // Reduce la foto en el navegador antes de enviarla
        function comprimir(archivo) {
            return new Promise(function (resolve) {
                const url = URL.createObjectURL(archivo);
                const img = new Image();
                img.onload = function () {
                    const escala = Math.min(1, MAX_LADO / Math.max(img.naturalWidth, img.naturalHeight));
                    const w = Math.round(img.naturalWidth * escala);
                    const h = Math.round(img.naturalHeight * escala);
                    const lienzo = document.createElement('canvas');
                    lienzo.width = w;
                    lienzo.height = h;
                    const ctx = lienzo.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);
                    URL.revokeObjectURL(url);
                    lienzo.toBlob(function (blob) {
                        if (!blob || blob.size >= archivo.size) return resolve(archivo);
                        const nombre = archivo.name.replace(/\.[^.]+$/, '') + '.jpg';
                        resolve(new File([blob], nombre, { type: 'image/jpeg' }));
                    }, 'image/jpeg', 0.85);
                };
                img.onerror = function () { URL.revokeObjectURL(url); resolve(archivo); };
                img.src = url;
            });
        }

        input.addEventListener('change', function () {
            const archivo = input.files && input.files[0];
            if (!archivo) return;

            btnGuardar.disabled = true;
            comprimir(archivo).then(function (final) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(final);
                    input.files = dt.files;
                } catch (e) { /* si el navegador no lo permite, se envía el original */ }

                liberarUrl();
                urlTemporal = URL.createObjectURL(final);
                previa.src = urlTemporal;
                zona.classList.add('con-imagen');
                textoCambiar.hidden = false;
                btnGuardar.disabled = false;
            });
        });

        // Botón "Cambiar foto": abre el selector de archivos
        textoCambiar.addEventListener('click', function () {
            input.click();
        });

        form.addEventListener('submit', function (e) {
            if (!editarId.value && !(input.files && input.files.length)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'info',
                    title: 'Falta la foto',
                    text: 'Elige una foto para poder guardarla.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#000000',
                });
                return;
            }
            btnGuardar.disabled = true;
        });

        // Al volver con el botón "atrás" del celular el botón podría quedar bloqueado
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) btnGuardar.disabled = false;
        });

        // Imágenes: quita el esqueleto al cargar
        document.querySelectorAll('.foto-img').forEach(function (c) {
            const img = c.querySelector('img');
            const esq = c.querySelector('.esqueleto');
            function listo() {
                img.classList.add('cargada');
                if (esq) esq.classList.add('oculto');
            }
            if (img.complete && img.naturalWidth > 0) listo();
            else {
                img.addEventListener('load', listo);
                img.addEventListener('error', listo);
            }
        });

        // Eliminar con confirmación
        document.querySelectorAll('.js-borrar').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: '¿Eliminar esta foto?',
                    text: 'Se borrará de tu galería y ya no la verán los clientes.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                }).then(function (r) { if (r.isConfirmed) f.submit(); });
            });
        });

        // Si la validación falló, reabre el modal
        @if ($errors->any())
            (function () {
                const id = editarId.value;
                if (id && FOTOS[id]) modoEditar(id); else modoCrear();
                abrir();
            })();
        @endif
    })();
</script>
@endpush