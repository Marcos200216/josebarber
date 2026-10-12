@extends('layout.app')

@section('title', 'Mi horario · ' . config('app.name'))

@push('styles')
<style>
    .encabezado { margin-bottom: 20px; }
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

    .errores {
        margin-bottom: 16px;
        padding: 12px 16px;
        border: 1px solid var(--gris-4);
        border-left-width: 4px;
        border-radius: 8px;
        background: var(--blanco);
    }
    .errores ul { margin: 0; padding-left: 18px; }

    .aviso-inicial {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
        padding: 12px 16px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 8px;
        font-size: .92rem;
        color: var(--gris-4);
    }
    .aviso-inicial [data-lucide] { margin-top: 2px; }

    .herramientas { margin-bottom: 14px; }

    /* ---------- Lista de días ---------- */
    .dias { display: grid; gap: 10px; padding-bottom: 96px; }

    .dia {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
        padding: 16px;
        background: var(--blanco);
        border: 1px solid var(--gris-2);
        border-radius: 12px;
        transition: background 450ms ease, border-color 450ms ease;
    }
    .dia.libre { background: var(--gris-1); border-color: transparent; }
    .dia.con-error { border-color: var(--negro); border-width: 2px; }

    .dia-cab { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .dia-nombre { font-family: 'Barlow Condensed', sans-serif; font-size: 1.5rem; font-weight: 600; line-height: 1; }

    /* Interruptor Trabajo / Libre */
    .interruptor { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
    .interruptor input { position: absolute; opacity: 0; pointer-events: none; }
    .pista {
        position: relative;
        width: 48px;
        height: 28px;
        border-radius: 999px;
        background: var(--gris-2);
        transition: background 400ms ease;
    }
    .pista::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--blanco);
        transition: transform 400ms cubic-bezier(.22, .61, .36, 1);
    }
    .interruptor input:checked + .pista { background: var(--negro); }
    .interruptor input:checked + .pista::after { transform: translateX(20px); }
    .interruptor input:focus-visible + .pista { outline: 2px solid var(--gris-4); outline-offset: 2px; }
    .interruptor-texto { min-width: 54px; font-weight: 600; font-size: .92rem; }

    .dia-cuerpo { display: grid; gap: 12px; transition: opacity 450ms ease; }
    .dia.libre .dia-cuerpo { opacity: .35; pointer-events: none; }

    .horas, .pausa-horas { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    .campo label.etq { display: block; margin-bottom: 4px; font-size: .8rem; font-weight: 600; color: var(--gris-4); }
    .campo input[type="time"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--gris-2);
        border-radius: 8px;
        background: var(--blanco);
        font: inherit;
        color: var(--negro);
    }
    .campo input[type="time"]:focus { outline: 2px solid var(--gris-4); outline-offset: 1px; border-color: var(--gris-4); }
    .campo input[type="time"]:disabled { background: var(--gris-1); }

    /* Pausa */
    .pausa { display: grid; gap: 10px; padding-top: 12px; border-top: 1px dashed var(--gris-2); }
    .check-linea { display: inline-flex; align-items: center; gap: 10px; font-weight: 600; font-size: .92rem; cursor: pointer; width: fit-content; }
    .check-linea input { width: 20px; height: 20px; accent-color: var(--negro); }
    .pausa-horas[hidden] { display: none; }

    @media (min-width: 640px) {
        .dia { grid-template-columns: 220px 1fr; align-items: start; }
        .dia-cab { padding-top: 4px; }
        .dia-cuerpo { max-width: 380px; }
    }

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
    .barra-guardar .resumen { font-weight: 600; }
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
        <h1>Mi horario</h1>
        <p>Indica los días que trabajas, tu entrada y salida, y tu pausa diaria (por ejemplo, el almuerzo). Los clientes solo podrán reservar dentro de tu horario y fuera de la pausa.</p>
    </div>


    @unless ($hayGuardados)
        <div class="aviso-inicial" data-reveal>
            <i data-lucide="info"></i>
            <span>Todavía no has guardado tu horario. Te sugerimos lunes a sábado de 9:00 a 18:00, con pausa de 12:00 a 13:00: ajústalo y presiona <strong>Guardar</strong>.</span>
        </div>
    @endunless

    <form method="POST" action="{{ route('horario.update') }}" id="formHorario" novalidate>
        @csrf
        @method('PUT')

        <div class="herramientas" data-reveal>
            <button type="button" class="btn btn-claro btn-sm" id="copiarLunes">
                <i data-lucide="copy"></i> Copiar el lunes a los demás días de trabajo
            </button>
        </div>

        <div class="dias">
            @foreach ($dias as $num => $d)
                @php
                    $hayOld = old('dias') !== null;
                    $trabaja = $hayOld ? ! empty(old("dias.$num.trabaja")) : $d['trabaja'];
                    $inicio = old("dias.$num.inicio", $d['inicio']);
                    $fin = old("dias.$num.fin", $d['fin']);
                    $pausa = $hayOld ? ! empty(old("dias.$num.pausa")) : $d['pausa'];
                    $pausaInicio = old("dias.$num.pausa_inicio", $d['pausa_inicio']);
                    $pausaFin = old("dias.$num.pausa_fin", $d['pausa_fin']);
                @endphp
                <div class="dia {{ $trabaja ? '' : 'libre' }} {{ $errors->has("dias.$num") ? 'con-error' : '' }}" data-dia="{{ $num }}" data-reveal>
                    <div class="dia-cab">
                        <span class="dia-nombre">{{ $d['nombre'] }}</span>
                        <label class="interruptor">
                            <input type="checkbox" name="dias[{{ $num }}][trabaja]" value="1" data-campo="trabaja" {{ $trabaja ? 'checked' : '' }}>
                            <span class="pista"></span>
                            <span class="interruptor-texto">{{ $trabaja ? 'Trabajo' : 'Libre' }}</span>
                        </label>
                    </div>

                    <div class="dia-cuerpo">
                        <div class="horas">
                            <div class="campo">
                                <label class="etq" for="inicio{{ $num }}">Entrada</label>
                                <input type="time" id="inicio{{ $num }}" name="dias[{{ $num }}][inicio]" value="{{ $inicio }}" step="900" data-campo="inicio" {{ $trabaja ? '' : 'disabled' }}>
                            </div>
                            <div class="campo">
                                <label class="etq" for="fin{{ $num }}">Salida</label>
                                <input type="time" id="fin{{ $num }}" name="dias[{{ $num }}][fin]" value="{{ $fin }}" step="900" data-campo="fin" {{ $trabaja ? '' : 'disabled' }}>
                            </div>
                        </div>

                        <div class="pausa">
                            <label class="check-linea">
                                <input type="checkbox" name="dias[{{ $num }}][pausa]" value="1" data-campo="pausa" {{ $pausa ? 'checked' : '' }} {{ $trabaja ? '' : 'disabled' }}>
                                Pausa (almuerzo u otra)
                            </label>
                            <div class="pausa-horas" {{ $pausa && $trabaja ? '' : 'hidden' }}>
                                <div class="campo">
                                    <label class="etq" for="pausaIni{{ $num }}">Pausa desde</label>
                                    <input type="time" id="pausaIni{{ $num }}" name="dias[{{ $num }}][pausa_inicio]" value="{{ $pausaInicio }}" step="900" data-campo="pausa_inicio" {{ $pausa && $trabaja ? '' : 'disabled' }}>
                                </div>
                                <div class="campo">
                                    <label class="etq" for="pausaFin{{ $num }}">Pausa hasta</label>
                                    <input type="time" id="pausaFin{{ $num }}" name="dias[{{ $num }}][pausa_fin]" value="{{ $pausaFin }}" step="900" data-campo="pausa_fin" {{ $pausa && $trabaja ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="barra-guardar">
            <div class="resumen">
                <span id="resumenDias">0 días de trabajo</span>
                <small id="aviso">Sin cambios por guardar</small>
            </div>
            <button type="submit" class="btn" id="btnGuardar" disabled>
                <i data-lucide="save"></i> Guardar
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('formHorario');
        const filas = Array.from(form.querySelectorAll('.dia'));
        const btnGuardar = document.getElementById('btnGuardar');
        const aviso = document.getElementById('aviso');
        const resumen = document.getElementById('resumenDias');
        const hayGuardados = @json($hayGuardados);

        function partes(fila) {
            return {
                trabaja: fila.querySelector('[data-campo="trabaja"]'),
                ini: fila.querySelector('[data-campo="inicio"]'),
                fin: fila.querySelector('[data-campo="fin"]'),
                pausa: fila.querySelector('[data-campo="pausa"]'),
                pIni: fila.querySelector('[data-campo="pausa_inicio"]'),
                pFin: fila.querySelector('[data-campo="pausa_fin"]'),
                pHoras: fila.querySelector('.pausa-horas'),
                txt: fila.querySelector('.interruptor-texto'),
            };
        }

        function instantanea() {
            return filas.map(function (f) {
                const p = partes(f);
                return [
                    p.trabaja.checked ? 1 : 0, p.ini.value, p.fin.value,
                    p.pausa.checked ? 1 : 0, p.pIni.value, p.pFin.value,
                ].join('|');
            }).join(';');
        }

        // Lo que hay en pantalla al cargar. Si nunca se guardó, el botón
        // queda activo para poder guardar la sugerencia tal cual.
        const inicial = instantanea();

        function pintarFila(fila) {
            const p = partes(fila);
            const on = p.trabaja.checked;
            const conPausa = on && p.pausa.checked;

            fila.classList.toggle('libre', !on);
            p.ini.disabled = !on;
            p.fin.disabled = !on;
            p.pausa.disabled = !on;
            p.pHoras.hidden = !conPausa;
            p.pIni.disabled = !conPausa;
            p.pFin.disabled = !conPausa;
            p.txt.textContent = on ? 'Trabajo' : 'Libre';
        }

        function actualizar() {
            const total = filas.filter(function (f) { return partes(f).trabaja.checked; }).length;
            resumen.textContent = total + (total === 1 ? ' día de trabajo' : ' días de trabajo');

            const cambios = instantanea() !== inicial || !hayGuardados;
            btnGuardar.disabled = !cambios;
            aviso.textContent = cambios ? 'Tienes cambios sin guardar' : 'Sin cambios por guardar';
        }

        filas.forEach(function (f) {
            const p = partes(f);
            [p.trabaja, p.pausa].forEach(function (c) {
                c.addEventListener('change', function () { pintarFila(f); actualizar(); });
            });
            [p.ini, p.fin, p.pIni, p.pFin].forEach(function (c) {
                c.addEventListener('input', actualizar);
            });
            pintarFila(f);
        });

        // Copiar el lunes (horas y pausa) a los demás días de trabajo
        document.getElementById('copiarLunes').addEventListener('click', function () {
            const lunes = partes(form.querySelector('[data-dia="1"]'));
            if (!lunes.trabaja.checked) {
                Swal.fire({
                    icon: 'info',
                    title: 'El lunes está libre',
                    text: 'Activa el lunes y define sus horas para poder copiarlas.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#000000',
                });
                return;
            }
            filas.forEach(function (f) {
                const p = partes(f);
                if (f.dataset.dia === '1' || !p.trabaja.checked) return;
                p.ini.value = lunes.ini.value;
                p.fin.value = lunes.fin.value;
                p.pausa.checked = lunes.pausa.checked;
                p.pIni.value = lunes.pIni.value;
                p.pFin.value = lunes.pFin.value;
                pintarFila(f);
            });
            actualizar();
        });

        // Envío
        let confirmado = false;
        form.addEventListener('submit', function (e) {
            if (confirmado) return;

            const total = filas.filter(function (f) { return partes(f).trabaja.checked; }).length;
            if (total === 0) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Guardar sin días de trabajo?',
                    text: 'Con todos los días libres, los clientes no podrán reservar contigo.',
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

                // Errores de validación: aviso temporal que desaparece solo
        @if ($errors->any())
            (function () {
                const mensajes = @json($errors->all());
                const esc = function (t) {
                    const d = document.createElement('div');
                    d.textContent = t;
                    return d.innerHTML;
                };

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Revisa tu horario',
                    html: mensajes.map(esc).join('<br>'),
                    width: '26em',
                    showConfirmButton: false,
                    timer: 7000,
                    timerProgressBar: true,
                    didOpen: function (toast) {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    },
                });

                const fila = document.querySelector('.dia.con-error');
                if (fila) fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            })();
        @endif

        
        actualizar();
    })();
</script>
@endpush