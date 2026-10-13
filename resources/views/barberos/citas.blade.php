@extends('layout.app')

@section('title', 'Citas · ' . config('app.name'))

@push('styles')
<style>
    /* =========================================================
       Citas: mobile-first
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
    .btn-sm { min-height: 44px; padding: 8px 12px; font-size: .92rem; }

    .btn:focus-visible,
    .modal-x:focus-visible,
    .chip:focus-visible,
    .cita-tel:focus-visible { outline: 2px solid var(--negro); outline-offset: 2px; }

    /* ---------- Resumen ---------- */
    .resumen { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .dato {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        font-size: .9rem;
        font-weight: 600;
    }
    .dato strong { font-family: 'Barlow Condensed', sans-serif; font-size: 1.25rem; line-height: 1; }

    /* ---------- Grupos y tarjetas ---------- */
    .grupo { margin-bottom: 24px; }
    .grupo-titulo {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 2px 10px;
        margin: 0 0 10px;
        font-size: 1.5rem;
    }
    .grupo-nota {
        flex: 1 1 100%;
        font-family: 'Manrope', sans-serif;
        font-size: .82rem;
        font-weight: 500;
        line-height: 1.4;
        color: var(--gris-4);
    }

    .lista { display: grid; gap: 12px; }

    .cita {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
        padding: 14px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 14px;
    }
    .cita.por-cerrar { border: 2px solid var(--negro); padding: 13px; }

    /* Móvil: hora grande y "hasta" en la misma línea */
    .cita-hora {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 2px 10px;
        font-family: 'Barlow Condensed', sans-serif;
        font-size: 2rem;
        font-weight: 600;
        line-height: 1;
    }
    .cita-hora small {
        font-family: 'Manrope', sans-serif;
        font-size: .82rem;
        font-weight: 600;
        color: var(--gris-4);
    }

    .cita-info { min-width: 0; }
    .cita-info h3 { margin: 0 0 2px; font-size: 1.4rem; overflow-wrap: anywhere; }
    .cita-info p { margin: 0; font-size: .92rem; color: var(--gris-4); overflow-wrap: anywhere; }

    /* Enlace de WhatsApp con área táctil de 44px */
    .cita-tel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        margin-top: 2px;
        font-size: .95rem;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
        -webkit-tap-highlight-color: transparent;
    }
    .cita-tel svg { width: 18px; height: 18px; }
    .cita-tel:hover { color: var(--gris-4); }

    /* Móvil: acciones en rejilla de 2 columnas (2 o 4 botones) */
    .cita-acciones {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--gris-1);
    }
    .cita-acciones form { margin: 0; display: flex; }
    .cita-acciones .btn { width: 100%; }

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

    .vacio {
        padding: 36px 18px;
        text-align: center;
        background: var(--blanco);
        border-radius: 14px;
        color: var(--gris-4);
    }
    .vacio [data-lucide],
    .vacio svg { width: 36px; height: 36px; color: var(--gris-3); margin-bottom: 8px; }
    .vacio p { margin: 0; }

    /* ---------- Modales (hoja inferior en móvil) ---------- */
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
        gap: 16px;
        padding: 18px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    /* Modal sin pie: respeta la barra de gestos al final de la lista */
    .modal-cuerpo.sin-pie { padding-bottom: calc(18px + env(safe-area-inset-bottom, 0px)); }

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

    .info-cita {
        margin: 0;
        padding: 12px 14px;
        background: var(--gris-1);
        border-radius: 10px;
        font-size: .92rem;
        overflow-wrap: anywhere;
    }
    .nota { margin: 0; font-size: .85rem; color: var(--gris-4); }

    /* ---------- Campos ---------- */
    .fila-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .campo { min-width: 0; }
    .campo label.etq { display: block; margin-bottom: 5px; font-size: .85rem; font-weight: 600; color: var(--gris-4); }

    /* 16px evita el zoom de iOS; appearance:none corrige el alto raro de date/time en iPhone */
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

    /* ---------- Historial ---------- */
    /* Los filtros quedan fijos arriba mientras la lista hace scroll */
    .modal-filtros {
        padding: 12px 18px;
        border-bottom: 1px solid var(--gris-1);
        background: var(--blanco);
    }

    .chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .chip {
        min-height: 40px;
        padding: 6px 14px;
        border: 1px solid var(--gris-2);
        border-radius: 999px;
        background: var(--blanco);
        font: inherit;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }
    .chip:hover { background: var(--gris-1); border-color: var(--gris-3); }
    .chip.activo { background: var(--negro); color: var(--blanco); border-color: var(--negro); }

    .hist { display: grid; gap: 8px; }
    .hist-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: var(--gris-1);
        border-radius: 10px;
    }
    .hist-item[hidden] { display: none; }
    .hist-item > div { min-width: 0; }
    .hist-item strong { display: block; overflow-wrap: anywhere; }
    .hist-item span { font-size: .85rem; color: var(--gris-4); overflow-wrap: anywhere; }
    .hist-estado {
        flex-shrink: 0;
        padding: 3px 10px;
        border: 1px solid var(--gris-3);
        border-radius: 999px;
        background: var(--blanco);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--gris-4);
    }
    .hist-vacio { margin: 0; padding: 20px 0; text-align: center; color: var(--gris-4); }
    .hist-vacio[hidden] { display: none; }

    /* Celulares muy angostos: un campo por fila */
        /* ---------- Botones del encabezado ---------- */
    .encabezado-botones { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

    /* ---------- Calendario ---------- */
    .cal { display: grid; gap: 6px; }
    .cal-nav { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .cal-nav strong { font-family: 'Barlow Condensed', sans-serif; font-size: 1.5rem; font-weight: 600; text-align: center; }

    .cal-flecha {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 10px;
        color: var(--negro);
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease;
    }
    .cal-flecha:hover:not(:disabled) { background: var(--gris-1); }
    .cal-flecha:disabled { opacity: .35; cursor: not-allowed; }

    .cal-semana,
    .cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px; }
    .cal-semana span { text-align: center; font-size: .72rem; font-weight: 800; letter-spacing: .08em; color: var(--gris-3); }

    .cal-dia {
        min-width: 0;
        height: 54px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 0;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 10px;
        font: inherit;
        color: var(--negro);
        cursor: pointer;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
    }
    .cal-dia b { font-family: 'Barlow Condensed', sans-serif; font-size: 1.3rem; font-weight: 600; line-height: 1; }
    .cal-dia:hover:not(:disabled):not(.sel) { border-color: var(--negro); }
    .cal-dia.hoy { border: 2px solid var(--negro); }
    .cal-dia.sel { background: var(--negro); color: var(--blanco); border-color: var(--negro); }
    .cal-dia:disabled { opacity: .38; background: var(--gris-1); cursor: not-allowed; }
    .cal-dia:focus-visible,
    .cal-flecha:focus-visible { outline: 2px solid var(--negro); outline-offset: 2px; }

    .cal-cuenta {
        min-width: 18px;
        padding: 0 5px;
        border-radius: 999px;
        background: var(--negro);
        color: var(--blanco);
        font-size: .68rem;
        font-weight: 700;
        line-height: 16px;
        text-align: center;
    }
    .cal-cuenta.cero { visibility: hidden; }
    .cal-dia.sel .cal-cuenta { background: var(--blanco); color: var(--negro); }

    .cal-error { grid-column: 1 / -1; display: grid; gap: 10px; justify-items: start; padding: 12px 14px; border: 1px solid var(--gris-4); border-left-width: 4px; border-radius: 8px; font-size: .92rem; }
    .cal-error p { margin: 0; }

    /* ---------- Agenda del día ---------- */
    .agenda { display: grid; gap: 10px; }
    .agenda-cab h3 { margin: 0 0 4px; font-size: 1.6rem; }
    .agenda-cab p { margin: 0; font-size: .9rem; color: var(--gris-4); }
    .agenda-cab .agenda-resumen { margin-top: 4px; font-weight: 700; color: var(--negro); }
    .agenda-lista { display: grid; gap: 8px; }
    .agenda-sub { margin: 6px 0 0; font-size: .78rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--gris-3); }

    .fila-hora {
        display: grid;
        grid-template-columns: 84px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        padding: 10px 12px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
    }
    .fila-hora .hh { font-family: 'Barlow Condensed', sans-serif; font-size: 1.25rem; font-weight: 600; line-height: 1.3; }
        .fila-hora .hh small { display: block; font-family: 'Manrope', sans-serif; font-size: .72rem; font-weight: 600; line-height: 1.3; color: var(--gris-4); }
    .fila-hora .estado { min-width: 0; display: grid; gap: 8px; font-size: .92rem; color: var(--gris-4); overflow-wrap: anywhere; }
    .fila-hora .libre-txt { color: var(--negro); line-height: 1.6; }
    .fila-hora.cita { border: 2px solid var(--negro); padding: 9px 11px; }
    .fila-hora.continua,
    .fila-hora.bloqueo,
    .fila-hora.pasada { background: var(--gris-1); border-color: var(--gris-1); }
    .fila-hora.pasada .hh { color: var(--gris-3); }

    .agenda-cita { display: grid; gap: 2px; }
    .agenda-cita strong { color: var(--negro); font-size: 1rem; }
    .agenda-wa { display: inline-flex; align-items: center; min-height: 32px; font-weight: 600; color: var(--negro); text-decoration: underline; text-underline-offset: 3px; }

    @media (min-width: 640px) {
        .encabezado-botones { display: flex; gap: 10px; }
        .cal-dia { height: 58px; }
    }
    @media (max-width: 359px) {
        .fila-2 { grid-template-columns: 1fr; }
        .cita-acciones { grid-template-columns: 1fr; }
    }

    /* ---------- Tablet y escritorio ---------- */
    @media (min-width: 640px) {
        .encabezado {
            grid-template-columns: 1fr auto;
            align-items: end;
            margin-bottom: 18px;
        }
        .encabezado .btn { width: auto; }
        .resumen { gap: 10px; margin-bottom: 22px; }
        .grupo { margin-bottom: 26px; }
        .grupo-nota { flex: 0 1 auto; }
        .vacio { padding: 40px 20px; }

        .cita {
            grid-template-columns: 130px minmax(0, 1fr);
            gap: 14px;
            padding: 16px 18px;
        }
        .cita.por-cerrar { padding: 15px 17px; }

        .cita-hora { display: block; }
        .cita-hora small { display: block; margin-top: 4px; }

        .cita-tel { min-height: 0; margin-top: 6px; font-size: .9rem; }
        .cita-tel svg { width: 16px; height: 16px; }

        .cita-acciones {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
        }
        .cita-acciones .btn { width: auto; }

        .modal { align-items: center; padding: 24px; }
        .modal-caja { border-radius: 16px; max-height: min(92vh, 780px); }
        .modal-cab::before { display: none; }
        .modal-cab { padding: 18px 16px 14px 22px; }
        .modal-cuerpo { padding: 20px 22px; }
        .modal-cuerpo.sin-pie { padding-bottom: 20px; }
        .modal-filtros { padding: 14px 22px; }
        .modal-pie { justify-content: flex-end; padding: 16px 22px; }
        .modal-pie .btn { flex: 0 0 auto; }
    }

    @media (prefers-reduced-motion: reduce) {
        .modal, .modal-caja, .btn, .modal-x, .chip { transition: none; }
    }
