@extends('layout.app')

@section('title', 'Cortes')

@push('styles')
<style>
    [hidden] { display: none !important; }

    .cab {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
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

    /* ---------- Buscador y filtros ---------- */
    .barra-filtros {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .buscador { position: relative; flex: 1 1 100%; }
    .buscador [data-lucide] {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        color: var(--gris-3);
        pointer-events: none;
    }
    .buscador input {
        width: 100%;
        padding: 11px 14px 11px 40px;
        font: inherit;
        font-size: 16px;
        color: var(--negro);
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 8px;
        transition: border-color 400ms ease, box-shadow 400ms ease;
    }
    .buscador input::placeholder { color: var(--gris-3); }
    .buscador input:focus { outline: none; border-color: var(--negro); box-shadow: 0 0 0 3px rgba(0, 0, 0, .10); }

    @media (min-width: 640px) {
        .buscador { flex: 1 1 260px; max-width: 360px; }
    }

    .filtros { display: flex; flex-wrap: wrap; gap: 8px; }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        background: var(--blanco);
        color: var(--negro);
        font: inherit;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }
    .chip small { font-size: .8rem; font-weight: 700; color: var(--gris-3); transition: color 400ms ease; }
    .chip:hover { border-color: var(--negro); }
    .chip.activo { background: var(--negro); color: var(--gris-1); border-color: var(--negro); }
    .chip.activo small { color: var(--gris-2); }

    /* ---------- Rejilla ---------- */
    .rejilla { display: grid; grid-template-columns: 1fr; gap: 14px; }
    @media (min-width: 640px) { .rejilla { grid-template-columns: repeat(2, 1fr); gap: 20px; } }
    @media (min-width: 1200px) { .rejilla { grid-template-columns: repeat(3, 1fr); } }

    .tarjeta {
        display: flex;
        flex-direction: column;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 450ms ease, transform 450ms ease;
    }

    .tarjeta:hover { box-shadow: 0 10px 28px rgba(0, 0, 0, .12); transform: translateY(-2px); }
    .tarjeta.inactivo .tarjeta-img,
    .tarjeta.inactivo .tarjeta-cuerpo { opacity: .55; }

    .tarjeta-img {
        position: relative;
        aspect-ratio: 4 / 3;
        background: linear-gradient(100deg, var(--gris-1) 30%, #f4f4f4 50%, var(--gris-1) 70%);
        background-size: 200% 100%;
        animation: esqueleto 1.4s linear infinite;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gris-3);
    }

    .tarjeta-img.sin-imagen { animation: none; background: var(--gris-1); }
    .tarjeta-img.sin-imagen svg { width: 40px; height: 40px; }

    .tarjeta-img img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 500ms ease;
    }

    .tarjeta-img img.cargada { opacity: 1; }
    .tarjeta-img:has(img.cargada) { animation: none; }

    @keyframes esqueleto { to { background-position: -200% 0; } }

    .tarjeta-cuerpo { padding: 16px 18px 6px; flex: 1; }
    .tarjeta-cuerpo h3 { font-size: 1.5rem; margin-bottom: 6px; }

    .meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px 14px;
        margin: 0 0 8px;
        color: var(--gris-4);
        font-size: .9rem;
    }

    .meta span { display: inline-flex; align-items: center; gap: 6px; }
    .meta svg { width: 16px; height: 16px; }

    .precio { font-family: 'Barlow Condensed', sans-serif; font-size: 1.6rem; font-weight: 600; color: var(--negro); }

    .desc {
        margin: 0;
        font-size: .9rem;
        color: var(--gris-4);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .estado {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        background: var(--negro);
        color: var(--gris-1);
    }

    .estado.off { background: var(--gris-1); color: var(--gris-4); border: 1px solid var(--gris-3); }

    .tarjeta-acciones { display: flex; gap: 10px; padding: 14px 18px 18px; }
    .tarjeta-acciones form { flex: 1; margin: 0; }
    .tarjeta-acciones .btn { width: 100%; }
    .tarjeta-acciones > .btn { flex: 1; }

    /* Móvil: tarjeta compacta (imagen a la izquierda, datos a la derecha, botones abajo) */
    @media (max-width: 639px) {
        .tarjeta { display: grid; grid-template-columns: 112px minmax(0, 1fr); }
        .tarjeta-img { grid-column: 1; grid-row: 1; aspect-ratio: auto; min-height: 128px; }
        .tarjeta-cuerpo { grid-column: 2; grid-row: 1; min-width: 0; padding: 12px 14px 8px; }
        .tarjeta-cuerpo h3 { font-size: 1.3rem; margin-bottom: 4px; }
        .desc { -webkit-line-clamp: 1; }
        .tarjeta-acciones { grid-column: 1 / -1; grid-row: 2; padding: 10px 14px 14px; border-top: 1px solid var(--gris-1); }
        .estado { top: 8px; left: 8px; padding: 3px 8px; font-size: .66rem; }
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
    .vacio p { margin: 0; }

    /* ---------- Modal ---------- */
    .modal {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 0;
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

    .campo input[type="text"],
    .campo input[type="number"],
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

    .campo textarea { min-height: 90px; resize: vertical; }
    .campo input:focus, .campo textarea:focus { outline: none; border-color: var(--negro); }

    .fila { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .fila-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
    @media (max-width: 480px) { .fila-3 { grid-template-columns: 1fr 1fr; } }

    .check { display: flex; align-items: center; gap: 10px; font-size: .92rem; cursor: pointer; }
    .check input { width: 18px; height: 18px; accent-color: var(--negro); }

    .previa {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .previa-img {
        width: 96px;
        height: 72px;
        flex-shrink: 0;
        border-radius: 8px;
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
    @php
        $totalActivos = $cortes->where('activo', true)->count();
        $totalInactivos = $cortes->count() - $totalActivos;
    @endphp

    <div class="cab" data-reveal>
        <div>
            <h1>Cortes</h1>
            <p>Catálogo general. Cada barbero elige cuáles de estos realiza.</p>
        </div>
        <button type="button" class="btn" id="btnNuevo">
            <i data-lucide="plus"></i> Nuevo corte
        </button>
    </div>

    @if ($cortes->isEmpty())
        <div class="vacio" data-reveal>
            <i data-lucide="scissors"></i>
            <h2>Aún no hay cortes</h2>
            <p>Crea el primero para que los barberos puedan elegirlo.</p>
        </div>
    @else
        <div class="barra-filtros" data-reveal>
            <div class="buscador">
                <i data-lucide="search"></i>
                <input type="search" id="buscar" placeholder="Buscar un corte por nombre" autocomplete="off" aria-label="Buscar un corte por nombre">
            </div>
            <div class="filtros" role="group" aria-label="Filtrar por estado">
                <button type="button" class="chip activo" data-filtro="todos">Todos <small>{{ $cortes->count() }}</small></button>
                <button type="button" class="chip" data-filtro="activos">Activos <small>{{ $totalActivos }}</small></button>
                <button type="button" class="chip" data-filtro="inactivos">Inactivos <small>{{ $totalInactivos }}</small></button>
            </div>
        </div>

        <div class="vacio" id="sinResultados" hidden style="margin-bottom:20px">
            <i data-lucide="search-x"></i>
            <h2>Sin resultados</h2>
            <p>No hay cortes que coincidan con tu búsqueda o filtro.</p>
        </div>

        <div class="rejilla">
            @foreach ($cortes as $corte)
                <article class="tarjeta {{ $corte->activo ? '' : 'inactivo' }}" data-reveal
                         data-nombre="{{ $corte->nombre }}"
                         data-estado="{{ $corte->activo ? 'activos' : 'inactivos' }}">
                    <div class="tarjeta-img {{ $corte->imagen_url ? '' : 'sin-imagen' }}">
                        <span class="estado {{ $corte->activo ? '' : 'off' }}">
                            {{ $corte->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                        @if ($corte->imagen_url)
                            <img src="{{ $corte->imagen_url }}" alt="{{ $corte->nombre }}" loading="lazy">
                        @else
                            <i data-lucide="image-off"></i>
                        @endif
                    </div>

                    <div class="tarjeta-cuerpo">
                        <h3>{{ $corte->nombre }}</h3>
                        <p class="meta">
                            <span class="precio">₡{{ number_format($corte->precio, 0, ',', ' ') }}</span>
                            <span><i data-lucide="clock"></i> {{ $corte->duracion_minutos }} min</span>
                            <span><i data-lucide="list-ordered"></i> Orden {{ $corte->orden }}</span>
                        </p>
                        @if ($corte->descripcion)
                            <p class="desc">{{ $corte->descripcion }}</p>
                        @endif
                    </div>

                    <div class="tarjeta-acciones">
                        <button type="button" class="btn btn-claro js-editar"
                                data-action="{{ route('admin.cortes.update', $corte) }}"
                                data-id="{{ $corte->id }}"
                                data-nombre="{{ $corte->nombre }}"
                                data-descripcion="{{ $corte->descripcion }}"
                                data-precio="{{ $corte->precio }}"
                                data-duracion="{{ $corte->duracion_minutos }}"
                                data-orden="{{ $corte->orden }}"
                                data-activo="{{ $corte->activo ? '1' : '0' }}"
                                data-imagen="{{ $corte->imagen_url }}">
                            <i data-lucide="pencil"></i> Editar
                        </button>
                        <form method="POST" action="{{ route('admin.cortes.toggle', $corte) }}"
                              class="js-toggle"
                              data-nombre="{{ $corte->nombre }}"
                              data-activo="{{ $corte->activo ? '1' : '0' }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-claro">
                                <i data-lucide="{{ $corte->activo ? 'eye-off' : 'eye' }}"></i>
                                {{ $corte->activo ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Modal crear / editar --}}
    <div class="modal" id="modalCorte" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal-caja">
            <div class="modal-cab">
                <h2 id="modalTitulo">Nuevo corte</h2>
                <button type="button" class="modal-x" id="modalCerrar" aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.cortes.store') }}" enctype="multipart/form-data" id="formCorte" style="display:contents">
                @csrf
                <input type="hidden" name="_method" value="PUT" id="campoMetodo" disabled>
                <input type="hidden" name="_modo" value="{{ old('_modo', 'crear') }}" id="campoModo">
                <input type="hidden" name="_id" value="{{ old('_id') }}" id="campoId">

                <div class="modal-cuerpo">
                    @if ($errors->any())
                        <div class="errores">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="campo">
                        <label for="fNombre">Nombre</label>
                        <input type="text" id="fNombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" required>
                    </div>

                    <div class="campo">
                        <label for="fDescripcion">Descripción</label>
                        <textarea id="fDescripcion" name="descripcion" maxlength="1000">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="fila-3">
                        <div class="campo">
                            <label for="fPrecio">Precio (₡)</label>
                            <input type="number" id="fPrecio" name="precio" value="{{ old('precio') }}" min="0" step="any" inputmode="decimal" required>
                        </div>
                        <div class="campo">
                            <label for="fDuracion">Duración (min)</label>
                            <input type="number" id="fDuracion" name="duracion_minutos" value="{{ old('duracion_minutos', 30) }}" min="5" max="480" step="5" required>
                        </div>
                        <div class="campo">
                            <label for="fOrden">Orden</label>
                            <input type="number" id="fOrden" name="orden" value="{{ old('orden', 0) }}" min="0">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="fImagen">Imagen (JPG, PNG o WEBP, máx. 2 MB)</label>
                        <div class="previa">
                            <div class="previa-img" id="previaCaja">
                                <i data-lucide="image"></i>
                            </div>
                            <input type="file" id="fImagen" name="imagen" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    <label class="check" id="filaQuitar" style="display:none">
                        <input type="checkbox" name="quitar_imagen" value="1" id="fQuitar"> Quitar la imagen actual
                    </label>

                    <label class="check">
                        <input type="checkbox" name="activo" value="1" id="fActivo" {{ old('_modo') ? (old('activo') ? 'checked' : '') : 'checked' }}> Activo (visible para clientes)
                    </label>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn btn-claro" id="modalCancelar">Cancelar</button>
                    <button type="submit" class="btn" id="btnGuardar">
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
        const URL_STORE = @json(route('admin.cortes.store'));
        const URL_UPDATE_BASE = @json(url('panel/admin/cortes'));

        const modal = document.getElementById('modalCorte');
        const form = document.getElementById('formCorte');
        const titulo = document.getElementById('modalTitulo');
        const campoMetodo = document.getElementById('campoMetodo');
        const campoModo = document.getElementById('campoModo');
        const campoId = document.getElementById('campoId');
        const fNombre = document.getElementById('fNombre');
        const fDescripcion = document.getElementById('fDescripcion');
        const fPrecio = document.getElementById('fPrecio');
        const fDuracion = document.getElementById('fDuracion');
        const fOrden = document.getElementById('fOrden');
        const fImagen = document.getElementById('fImagen');
        const fActivo = document.getElementById('fActivo');
        const fQuitar = document.getElementById('fQuitar');
        const filaQuitar = document.getElementById('filaQuitar');
        const previaCaja = document.getElementById('previaCaja');
        const iconoVacio = previaCaja.innerHTML;

        function mostrarPrevia(url) {
            previaCaja.innerHTML = url ? '<img src="' + url + '" alt="Vista previa">' : iconoVacio;
            if (!url && window.lucide) lucide.createIcons();
        }

        function abrir() {
            modal.classList.add('abierto');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { fNombre.focus(); }, 300);
        }

        function cerrar() {
            modal.classList.remove('abierto');
            document.body.style.overflow = '';
        }

        function prepararNuevo() {
            titulo.textContent = 'Nuevo corte';
            form.action = URL_STORE;
            campoMetodo.disabled = true;
            campoModo.value = 'crear';
            campoId.value = '';
            fNombre.value = '';
            fDescripcion.value = '';
            fPrecio.value = '';
            fDuracion.value = 30;
            fOrden.value = 0;
            fImagen.value = '';
            fActivo.checked = true;
            fQuitar.checked = false;
            filaQuitar.style.display = 'none';
            mostrarPrevia(null);
        }

        function prepararEdicion(d) {
            titulo.textContent = 'Editar corte';
            form.action = URL_UPDATE_BASE + '/' + d.id;
            campoMetodo.disabled = false;
            campoModo.value = 'editar';
            campoId.value = d.id;
            fNombre.value = d.nombre || '';
            fDescripcion.value = d.descripcion || '';
            fPrecio.value = d.precio || 0;
            fDuracion.value = d.duracion || 30;
            fOrden.value = d.orden || 0;
            fImagen.value = '';
            fActivo.checked = d.activo === '1';
            fQuitar.checked = false;
            filaQuitar.style.display = d.imagen ? 'flex' : 'none';
            mostrarPrevia(d.imagen || null);
        }

        document.getElementById('btnNuevo').addEventListener('click', function () {
            prepararNuevo();
            abrir();
        });

        document.querySelectorAll('.js-editar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                prepararEdicion(btn.dataset);
                abrir();
            });
        });

        document.getElementById('modalCerrar').addEventListener('click', cerrar);
        document.getElementById('modalCancelar').addEventListener('click', cerrar);
        modal.addEventListener('click', function (e) { if (e.target === modal) cerrar(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('abierto')) cerrar();
        });

        // Vista previa de la imagen elegida
        fImagen.addEventListener('change', function () {
            const archivo = fImagen.files[0];
            if (archivo) {
                mostrarPrevia(URL.createObjectURL(archivo));
                fQuitar.checked = false;
            }
        });

        // Evitar doble envío
        form.addEventListener('submit', function () {
            const b = document.getElementById('btnGuardar');
            b.disabled = true;
            b.style.opacity = '.6';
        });

        // Si hubo errores de validación, reabrir el modal tal como estaba
        @if ($errors->any())
            (function () {
                const esEdicion = campoModo.value === 'editar' && campoId.value;
                titulo.textContent = esEdicion ? 'Editar corte' : 'Nuevo corte';
                form.action = esEdicion ? URL_UPDATE_BASE + '/' + campoId.value : URL_STORE;
                campoMetodo.disabled = !esEdicion;
                abrir();
            })();
        @endif

        // Quitar el esqueleto cuando la imagen termina de cargar
        document.querySelectorAll('.tarjeta-img img').forEach(function (img) {
            if (img.complete) img.classList.add('cargada');
            else img.addEventListener('load', function () { img.classList.add('cargada'); });
        });

        // Confirmación antes de activar o desactivar
        document.querySelectorAll('.js-toggle').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                const activo = f.dataset.activo === '1';
                const nombre = f.dataset.nombre;

                Swal.fire({
                    icon: 'question',
                    title: activo ? 'Desactivar corte' : 'Activar corte',
                    text: activo
                        ? '"' + nombre + '" dejará de aparecer a los clientes. Las citas existentes se conservan.'
                        : '"' + nombre + '" volverá a estar disponible para reservar.',
                    showCancelButton: true,
                    confirmButtonText: activo ? 'Sí, desactivar' : 'Sí, activar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                }).then(function (r) {
                    if (r.isConfirmed) f.submit();
                });
            });
        });

        // ---------- Buscador y filtro Todos / Activos / Inactivos ----------
        (function () {
            const buscar = document.getElementById('buscar');
            const sinResultados = document.getElementById('sinResultados');
            const chips = Array.from(document.querySelectorAll('.chip[data-filtro]'));
            const tarjetas = Array.from(document.querySelectorAll('.rejilla .tarjeta'));
            if (!buscar || !tarjetas.length) return;

            let filtro = 'todos';

            function normalizar(s) {
                return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            const nombres = tarjetas.map(function (t) { return normalizar(t.dataset.nombre); });

            function aplicar() {
                const q = normalizar(buscar.value.trim());
                let visibles = 0;

                tarjetas.forEach(function (t, i) {
                    const okTexto = !q || nombres[i].indexOf(q) !== -1;
                    const okEstado = filtro === 'todos' || t.dataset.estado === filtro;
                    const mostrar = okTexto && okEstado;
                    t.hidden = !mostrar;
                    if (mostrar) visibles++;
                });

                sinResultados.hidden = visibles > 0;
            }

            buscar.addEventListener('input', aplicar);

            chips.forEach(function (c) {
                c.addEventListener('click', function () {
                    filtro = c.dataset.filtro;
                    chips.forEach(function (x) { x.classList.toggle('activo', x === c); });
                    aplicar();
                });
            });
        })();
    })();
</script>
@endpush