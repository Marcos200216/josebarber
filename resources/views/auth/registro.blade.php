<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Registro de barberos · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --gris-1: #E8E8E8;
            --gris-2: #B8B8B8;
            --gris-3: #8C8C8C;
            --gris-4: #525252;
            --negro: #000000;
            --blanco: #FFFFFF;
            --error: #B42318;
            --transicion: 450ms cubic-bezier(.22, .61, .36, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }
        [hidden] { display: none !important; }

        html { -webkit-text-size-adjust: 100%; background: var(--gris-1); }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            padding: 16px 14px 28px;
            font-family: 'Manrope', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: var(--negro);
            background: var(--gris-1);
            overflow-x: hidden;
        }

        /* margin:auto centra en pantallas altas y deja el contenido arriba (con scroll) en las bajas */
        .pagina {
            width: 100%;
            max-width: 440px;
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        h1 {
            font-family: 'Barlow Condensed', 'Arial Narrow', sans-serif;
            font-weight: 700;
            font-size: clamp(1.9rem, 8.5vw, 2.4rem);
            line-height: 1;
            letter-spacing: .01em;
            text-transform: uppercase;
            margin: 0 0 8px;
        }

        p { margin: 0; }
        a { color: inherit; }
        :focus-visible { outline: 2px solid var(--gris-4); outline-offset: 2px; }
        [data-lucide], .lucide { width: 20px; height: 20px; stroke-width: 1.75; flex-shrink: 0; }

        .marca {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            max-width: 100%;
            text-decoration: none;
            animation: entra 600ms var(--transicion) both;
        }

        .marca img { height: 44px; width: auto; display: block; flex-shrink: 0; }

        .marca-nombre {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tarjeta {
            width: 100%;
            padding: 22px 18px;
            background: var(--blanco);
            border-radius: 18px;
            box-shadow: 0 18px 44px rgba(0, 0, 0, .10);
            animation: entra 600ms var(--transicion) 80ms both;
        }

        @media (min-width: 480px) {
            body { padding: 28px 16px 36px; }
            .pagina { gap: 18px; }
            .marca img { height: 54px; }
            .marca-nombre { font-size: 1.6rem; }
            .tarjeta { padding: 34px 32px; border-radius: 20px; }
        }

        .tarjeta .apoyo { color: var(--gris-4); font-size: .95rem; margin-bottom: 18px; }

        @keyframes entra {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: none; }
        }

        .campo { margin-bottom: 14px; }
        .campo > label { display: block; margin-bottom: 5px; font-weight: 700; font-size: .9rem; }

        .campo input[type="text"],
        .campo input[type="email"],
        .campo input[type="password"] {
            width: 100%;
            padding: 13px 14px;
            font: inherit;
            font-size: 16px;
            color: var(--negro);
            background: var(--blanco);
            border: 1px solid var(--gris-3);
            border-radius: 10px;
            transition: border-color 400ms ease, box-shadow 400ms ease;
        }

        .campo input::placeholder { color: var(--gris-3); }
        .campo input:focus { outline: none; border-color: var(--negro); box-shadow: 0 0 0 3px rgba(0, 0, 0, .12); }
        .campo.con-error input { border-color: var(--error); }
        .campo.con-error input:focus { box-shadow: 0 0 0 3px rgba(180, 35, 24, .18); }
        .campo-ayuda { margin-top: 5px; font-size: .83rem; color: var(--gris-3); }

        /* Quita el fondo azul del autocompletado del navegador */
        input:-webkit-autofill,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
            -webkit-text-fill-color: #000000;
            transition: background-color 9999s ease-in-out 0s;
        }

        .con-ojo { position: relative; }
        .con-ojo input { padding-right: 52px !important; }

        .ojo {
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
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

        .ojo:hover { background: var(--gris-1); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 4px;
            padding: 14px 24px;
            border: 1px solid var(--negro);
            border-radius: 10px;
            background: var(--negro);
            color: var(--gris-1);
            font: inherit;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background 400ms ease, border-color 400ms ease, opacity 400ms ease;
        }

        .btn:hover { background: var(--gris-4); border-color: var(--gris-4); }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .btn:disabled:hover { background: var(--negro); border-color: var(--negro); }

        .cambio {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--gris-1);
            text-align: center;
            font-size: .93rem;
            color: var(--gris-4);
        }

        .cambio a { font-weight: 800; color: var(--negro); text-underline-offset: 4px; }

        .volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 4px;
            font-weight: 700;
            font-size: .93rem;
            color: var(--gris-4);
            text-decoration: none;
            animation: entra 600ms var(--transicion) 160ms both;
            transition: color 400ms ease;
        }

        .volver:hover { color: var(--negro); }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 1ms !important; transition-duration: 1ms !important; }
        }
        
        /* ---------- Fondo negro para que el logo se vea ---------- */
        html, body { background: var(--negro); }

        .marca { color: var(--gris-1); }
        .marca img { height: 64px; }
        .marca-nombre { color: var(--gris-1); }

        .tarjeta { box-shadow: 0 18px 44px rgba(0, 0, 0, .5); }

        .volver { color: var(--gris-2); }
        .volver:hover { color: var(--blanco); }

        .marca:focus-visible,
        .volver:focus-visible { outline-color: var(--gris-2); }

        @media (min-width: 480px) {
            .marca img { height: 80px; }
        }
    </style>