</style>
@endpush

@section('content')
    <div class="encabezado" data-reveal>
        <div>
            <h1>Citas</h1>
            <p>Tus citas pendientes. Marca cada una como terminada cuando el cliente se atienda.</p>
        </div>
                <div class="encabezado-botones">
            <button type="button" class="btn btn-claro" id="btnCalendario">
                <i data-lucide="calendar-days"></i> Calendario
            </button>
            <button type="button" class="btn btn-claro" id="btnHistorial">
                <i data-lucide="history"></i> Terminadas
            </button>
        </div>
    </div>

    <div class="resumen" data-reveal>
        <span class="dato"><strong>{{ $hoy }}</strong> {{ $hoy === 1 ? 'cita hoy' : 'citas hoy' }}</span>
        <span class="dato"><strong>{{ $total }}</strong> {{ $total === 1 ? 'pendiente en total' : 'pendientes en total' }}</span>
    </div>

    @if (count($grupos) === 0)
        <div class="vacio" data-reveal>
            <i data-lucide="calendar-check"></i>
            <p>No tienes citas pendientes.<br>Cuando un cliente reserve contigo, aparecerá aquí.</p>
        </div>
    @else
        @foreach ($grupos as $g)
            <section class="grupo" data-reveal>
                <h2 class="grupo-titulo">
                    {{ $g['titulo'] }}
                    @if ($g['por_cerrar'])
                        <span class="grupo-nota">Ya pasó su hora: márcalas como terminadas o no asistió.</span>
                    @endif
                </h2>

                <div class="lista">
                    @foreach ($g['citas'] as $c)
                        <article class="cita {{ $g['por_cerrar'] ? 'por-cerrar' : '' }}">
                            <div class="cita-hora">
                                {{ $c['hora_ini'] }}
                                <small>
                                    {{ $g['por_cerrar'] ? $c['dia'] . ' · ' : '' }}hasta {{ $c['hora_fin'] }}
                                </small>
                            </div>

                            <div class="cita-info">
                                <h3>{{ $c['cliente'] }}</h3>
                                <p>
                                    {{ $c['corte'] }} · {{ $c['minutos'] }} min
                                    @if ($c['precio']) · {{ $c['precio'] }} @endif
                                </p>
                                <a class="cita-tel" href="https://wa.me/{{ $c['wa'] }}" target="_blank" rel="noopener">
                                    <i data-lucide="message-circle"></i> {{ $c['telefono'] }}
                                </a>
                            </div>

                            <div class="cita-acciones">
                                @if ($c['ya_inicio'])
                                    <form method="POST" action="{{ route('citas.completar', $c['id']) }}" class="js-accion"
                                          data-titulo="¿Marcar como terminada?"
                                          data-texto="Se moverá a Terminadas."
                                          data-boton="Sí, terminada">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm"><i data-lucide="check"></i> Terminada</button>
                                    </form>

                                    <form method="POST" action="{{ route('citas.no-asistio', $c['id']) }}" class="js-accion"
                                          data-titulo="¿El cliente no asistió?"
                                          data-texto="La cita quedará registrada como &quot;no asistió&quot;."
                                          data-boton="Sí, no asistió">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-claro btn-sm"><i data-lucide="user-x"></i> No asistió</button>
                                    </form>
                                @endif

                                <button type="button" class="btn btn-claro btn-sm js-editar" data-cita="{{ json_encode($c['editar']) }}">
                                    <i data-lucide="calendar-clock"></i> Editar hora
                                </button>

                                <form method="POST" action="{{ route('citas.cancelar', $c['id']) }}" class="js-accion"
                                      data-titulo="¿Cancelar esta cita?"
                                      data-texto="El espacio quedará libre para otros clientes."
                                      data-boton="Sí, cancelar">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-claro btn-sm"><i data-lucide="x"></i> Cancelar</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    {{-- ---------- Modal editar hora ---------- --}}
    <div class="modal" id="modalHora" role="dialog" aria-modal="true" aria-labelledby="horaTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="horaTitulo">Editar hora</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form method="POST" action="#" id="formHora" class="modal-form" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="editar_id" id="editarId" value="{{ old('editar_id') }}">

                <div class="modal-cuerpo">
                    @if ($errors->hasAny(['fecha', 'hora']))
                        <div class="errores" role="alert">
                            <ul>
                                @foreach (['fecha', 'hora'] as $k)
                                    @foreach ($errors->get($k) as $m)
                                        <li>{{ $m }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="info-cita" id="infoCita"></p>

                    <div class="fila-2">
                        <div class="campo">
                            <label class="etq" for="fecha">Nueva fecha</label>
                            <input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}" required>
                        </div>
                        <div class="campo">
                            <label class="etq" for="hora">Nueva hora</label>
                            <input type="time" id="hora" name="hora" value="{{ old('hora') }}" step="900" required>
                        </div>
                    </div>

                    <p class="nota" id="notaDuracion"></p>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn btn-claro" data-cerrar>Cancelar</button>
                    <button type="submit" class="btn" id="btnGuardarHora">
                        <i data-lucide="save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------- Modal terminadas / historial ---------- --}}
    <div class="modal" id="modalHistorial" role="dialog" aria-modal="true" aria-labelledby="histTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="histTitulo">Historial de citas</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="modal-filtros">
                <div class="chips" role="group" aria-label="Filtrar historial">
                    <button type="button" class="chip activo" data-filtro="completada" aria-pressed="true">Terminadas</button>
                    <button type="button" class="chip" data-filtro="cancelada" aria-pressed="false">Canceladas</button>
                    <button type="button" class="chip" data-filtro="no_asistio" aria-pressed="false">No asistió</button>
                </div>
            </div>

            <div class="modal-cuerpo sin-pie">
                <div class="hist">
                    @foreach ($historial as $h)
                        <div class="hist-item" data-estado="{{ $h['estado'] }}">
                            <div>
                                <strong>{{ $h['cliente'] }}</strong>
                                <span>{{ $h['corte'] }} · {{ $h['fecha'] }}</span>
                            </div>
                            <span class="hist-estado">{{ $h['etiqueta'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="hist-vacio" id="histVacio" hidden>No hay citas en esta categoría.</p>
                <p class="nota">Se muestran las 60 citas más recientes.</p>
            </div>
        </div>
    </div>

        {{-- ---------- Modal calendario ---------- --}}
    <div class="modal" id="modalCalendario" role="dialog" aria-modal="true" aria-labelledby="calModalTitulo">
        <div class="modal-caja" tabindex="-1">
            <div class="modal-cab">
                <h2 id="calModalTitulo">Calendario</h2>
                <button type="button" class="modal-x" data-cerrar aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="modal-cuerpo sin-pie">
                <div class="cal-nav">
                    <button type="button" class="cal-flecha" id="calAnt" aria-label="Mes anterior">
                        <i data-lucide="chevron-left"></i>
                    </button>
                    <strong id="calTitulo" aria-live="polite"></strong>
                    <button type="button" class="cal-flecha" id="calSig" aria-label="Mes siguiente">
                        <i data-lucide="chevron-right"></i>
                    </button>
                </div>

                <div class="cal">
                    <div class="cal-semana" aria-hidden="true">
                        <span>D</span><span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span>
                    </div>
                    <div class="cal-grid" id="calGrid"></div>
                </div>

                <div class="agenda" id="calAgenda" aria-live="polite"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const URL_BASE = @json(url('/panel/citas')) + '/';

        // ---------- Modales (apertura, cierre y foco) ----------
        let activo = null;
        let disparador = null;

        function abrir(m, origen) {
            activo = m;
            disparador = origen || null;
            m.classList.add('abierto');
            document.documentElement.classList.add('sin-scroll');
            // Se enfoca la caja (no un campo) para no abrir el teclado de golpe en el celular
            m.querySelector('.modal-caja').focus({ preventScroll: true });
        }

        function cerrar(m) {
            m = m || activo;
            if (!m) return;
            m.classList.remove('abierto');
            document.documentElement.classList.remove('sin-scroll');
            if (disparador && document.contains(disparador)) {
                disparador.focus({ preventScroll: true });
            }
            activo = null;
            disparador = null;
        }

        document.querySelectorAll('.modal').forEach(function (m) {
            m.querySelectorAll('[data-cerrar]').forEach(function (b) {
                b.addEventListener('click', function () { cerrar(m); });
            });
            m.addEventListener('click', function (e) { if (e.target === m) cerrar(m); });
        });

        document.addEventListener('keydown', function (e) {
            if (!activo) return;

            if (e.key === 'Escape') {
                cerrar();
                return;
            }

            // Mantener el foco dentro del modal abierto
            if (e.key === 'Tab') {
                const caja = activo.querySelector('.modal-caja');
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

        // ---------- Acciones con confirmación ----------
        document.querySelectorAll('.js-accion').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'question',
                    title: f.dataset.titulo,
                    text: f.dataset.texto,
                    showCancelButton: true,
                    confirmButtonText: f.dataset.boton,
                    cancelButtonText: 'Volver',
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

        // ---------- Editar hora ----------
        const modalHora = document.getElementById('modalHora');
        const formHora = document.getElementById('formHora');
        const editarId = document.getElementById('editarId');
        const infoCita = document.getElementById('infoCita');
        const notaDuracion = document.getElementById('notaDuracion');
        const fecha = document.getElementById('fecha');
        const hora = document.getElementById('hora');
        const btnGuardarHora = document.getElementById('btnGuardarHora');

        const CITAS = {};
        document.querySelectorAll('.js-editar').forEach(function (b) {
            const d = JSON.parse(b.dataset.cita);
            CITAS[d.id] = d;
            b.addEventListener('click', function () { abrirEdicion(d.id, true, b); });
        });

        function hoyISO() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function abrirEdicion(id, conDatos, origen) {
            const d = CITAS[id];
            if (!d) return;

            formHora.action = URL_BASE + id + '/hora';
            editarId.value = id;
            infoCita.textContent = d.cliente + ' · ' + d.corte;
            notaDuracion.textContent = 'La duración de la cita (' + d.minutos + ' min) se mantiene.';
            fecha.min = hoyISO();

            if (conDatos) {
                fecha.value = d.fecha;
                hora.value = d.hora;
                const err = modalHora.querySelector('.errores');
                if (err) err.remove();
            }
            btnGuardarHora.disabled = false;
            abrir(modalHora, origen);
        }

        formHora.addEventListener('submit', function () { btnGuardarHora.disabled = true; });

        // ---------- Historial ----------
        const modalHist = document.getElementById('modalHistorial');
        const cuerpoHist = modalHist.querySelector('.modal-cuerpo');
        const chips = Array.from(modalHist.querySelectorAll('.chip'));
        const items = Array.from(modalHist.querySelectorAll('.hist-item'));
        const histVacio = document.getElementById('histVacio');

        function filtrar(estado) {
            chips.forEach(function (c) {
                const on = c.dataset.filtro === estado;
                c.classList.toggle('activo', on);
                c.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            let visibles = 0;
            items.forEach(function (i) {
                const mostrar = i.dataset.estado === estado;
                i.hidden = !mostrar;
                if (mostrar) visibles++;
            });
            histVacio.hidden = visibles > 0;
            cuerpoHist.scrollTop = 0;
        }

        chips.forEach(function (c) { c.addEventListener('click', function () { filtrar(c.dataset.filtro); }); });
        document.getElementById('btnHistorial').addEventListener('click', function (e) {
            filtrar('completada');
            abrir(modalHist, e.currentTarget);
        });
        filtrar('completada');

        // Al volver con el botón "atrás" del celular los botones podrían quedar bloqueados
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            btnGuardarHora.disabled = false;
            document.querySelectorAll('.js-accion button[type="submit"]').forEach(function (b) { b.disabled = false; });
        });

        // ---------- Errores ----------

        // ---------- Calendario ----------
        (function () {
            const URL_CAL = @json(route('citas.calendario'));
            const URL_DIA = @json(route('citas.dia'));
            const MESES_ADELANTE = 2;
            const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const modalCal = document.getElementById('modalCalendario');
            const cuerpoCal = modalCal.querySelector('.modal-cuerpo');
            const tituloMes = document.getElementById('calTitulo');
            const btnAnt = document.getElementById('calAnt');
            const btnSig = document.getElementById('calSig');
            const gridCal = document.getElementById('calGrid');
            const agenda = document.getElementById('calAgenda');

            let mesBase = '';
            let mesActual = '';
            let diaSel = null;
            let cacheMes = {};
            let cacheDia = {};
            let tokenMes = 0;
            let tokenDia = 0;

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function moverMes(mes, delta) {
                const p = mes.split('-');
                const d = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1 + delta, 1);
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            }

            async function pedirJson(url) {
                let r;
                try {
                    r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                } catch (e) {
                    throw new Error('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
                }
                if (r.redirected || r.status === 401 || r.status === 419) {
                    throw new Error('Tu sesión terminó. Recarga la página para entrar de nuevo.');
                }
                let d = null;
                try { d = await r.json(); } catch (e) { d = null; }
                if (!r.ok || !d || !d.ok) {
                    throw new Error((d && d.mensaje) || 'Algo salió mal. Inténtalo de nuevo en unos segundos.');
                }
                return d;
            }

            function errorHtml(texto, accion) {
                return '<div class="cal-error"><p>' + esc(texto) + '</p>' +
                    '<button type="button" class="btn btn-claro btn-sm" data-cal="' + accion + '">Reintentar</button></div>';
            }

            // ----- Mes -----
            function pintarMes(d) {
                tituloMes.textContent = d.titulo;
                const fechas = Object.keys(d.dias);
                const hoy = mesActual === mesBase
                    ? fechas.find(function (f) { return !d.dias[f].pasado; })
                    : null;

                let html = '';
                for (let i = 0; i < d.primer_dia_semana; i++) html += '<span></span>';

                fechas.forEach(function (f) {
                    const x = d.dias[f];
                    const n = parseInt(f.slice(8), 10);
                    const activo = !x.pasado && (x.trabaja || x.citas > 0);
                    const etiqueta = n + ' de ' + d.titulo.toLowerCase() + ', ' +
                        (x.citas === 0 ? 'sin citas' : (x.citas === 1 ? '1 cita' : x.citas + ' citas')) +
                        (x.trabaja ? '' : ', no trabajas');

                    html += '<button type="button" class="cal-dia' + (f === hoy ? ' hoy' : '') + (f === diaSel ? ' sel' : '') + '"' +
                        ' data-cal="dia" data-fecha="' + f + '"' + (activo ? '' : ' disabled') +
                        ' aria-label="' + esc(etiqueta) + '" aria-pressed="' + (f === diaSel ? 'true' : 'false') + '">' +
                        '<b>' + n + '</b><span class="cal-cuenta' + (x.citas ? '' : ' cero') + '">' + x.citas + '</span></button>';
                });

                gridCal.innerHTML = html;
            }

            async function mostrarMes(mes) {
                const t = ++tokenMes;
                mesActual = mes;
                btnAnt.disabled = mes <= mesBase;
                btnSig.disabled = mes >= moverMes(mesBase, MESES_ADELANTE);
                gridCal.innerHTML = '<p class="nota" style="grid-column:1/-1">Cargando…</p>';

                try {
                    const d = cacheMes[mes] || (cacheMes[mes] = await pedirJson(URL_CAL + '?mes=' + encodeURIComponent(mes)));
                    if (t !== tokenMes) return;
                    pintarMes(d);
                } catch (e) {
                    if (t !== tokenMes) return;
                    delete cacheMes[mes];
                    gridCal.innerHTML = errorHtml(e.message, 'mes-reintentar');
                }
            }

            // ----- Día -----
            function citaHtml(c) {
                return '<div class="agenda-cita"><strong>' + esc(c.cliente) + '</strong>' +
                    '<span>' + esc(c.corte) + ' · ' + esc(c.ini) + ' a ' + esc(c.fin) + '</span>' +
                    (c.wa ? '<a class="agenda-wa" href="https://wa.me/' + esc(c.wa) + '" target="_blank" rel="noopener">' + esc(c.telefono) + '</a>' : '') +
                    '</div>';
            }

            function filaHtml(f) {
                let estado;

                if (f.tipo === 'cita') {
                    estado = f.citas.map(citaHtml).join('');
                } else if (f.tipo === 'continua') {
                    estado = f.citas.map(function (c) {
                        return '<span>Sigue con <strong>' + esc(c.cliente) + '</strong> hasta las ' + esc(c.fin) + '</span>';
                    }).join('');
                } else if (f.tipo === 'bloqueo') {
                    estado = '<span>Bloqueada</span>';
                } else if (f.tipo === 'pasada') {
                    estado = '<span>Ya pasó</span>';
                } else {
                    estado = '<strong class="libre-txt">Libre</strong>';
                }

                                return '<div class="fila-hora ' + esc(f.tipo) + '"><div class="hh">' + esc(f.hora) + '<small>hasta ' + esc(f.hasta) + '</small></div>' +
                    '<div class="estado">' + estado + '</div></div>';
            }

            function pintarDia(d) {
                let html = '<div class="agenda-cab"><h3>' + esc(d.titulo) + '</h3>';

                if (d.trabaja) {
                    html += '<p>Horario: ' + esc(d.jornada) + (d.pausa ? ' · Almuerzo: ' + esc(d.pausa) : '') + '</p>';
                    html += '<p class="agenda-resumen">' +
                        (d.total_citas === 0 ? 'Sin citas' : (d.total_citas === 1 ? '1 cita' : d.total_citas + ' citas')) + ' · ' +
                        (d.horas_libres === 1 ? '1 hora libre' : d.horas_libres + ' horas libres') + '</p>';
                } else {
                    html += '<p>No trabajas este día.</p>';
                }

                html += '</div>';

                if (d.filas.length) {
                    html += '<div class="agenda-lista">' + d.filas.map(filaHtml).join('') + '</div>';
                }

                if (d.otras.length) {
                    html += '<p class="agenda-sub">Fuera de tu horario</p><div class="agenda-lista">' +
                        d.otras.map(function (c) {
                            return '<div class="fila-hora cita"><div class="hh">' + esc(c.ini) + '</div><div class="estado">' + citaHtml(c) + '</div></div>';
                        }).join('') + '</div>';
                }

                agenda.innerHTML = html;
            }

            function marcarSeleccion() {
                gridCal.querySelectorAll('.cal-dia').forEach(function (b) {
                    const on = b.dataset.fecha === diaSel;
                    b.classList.toggle('sel', on);
                    b.setAttribute('aria-pressed', on ? 'true' : 'false');
                });
            }

            async function mostrarDia(f, desplazar) {
                const t = ++tokenDia;
                diaSel = f;
                marcarSeleccion();
                agenda.innerHTML = '<p class="nota">Cargando…</p>';

                try {
                    const d = cacheDia[f] || (cacheDia[f] = await pedirJson(URL_DIA + '?fecha=' + encodeURIComponent(f)));
                    if (t !== tokenDia) return;
                    pintarDia(d);
                    if (desplazar) agenda.scrollIntoView({ behavior: reducir ? 'auto' : 'smooth', block: 'start' });
                } catch (e) {
                    if (t !== tokenDia) return;
                    delete cacheDia[f];
                    agenda.innerHTML = errorHtml(e.message, 'dia-reintentar');
                }
            }

            // ----- Eventos -----
            modalCal.addEventListener('click', function (e) {
                const el = e.target.closest('[data-cal]');
                if (!el || el.disabled) return;

                const accion = el.dataset.cal;
                if (accion === 'dia') mostrarDia(el.dataset.fecha, true);
                else if (accion === 'mes-reintentar') mostrarMes(mesActual);
                else if (accion === 'dia-reintentar' && diaSel) mostrarDia(diaSel, false);
            });

            btnAnt.addEventListener('click', function () { mostrarMes(moverMes(mesActual, -1)); });
            btnSig.addEventListener('click', function () { mostrarMes(moverMes(mesActual, 1)); });

            document.getElementById('btnCalendario').addEventListener('click', function (e) {
                const hoy = hoyISO();
                mesBase = hoy.slice(0, 7);
                cacheMes = {};
                cacheDia = {};
                abrir(modalCal, e.currentTarget);
                cuerpoCal.scrollTop = 0;
                mostrarMes(mesBase);
                mostrarDia(hoy, false);
            });
        })();

        @if ($errors->has('accion'))
            Swal.fire({
                toast: true,
                position: window.matchMedia('(max-width: 639px)').matches ? 'top' : 'top-end',
                icon: 'error',
                title: @json($errors->first('accion')),
                showConfirmButton: false,
                timer: 6000,
                timerProgressBar: true,
            });
        @endif

        @if ($errors->hasAny(['fecha', 'hora']))
            (function () {
                const id = editarId.value;
                if (id && CITAS[id]) abrirEdicion(id, false);
            })();
        @endif
    })();
</script>
@endpush