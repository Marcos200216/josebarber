@extends('layout.app')

@section('title', 'Mis cortes · ' . config('app.name'))

@push('styles')
<style>
    [hidden] { display: none !important; }

    .encabezado { margin-bottom: 20px; }
    .encabezado p { margin: 0; color: var(--gris-4); max-width: 60ch; }

    .herramientas {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

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
        transition: background var(--transicion), color var(--transicion), opacity var(--transicion);
    }
    .btn:hover:not(:disabled) { background: var(--gris-4); border-color: var(--gris-4); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }

    .btn-claro {
        background: var(--blanco);
        color: var(--negro);
        border-color: var(--gris-2);
    }
    .btn-claro:hover:not(:disabled) { background: var(--gris-1); color: var(--negro); border-color: var(--gris-3); }
    .btn-sm { padding: 8px 14px; font-size: .9rem; }

    /* ---------- Buscador ---------- */
    .buscador {
        position: relative;
        flex: 1 1 100%;
    }
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

    .errores {
        margin-bottom: 16px;
        padding: 12px 16px;
        border: 1px solid var(--gris-4);
        border-radius: 8px;
        background: var(--blanco);
    }
    .errores ul { margin: 0; padding-left: 18px; }

    /* ---------- Rejilla de cortes ---------- */
    .rejilla {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        padding-bottom: 96px; /* espacio para la barra fija */
    }
    @media (min-width: 640px)  { .rejilla { grid-template-columns: repeat(2, 1fr); gap: 14px; } }
    @media (min-width: 1200px) { .rejilla { grid-template-columns: repeat(3, 1fr); } }

    .corte {
        position: relative;
        display: flex;
        flex-direction: column;
        background: var(--blanco);
        border: 2px solid transparent;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: border-color var(--transicion), box-shadow var(--transicion), transform var(--transicion);
    }
    .corte:hover { box-shadow: 0 6px 18px rgba(0, 0, 0, .10); }
    .corte.sel { border-color: var(--negro); }
    .corte:has(input:focus-visible) { outline: 2px solid var(--gris-4); outline-offset: 2px; }

    .corte input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .corte-img {
        position: relative;
        aspect-ratio: 16 / 9;
        background: var(--gris-1);
        overflow: hidden;
    }
    .corte-img img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        opacity: 0;
        transition: opacity 500ms ease;
    }
    .corte-img img.cargada { opacity: 1; }

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

    .sin-imagen {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gris-3);
    }
    .sin-imagen [data-lucide] { width: 36px; height: 36px; }

    .marca {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--blanco);
        border: 2px solid var(--gris-2);
        color: transparent;
        transition: background var(--transicion), border-color var(--transicion), color var(--transicion);
    }
    .marca [data-lucide] { width: 16px; height: 16px; stroke-width: 3; }
    .corte.sel .marca { background: var(--negro); border-color: var(--negro); color: var(--blanco); }

    .corte-info { padding: 14px 16px 16px; }
    .corte-info h3 { margin: 0 0 4px; font-size: 1.4rem; }
    .corte-info p { margin: 0 0 10px; font-size: .92rem; color: var(--gris-4); }
    .datos { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: .9rem; font-weight: 600; }
    .datos span { display: inline-flex; align-items: center; gap: 6px; }
    .datos [data-lucide] { width: 16px; height: 16px; }

    /* Móvil: tarjeta compacta (imagen a la izquierda, datos a la derecha) */
    @media (max-width: 639px) {
        .corte { flex-direction: row; align-items: stretch; }
        .corte-img { width: 112px; min-height: 112px; aspect-ratio: auto; flex-shrink: 0; }
        .corte-info { flex: 1; min-width: 0; padding: 12px 14px; display: flex; flex-direction: column; justify-content: center; }
        .corte-info h3 { font-size: 1.3rem; }
        .corte-info p { display: none; }
        .marca { top: 6px; right: 6px; width: 26px; height: 26px; }
        .marca [data-lucide] { width: 14px; height: 14px; }
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
    #sinResultados { margin-bottom: 16px; }

    /* ---------- Barra de guardado ---------- */
    .barra-guardar {
        position: fixed;
        left: 12px;
        right: 12px;
        bottom: 12px;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: var(--negro);
        color: var(--gris-1);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .25);
    }
    .barra-guardar .contador-sel { font-weight: 600; }
    .barra-guardar small { display: block; color: var(--gris-2); font-weight: 400; }
    .barra-guardar .btn { background: var(--blanco); color: var(--negro); border-color: var(--blanco); }
    .barra-guardar .btn:hover:not(:disabled) { background: var(--gris-1); border-color: var(--gris-1); }

    @media (min-width: 992px) {
        .barra-guardar {
            left: calc(var(--sidebar-ancho) + 48px);
            right: 48px;
            max-width: 1000px;
        }
    }
