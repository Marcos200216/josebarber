<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --gris-1: #E8E8E8;
            --gris-2: #B8B8B8;
            --gris-3: #8C8C8C;
            --gris-4: #525252;
            --negro: #000000;
            --blanco: #FFFFFF;
            --sidebar-ancho: 260px;
            --topbar-alto: 60px;
            --transicion: 450ms cubic-bezier(.22, .61, .36, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            font-family: 'Manrope', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.55;
            color: var(--negro);
            background: var(--gris-1);
        }

        h1, h2, h3, h4 {
            font-family: 'Barlow Condensed', 'Arial Narrow', sans-serif;
            font-weight: 600;
            letter-spacing: .01em;
            line-height: 1.1;
            margin: 0 0 .5rem;
            text-decoration: none;
        }

        h1 { font-size: clamp(2rem, 5vw, 2.75rem); }
        h2 { font-size: 1.75rem; }

        a { color: inherit; }

        :focus-visible { outline: 2px solid var(--gris-4); outline-offset: 2px; }

        [data-lucide], .lucide { width: 20px; height: 20px; stroke-width: 1.75; flex-shrink: 0; }

        /* ---------- Estructura ---------- */
        .topbar {
            position: fixed;
            inset: 0 0 auto 0;
            height: var(--topbar-alto);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 16px;
            background: var(--negro);
            color: var(--gris-1);
            z-index: 30;
        }

        .topbar-brand {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .menu-toggle {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--gris-4);
            border-radius: 8px;
            color: var(--gris-1);
            cursor: pointer;
            transition: background var(--transicion);
        }

        .menu-toggle:hover { background: var(--gris-4); }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-ancho);
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            background: var(--negro);
            color: var(--gris-1);
            z-index: 50;
            transform: translateX(-100%);
            transition: transform var(--transicion);
            overflow-y: auto;
        }

        .sidebar.abierto { transform: translateX(0); }

        .sidebar-brand {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.75rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: 0 12px 24px;
            border-bottom: 1px solid var(--gris-4);
            margin-bottom: 20px;
        }

        .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 4px; }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--gris-2);
            font-weight: 500;
            transition: background var(--transicion), color var(--transicion);
        }

        .sidebar-nav a:hover { background: #1a1a1a; color: var(--gris-1); }

        .sidebar-nav a.activo { background: var(--gris-1); color: var(--negro); }

        .sidebar-titulo {
            margin: 22px 12px 8px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--gris-3);
        }

        .sidebar-salir {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            background: transparent;
            border: 1px solid var(--gris-4);
            border-radius: 8px;
            color: var(--gris-2);
            font: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: background var(--transicion), color var(--transicion);
        }

        .sidebar-salir:hover { background: var(--gris-1); color: var(--negro); }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .6);
            opacity: 0;
            visibility: hidden;
            transition: opacity var(--transicion), visibility var(--transicion);
            z-index: 40;
        }

        .overlay.visible { opacity: 1; visibility: visible; }

        .contenido {
            padding: calc(var(--topbar-alto) + 24px) 16px 40px;
            max-width: 1100px;
            margin: 0 auto;
        }

                .sidebar-nav .contador {
            margin-left: auto;
            min-width: 22px;
            padding: 1px 7px;
            border-radius: 999px;
            background: var(--gris-1);
            color: var(--negro);
            font-size: .75rem;
            font-weight: 700;
            text-align: center;
        }

        .sidebar-nav a.activo .contador { background: var(--negro); color: var(--gris-1); }

        /* ---------- Escritorio ---------- */
        @media (min-width: 992px) {
            .topbar, .overlay { display: none; }
            .sidebar { transform: none; }
            .contenido {
                margin: 0 0 0 var(--sidebar-ancho);
                max-width: none;
                padding: 40px 48px 56px;
            }
            .contenido > * { max-width: 1000px; }
        }

        /* ---------- Aparición al hacer scroll ---------- */
        [data-reveal] {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 600ms ease, transform 600ms ease;
        }

        [data-reveal].visible { opacity: 1; transform: none; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 1ms !important; animation-duration: 1ms !important; }
            [data-reveal] { opacity: 1; transform: none; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <header class="topbar">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebar">
            <i data-lucide="menu"></i>
        </button>
        <span class="topbar-brand">{{ config('app.name') }}</span>
    </header>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">{{ config('app.name') }}</div>

        <nav class="sidebar-nav">
            <a href="{{ route('panel') }}" class="{{ request()->routeIs('panel') ? 'activo' : '' }}">
                <i data-lucide="layout-dashboard"></i> Inicio
            </a>
                        <a href="{{ route('perfil') }}" class="{{ request()->routeIs('perfil') ? 'activo' : '' }}">
                <i data-lucide="user-round"></i> Mi perfil
            </a>
                        <a href="{{ route('mis-cortes') }}" class="{{ request()->routeIs('mis-cortes*') ? 'activo' : '' }}">
                <i data-lucide="scissors"></i> Mis cortes
            </a>
                        <a href="{{ route('horario') }}" class="{{ request()->routeIs('horario*') ? 'activo' : '' }}">
                <i data-lucide="calendar-clock"></i> Mi horario
            </a>
                       <a href="{{ route('bloqueos') }}" class="{{ request()->routeIs('bloqueos*') ? 'activo' : '' }}">
                <i data-lucide="calendar-off"></i> Bloqueos
            </a>
                      <a href="{{ route('galeria') }}" class="{{ request()->routeIs('galeria*') ? 'activo' : '' }}">
                <i data-lucide="images"></i> Mi galería
            </a>
                       @php $citasPendientes = \App\Models\Cita::where('user_id', auth()->id())->where('estado', 'pendiente')->count(); @endphp
            <a href="{{ route('citas') }}" class="{{ request()->routeIs('citas*') ? 'activo' : '' }}">
                <i data-lucide="calendar-check"></i> Citas
                @if ($citasPendientes > 0)
                    <span class="contador">{{ $citasPendientes }}</span>
                @endif
            </a>

            @if (auth()->user()->isAdmin())
                <p class="sidebar-titulo">Administración</p>
                <a href="{{ route('admin.cortes.index') }}" class="{{ request()->routeIs('admin.cortes.*') ? 'activo' : '' }}">
                    <i data-lucide="scissors"></i> Cortes
                </a>
                               @php $pendientes = \App\Models\User::where('status', 'pendiente')->count(); @endphp
                <a href="{{ route('admin.solicitudes.index') }}" class="{{ request()->routeIs('admin.solicitudes.*') ? 'activo' : '' }}">
                    <i data-lucide="user-check"></i> Solicitudes
                    @if ($pendientes > 0)
                        <span class="contador">{{ $pendientes }}</span>
                    @endif
                </a>
            @endif
        </nav>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-salir">
                <i data-lucide="log-out"></i> Cerrar sesión
            </button>
        </form>
    </aside>

    <div class="overlay" id="overlay"></div>

    <main class="contenido">
        @yield('content')
    </main>

    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            // Iconos
            if (window.lucide) lucide.createIcons();

            // Menú lateral en móvil
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const toggle = document.getElementById('menuToggle');

            function abrir() {
                sidebar.classList.add('abierto');
                overlay.classList.add('visible');
                toggle.setAttribute('aria-expanded', 'true');
            }

            function cerrar() {
                sidebar.classList.remove('abierto');
                overlay.classList.remove('visible');
                toggle.setAttribute('aria-expanded', 'false');
            }

            toggle.addEventListener('click', function () {
                sidebar.classList.contains('abierto') ? cerrar() : abrir();
            });
            overlay.addEventListener('click', cerrar);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') cerrar();
            });

            // Aparición al hacer scroll
            const items = document.querySelectorAll('[data-reveal]');
            if ('IntersectionObserver' in window) {
                const obs = new IntersectionObserver(function (entradas) {
                    entradas.forEach(function (en) {
                        if (en.isIntersecting) {
                            en.target.classList.add('visible');
                            obs.unobserve(en.target);
                        }
                    });
                }, { threshold: 0.1 });
                items.forEach(function (el) { obs.observe(el); });
            } else {
                items.forEach(function (el) { el.classList.add('visible'); });
            }

            // Mensajes de confirmación enviados por el servidor
            @if (session('ok'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('ok')),
                    showConfirmButton: false,
                    timer: 2800,
                    timerProgressBar: true,
                });
            @endif
        })();
    </script>
    @stack('scripts')
</body>
</html>