</head>
<body>
    <div class="pagina">

        <a href="{{ route('inicio') }}" class="marca" aria-label="{{ config('app.name') }}, inicio">
            <img src="{{ asset('images/logo.png') }}" alt="" onerror="this.style.display='none'">
            <span class="marca-nombre">{{ config('app.name') }}</span>
        </a>

        <main class="tarjeta">
            <h1>Registro de barberos</h1>
            <p class="apoyo">Crea tu cuenta. Después, el administrador revisará tu perfil para que aparezcas ante los clientes.</p>

            <form method="POST" action="{{ url('/barbero/registro') }}" id="formRegistro" novalidate>
                @csrf

                <div class="campo{{ $errors->has('name') ? ' con-error' : '' }}" id="cName">
                    <label for="name">Nombre</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="100"
                           autocomplete="name" placeholder="Por ejemplo: José Pablo">
                </div>

                <div class="campo{{ $errors->has('email') ? ' con-error' : '' }}" id="cEmail">
                    <label for="email">Correo</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="150"
                           autocomplete="email" inputmode="email" placeholder="nombre@correo.com">
                </div>

                <div class="campo{{ $errors->has('password') ? ' con-error' : '' }}" id="cPassword">
                    <label for="password">Contraseña</label>
                    <div class="con-ojo">
                        <input id="password" type="password" name="password" autocomplete="new-password">
                        <button type="button" class="ojo" data-ojo="password" aria-label="Mostrar contraseña">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <p class="campo-ayuda">Mínimo 8 caracteres.</p>
                </div>

                <div class="campo{{ $errors->has('password') && str_contains((string) $errors->first('password'), 'coinciden') ? ' con-error' : '' }}" id="cConfirmar">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <div class="con-ojo">
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                        <button type="button" class="ojo" data-ojo="password_confirmation" aria-label="Mostrar contraseña">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn" id="btnEnviar">
                    <i data-lucide="user-plus"></i> <span>Crear cuenta</span>
                </button>
            </form>

            <p class="cambio">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
        </main>

        <a href="{{ route('inicio') }}" class="volver"><i data-lucide="arrow-left"></i> Volver al inicio</a>
    </div>

    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            if (window.lucide) lucide.createIcons();

            const ERRORES_SERVIDOR = {{ \Illuminate\Support\Js::from($errors->all()) }};

            // Toast de error (mismo estilo que el resto del sitio): ~7 s, barra de progreso, se pausa con el mouse
            function avisarError(mensajes) {
                const lista = Array.isArray(mensajes) ? mensajes : [mensajes];
                if (!lista.length) return;

                if (!window.Swal) { alert(lista.join('\n')); return; }

                const esc = function (s) {
                    return String(s).replace(/[&<>"']/g, function (c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                    });
                };

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    width: 'min(92vw, 380px)',
                    html: lista.length === 1
                        ? esc(lista[0])
                        : '<ul style="margin:0;padding-left:18px;text-align:left">' +
                          lista.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>',
                    showConfirmButton: false,
                    showCloseButton: true,
                    timer: 7000,
                    timerProgressBar: true,
                    didOpen: function (t) {
                        t.addEventListener('mouseenter', Swal.stopTimer);
                        t.addEventListener('mouseleave', Swal.resumeTimer);
                    },
                });
            }

            // Mostrar / ocultar contraseña
            document.querySelectorAll('[data-ojo]').forEach(function (b) {
                b.addEventListener('click', function () {
                    const inp = document.getElementById(b.dataset.ojo);
                    const ver = inp.type === 'password';
                    inp.type = ver ? 'text' : 'password';
                    b.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
                    b.innerHTML = '<i data-lucide="' + (ver ? 'eye-off' : 'eye') + '"></i>';
                    if (window.lucide) lucide.createIcons();
                });
            });

            const form = document.getElementById('formRegistro');
            const btn = document.getElementById('btnEnviar');
            const inName = document.getElementById('name');
            const inEmail = document.getElementById('email');
            const inPass = document.getElementById('password');
            const inConf = document.getElementById('password_confirmation');

            function marcar(id, mal) {
                document.getElementById(id).classList.toggle('con-error', mal);
            }

            [['name', 'cName'], ['email', 'cEmail'], ['password', 'cPassword'], ['password_confirmation', 'cConfirmar']].forEach(function (par) {
                document.getElementById(par[0]).addEventListener('input', function () { marcar(par[1], false); });
            });

            form.addEventListener('submit', function (e) {
                const msgs = [];
                let primero = null;
                const nombre = inName.value.trim();
                const correo = inEmail.value.trim();

                ['cName', 'cEmail', 'cPassword', 'cConfirmar'].forEach(function (id) { marcar(id, false); });

                if (!nombre) {
                    msgs.push('Escribe tu nombre.'); marcar('cName', true); primero = primero || inName;
                }

                if (!correo) {
                    msgs.push('Escribe tu correo.'); marcar('cEmail', true); primero = primero || inEmail;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
                    msgs.push('Escribe un correo válido, por ejemplo nombre@correo.com.'); marcar('cEmail', true); primero = primero || inEmail;
                }

                if (!inPass.value) {
                    msgs.push('Escribe una contraseña.'); marcar('cPassword', true); primero = primero || inPass;
                } else if (inPass.value.length < 8) {
                    msgs.push('La contraseña debe tener al menos 8 caracteres.'); marcar('cPassword', true); primero = primero || inPass;
                } else if (inConf.value !== inPass.value) {
                    msgs.push('Las contraseñas no coinciden.'); marcar('cConfirmar', true); primero = primero || inConf;
                }

                if (msgs.length) {
                    e.preventDefault();
                    avisarError(msgs);
                    primero.focus();
                    return;
                }

                btn.disabled = true;
                btn.querySelector('span').textContent = 'Creando cuenta…';
            });

            // Si vuelve con el botón "Atrás" del navegador, el botón no se queda bloqueado
            window.addEventListener('pageshow', function () {
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Crear cuenta';
            });

            // Errores que manda el servidor (correo repetido, demasiados intentos…)
            if (ERRORES_SERVIDOR.length) avisarError(ERRORES_SERVIDOR);

            const conError = document.querySelector('.campo.con-error input');
            (conError || inName).focus();
        })();
    </script>
</body>
</html>