</style>
@endpush

@section('content')
    <div class="encabezado" data-reveal>
        <h1>Mis cortes</h1>
        <p>Marca los cortes que realizas. Los clientes solo podrán reservar contigo los que elijas aquí.</p>
    </div>

    @if ($errors->any())
        <div class="errores" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($cortes->isEmpty())
        <div class="vacio" data-reveal>
            <i data-lucide="scissors"></i>
            <p>Todavía no hay cortes activos en el catálogo.<br>Cuando el administrador los active, aparecerán aquí.</p>
        </div>
    @else
        <form method="POST" action="{{ route('mis-cortes.update') }}" id="formCortes" novalidate>
            @csrf
            @method('PUT')

            <div class="herramientas" data-reveal>
                @if ($cortes->count() > 6)
                    <div class="buscador">
                        <i data-lucide="search"></i>
                        <input type="search" id="buscar" placeholder="Buscar un corte por nombre" autocomplete="off" aria-label="Buscar un corte por nombre">
                    </div>
                @endif
                <button type="button" class="btn btn-claro btn-sm" id="marcarTodos">
                    <i data-lucide="check-check"></i> <span id="txtMarcar">Marcar todos</span>
                </button>
                <button type="button" class="btn btn-claro btn-sm" id="quitarTodos">
                    <i data-lucide="x"></i> <span id="txtQuitar">Quitar todos</span>
                </button>
            </div>

            <div class="vacio" id="sinResultados" hidden>
                <i data-lucide="search-x"></i>
                <p>No encontramos ningún corte con ese nombre.</p>
            </div>

            <div class="rejilla">
                @foreach ($cortes as $corte)
                    @php $marcado = in_array($corte->id, old('cortes', $seleccionados)); @endphp
                    <label class="corte {{ $marcado ? 'sel' : '' }}" data-reveal data-nombre="{{ $corte->nombre }}">
                        <input type="checkbox" name="cortes[]" value="{{ $corte->id }}" {{ $marcado ? 'checked' : '' }}>

                        <div class="corte-img">
                            @if ($corte->imagen_url)
                                <div class="esqueleto"></div>
                                <img src="{{ $corte->imagen_url }}" alt="{{ $corte->nombre }}" loading="lazy">
                            @else
                                <div class="sin-imagen"><i data-lucide="scissors"></i></div>
                            @endif
                            <span class="marca"><i data-lucide="check"></i></span>
                        </div>

                        <div class="corte-info">
                            <h3>{{ $corte->nombre }}</h3>
                            @if ($corte->descripcion)
                                <p>{{ \Illuminate\Support\Str::limit($corte->descripcion, 90) }}</p>
                            @endif
                            <div class="datos">
                                <span><i data-lucide="banknote"></i> ₡{{ number_format($corte->precio, 0, ',', ' ') }}</span>
                                <span><i data-lucide="clock"></i> {{ $corte->duracion_minutos }} min</span>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="barra-guardar">
                <div class="contador-sel">
                    <span id="contador">0</span> <span id="contadorTexto">cortes</span> marcados
                    <small id="aviso">Sin cambios por guardar</small>
                </div>
                <button type="submit" class="btn" id="btnGuardar" disabled>
                    <i data-lucide="save"></i> Guardar
                </button>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('formCortes');
        if (!form) return;

        const tarjetas = Array.from(form.querySelectorAll('.corte'));
        const checks = tarjetas.map(function (t) { return t.querySelector('input'); });
        const contador = document.getElementById('contador');
        const contadorTexto = document.getElementById('contadorTexto');
        const aviso = document.getElementById('aviso');
        const btnGuardar = document.getElementById('btnGuardar');
        const buscar = document.getElementById('buscar');
        const sinResultados = document.getElementById('sinResultados');
        const txtMarcar = document.getElementById('txtMarcar');
        const txtQuitar = document.getElementById('txtQuitar');
        const esAceptado = @json(auth()->user()->status === 'aceptado');

        // Estado inicial (lo que está guardado en el servidor)
        const inicial = @json($seleccionados);
        const inicialSet = new Set(inicial.map(String));

        function normalizar(s) {
            return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        const nombres = tarjetas.map(function (t) { return normalizar(t.dataset.nombre); });

        function hayCambios() {
            const actual = checks.filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
            if (actual.length !== inicialSet.size) return true;
            return actual.some(function (v) { return !inicialSet.has(v); });
        }

        function actualizar() {
            let total = 0;
            checks.forEach(function (c, i) {
                tarjetas[i].classList.toggle('sel', c.checked);
                if (c.checked) total++;
            });

            contador.textContent = total;
            contadorTexto.textContent = total === 1 ? 'corte' : 'cortes';

            const cambios = hayCambios();
            btnGuardar.disabled = !cambios;
            aviso.textContent = cambios ? 'Tienes cambios sin guardar' : 'Sin cambios por guardar';
        }

        // Buscador: solo oculta tarjetas; las casillas ocultas siguen marcadas y se guardan igual
        function filtrar() {
            const q = normalizar(buscar ? buscar.value.trim() : '');
            let visibles = 0;

            tarjetas.forEach(function (t, i) {
                const coincide = !q || nombres[i].indexOf(q) !== -1;
                t.hidden = !coincide;
                if (coincide) visibles++;
            });

            sinResultados.hidden = visibles > 0;
            txtMarcar.textContent = q ? 'Marcar visibles' : 'Marcar todos';
            txtQuitar.textContent = q ? 'Quitar visibles' : 'Quitar todos';
        }

        if (buscar) buscar.addEventListener('input', filtrar);

        checks.forEach(function (c) { c.addEventListener('change', actualizar); });

        document.getElementById('marcarTodos').addEventListener('click', function () {
            checks.forEach(function (c, i) { if (!tarjetas[i].hidden) c.checked = true; });
            actualizar();
        });
        document.getElementById('quitarTodos').addEventListener('click', function () {
            checks.forEach(function (c, i) { if (!tarjetas[i].hidden) c.checked = false; });
            actualizar();
        });

        // Carga de imágenes: quita el esqueleto al terminar
        tarjetas.forEach(function (t) {
            const img = t.querySelector('img');
            const esq = t.querySelector('.esqueleto');
            if (!img) return;
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

        // Envío: confirma si un barbero aceptado se queda sin cortes
        let confirmado = false;
        form.addEventListener('submit', function (e) {
            if (confirmado) return;
            const total = checks.filter(function (c) { return c.checked; }).length;

            if (total === 0 && esAceptado) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Guardar sin ningún corte?',
                    text: 'Si no marcas cortes, los clientes no podrán reservar contigo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                }).then(function (r) {
                    if (r.isConfirmed) {
                        confirmado = true;
                        btnGuardar.disabled = true;
                        form.submit();
                    }
                });
                return;
            }
            btnGuardar.disabled = true;
        });

        actualizar();
    })();
</script>
@endpush