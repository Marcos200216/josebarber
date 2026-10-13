<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index, follow">
    <meta name="description"
        content="Reserva tu corte en {{ $marca }}. Elige tu corte, tu barbero y tu hora, sin crear cuenta.">
    <title>{{ $marca }} · Reserva tu corte</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --gris-1: #E8E8E8;
            --gris-2: #B8B8B8;
            --gris-3: #8C8C8C;
            --gris-4: #525252;
            --negro: #000000;
            --blanco: #FFFFFF;
            --error: #B42318;
            --header-alto: 110px;
            --logo-alto: 82px;
            --transicion: 450ms cubic-bezier(.22, .61, .36, 1);
        }

        @media (min-width: 900px) {
            :root {
                --header-alto: 132px;
                --logo-alto: 108px;
            }
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        [hidden] {
            display: none !important;
        }

        html {
            -webkit-text-size-adjust: 100%;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Manrope', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.55;
            color: var(--negro);
            background: var(--blanco);
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Barlow Condensed', 'Arial Narrow', sans-serif;
            font-weight: 600;
            letter-spacing: .01em;
            line-height: 1;
            margin: 0 0 .5rem;
            text-decoration: none;
        }

        h1 {
            font-size: clamp(3rem, 11vw, 5.6rem);
            font-weight: 700;
            text-transform: uppercase;
        }

        h2 {
            font-size: clamp(2.2rem, 6vw, 3.2rem);
            text-transform: uppercase;
        }

        a {
            color: inherit;
        }

        p {
            margin: 0;
        }

        :focus-visible {
            outline: 2px solid var(--gris-4);
            outline-offset: 2px;
        }

        [data-lucide],
        .lucide {
            width: 20px;
            height: 20px;
            stroke-width: 1.75;
            flex-shrink: 0;
        }

        .contenedor {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 20px;
        }

        section[id] {
            scroll-margin-top: calc(var(--header-alto) - 20px);
        }

        /* ---------- Botones ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 26px;
            border: 1px solid var(--negro);
            border-radius: 10px;
            background: var(--negro);
            color: var(--gris-1);
            font: inherit;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: background 400ms ease, color 400ms ease, border-color 400ms ease, transform 400ms ease, opacity 400ms ease;
        }

        .btn:hover {
            background: var(--gris-4);
            border-color: var(--gris-4);
        }

        .btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .btn:disabled:hover {
            background: var(--negro);
            border-color: var(--negro);
        }

        .btn-claro {
            background: transparent;
            color: var(--negro);
            border-color: var(--gris-3);
        }

        .btn-claro:hover {
            background: var(--gris-1);
            color: var(--negro);
            border-color: var(--negro);
        }

        .btn-blanco {
            background: var(--blanco);
            color: var(--negro);
            border-color: var(--blanco);
        }

        .btn-blanco:hover {
            background: var(--gris-1);
            color: var(--negro);
            border-color: var(--gris-1);
        }

        .btn-cancelar {
            background: transparent;
            color: var(--error);
            border-color: var(--error);
        }

        .btn-cancelar:hover {
            background: #FDECEA;
            color: var(--error);
            border-color: var(--error);
        }

        .btn-cancelar:disabled:hover {
            background: transparent;
            border-color: var(--error);
        }

        .btn-sm {
            padding: 10px 18px;
            font-size: .92rem;
        }

        /* ---------- Header ---------- */
        .header {
            position: sticky;
            top: 0;
            z-index: 60;
            background: var(--negro);
            border-bottom: 1px solid transparent;
            transition: box-shadow var(--transicion), border-color var(--transicion);
        }

        .header.compacto {
            border-color: var(--gris-4);
            box-shadow: 0 6px 22px rgba(0, 0, 0, .28);
        }

        .header-in {
            height: var(--header-alto);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            transition: height var(--transicion);
        }

        .header.compacto .header-in {
            height: calc(var(--header-alto) - 22px);
        }

        .marca {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: var(--gris-1);
            min-width: 0;
        }

        .marca img {
            height: var(--logo-alto);
            width: auto;
            display: block;
            transition: height var(--transicion);
        }

        .header.compacto .marca img {
            height: calc(var(--logo-alto) - 14px);
        }

        .marca-nombre {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            line-height: 1;
            color: var(--gris-1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (min-width: 900px) {
            .marca-nombre {
                font-size: 2rem;
            }
        }

        .nav {
            display: none;
            align-items: center;
            gap: 2px;
        }

        .nav a.enlace {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: .95rem;
            color: var(--gris-2);
            transition: background var(--transicion), color var(--transicion);
        }

        .nav a.enlace:hover {
            background: var(--gris-4);
            color: var(--blanco);
        }

        .nav .btn {
            margin-left: 12px;
        }

        .nav .btn-claro {
            color: var(--gris-1);
            border-color: var(--gris-4);
        }

        .nav .btn-claro:hover {
            background: var(--gris-4);
            color: var(--blanco);
            border-color: var(--gris-3);
        }

        .menu-btn {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--gris-4);
            border-radius: 10px;
            color: var(--gris-1);
            cursor: pointer;
            transition: background var(--transicion);
        }

        .menu-btn:hover {
            background: var(--gris-4);
        }

        .menu-btn svg {
            width: 24px;
            height: 24px;
        }

        @media (min-width: 900px) {
            .nav {
                display: flex;
            }

            .menu-btn {
                display: none;
            }
        }

        /* Menú móvil a pantalla completa */
        .menu-movil {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            flex-direction: column;
            background: var(--negro);
            color: var(--gris-1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-12px);
            transition: opacity var(--transicion), visibility var(--transicion), transform var(--transicion);
            overflow-y: auto;
        }

        .menu-movil.abierto {
            opacity: 1;
            visibility: visible;
            transform: none;
        }

        .menu-movil-cab {
            min-height: var(--header-alto);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }

        .menu-movil nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
            padding: 0 20px 40px;
        }

        .menu-movil nav a.enlace {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 8px;
            border-bottom: 1px solid var(--gris-4);
            text-decoration: none;
            color: var(--gris-1);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.1rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .menu-movil nav a.enlace svg {
            width: 26px;
            height: 26px;
        }

        .menu-movil nav .btn {
            margin-top: 26px;
            padding: 16px;
        }

        .menu-movil nav .btn-claro {
            color: var(--gris-1);
            border-color: var(--gris-4);
        }

        .menu-movil nav .btn-claro:hover {
            background: var(--gris-4);
            color: var(--blanco);
            border-color: var(--gris-3);
        }

        /* ---------- Portada ---------- */
        .hero {
            position: relative;
            padding: 40px 0 56px;
            overflow: hidden;
        }

        .hero-grid {
            display: grid;
            gap: 40px;
            align-items: center;
        }

        @media (min-width: 900px) {
            .hero {
                padding: 72px 0 88px;
            }

            .hero-grid {
                grid-template-columns: 1.15fr .85fr;
                gap: 56px;
            }
        }

        .etiqueta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            margin-bottom: 20px;
            border-radius: 999px;
            background: var(--gris-1);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-4);
        }

        .etiqueta svg {
            width: 16px;
            height: 16px;
            color: var(--negro);
        }

        .hero p.apoyo {
            max-width: 520px;
            margin-top: 18px;
            font-size: 1.15rem;
            color: var(--gris-4);
        }

        .hero-botones {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 30px;
        }

        .hero-botones .btn {
            width: 100%;
            padding: 18px 26px;
            font-size: 1.05rem;
        }

        @media (min-width: 560px) {
            .hero-botones {
                flex-direction: row;
            }

            .hero-botones .btn {
                width: auto;
            }
        }

        .pasos {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 44px;
            padding-top: 26px;
            border-top: 1px solid var(--gris-2);
            max-width: 560px;
        }

        .paso strong {
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.2rem;
            font-weight: 600;
            line-height: 1;
            color: var(--gris-2);
        }

        .paso span {
            display: block;
            margin-top: 4px;
            font-size: .88rem;
            font-weight: 700;
            color: var(--negro);
            line-height: 1.3;
        }

        /* Panel visual (solo escritorio) */
        .hero-visual {
            display: none;
            position: relative;
        }

        @media (min-width: 900px) {
            .hero-visual {
                display: block;
            }
        }

        .hero-panel {
            position: relative;
            aspect-ratio: 1 / 1.05;
            border-radius: 28px;
            background:
                repeating-linear-gradient(135deg, rgba(0, 0, 0, .035) 0 2px, transparent 2px 22px),
                var(--gris-1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-panel::before,
        .hero-panel::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 1px solid var(--gris-2);
        }

        .hero-panel::before {
            width: 76%;
            aspect-ratio: 1;
        }

        .hero-panel::after {
            width: 100%;
            aspect-ratio: 1;
            opacity: .6;
        }

        .hero-panel img {
            position: relative;
            z-index: 1;
            width: 62%;
            height: auto;
            max-height: 70%;
            object-fit: contain;
        }

        .flota {
            position: absolute;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 14px;
            background: var(--blanco);
            box-shadow: 0 14px 34px rgba(0, 0, 0, .14);
            font-size: .9rem;
            font-weight: 700;
            animation: flotar 6s ease-in-out infinite;
        }

        .flota svg {
            width: 22px;
            height: 22px;
        }

        .flota.a {
            top: 8%;
            left: -6%;
        }

        .flota.b {
            bottom: 9%;
            right: -5%;
            animation-delay: -3s;
        }

        @keyframes flotar {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* ---------- Banda "Ver mi cita" ---------- */
        .banda {
            background: var(--negro);
            color: var(--gris-1);
            padding: 36px 0;
        }

        .banda-in {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .banda-texto {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .banda-icono {
            width: 54px;
            height: 54px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--gris-4);
            border-radius: 14px;
        }

        .banda-icono svg {
            width: 26px;
            height: 26px;
        }

        .banda-texto strong {
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.7rem;
            font-weight: 600;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .banda-texto span {
            color: var(--gris-2);
            font-size: .95rem;
        }

        @media (min-width: 700px) {
            .banda-in {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        /* ---------- Cortes ---------- */
        .seccion {
            padding: 64px 0;
        }

        .seccion-cab {
            margin-bottom: 32px;
        }

        .seccion-cab p {
            color: var(--gris-4);
            max-width: 560px;
            font-size: 1.05rem;
        }

        @media (min-width: 900px) {
            .seccion {
                padding: 96px 0;
            }
        }

        .lista-cortes {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .corte {
            height: 100%;
            display: flex;
            background: var(--blanco);
            border: 1px solid var(--gris-2);
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            transition: box-shadow 450ms ease, transform 450ms ease, border-color 450ms ease;
        }

        .corte:hover {
            box-shadow: 0 16px 36px rgba(0, 0, 0, .13);
            transform: translateY(-4px);
            border-color: var(--gris-3);
        }

        .corte-img {
            position: relative;
            width: 124px;
            flex-shrink: 0;
            overflow: hidden;
            background: linear-gradient(100deg, var(--gris-1) 30%, #f4f4f4 50%, var(--gris-1) 70%);
            background-size: 200% 100%;
            animation: esqueleto 1.4s linear infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gris-3);
        }

        .corte-img.sin-imagen {
            animation: none;
            background: var(--gris-1);
        }

        .corte-img svg {
            width: 32px;
            height: 32px;
        }

        .corte-img img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 500ms ease, transform 900ms cubic-bezier(.22, .61, .36, 1);
        }

        .corte-img img.cargada {
            opacity: 1;
        }

        .corte:hover .corte-img img {
            transform: scale(1.06);
        }

        .corte-img:has(img.cargada) {
            animation: none;
        }

        @keyframes esqueleto {
            to {
                background-position: -200% 0;
            }
        }

        .corte-cuerpo {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px 14px 14px 16px;
        }

        .corte-cuerpo h3 {
            font-size: 1.55rem;
            margin: 0;
            text-transform: uppercase;
        }

        .corte-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px 14px;
            color: var(--gris-4);
            font-size: .9rem;
        }

        .corte-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .corte-meta svg {
            width: 16px;
            height: 16px;
        }

        .corte-precio {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--negro);
        }

        .corte-desc {
            font-size: .92rem;
            color: var(--gris-4);
            display: none;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .corte .btn {
            margin-top: auto;
            width: 100%;
            padding: 12px 16px;
        }

        @media (min-width: 700px) {
            .lista-cortes {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }

            .corte {
                flex-direction: column;
            }

            .corte-img {
                width: 100%;
                aspect-ratio: 4 / 3;
            }

            .corte-cuerpo {
                padding: 20px;
                gap: 8px;
            }

            .corte-desc {
                display: -webkit-box;
            }
        }

        @media (min-width: 1000px) {
            .lista-cortes {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .vacio {
            text-align: center;
            padding: 56px 20px;
            border: 1px dashed var(--gris-3);
            border-radius: 16px;
            color: var(--gris-4);
        }

        .vacio svg {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
            color: var(--gris-3);
        }


        /* ---------- Botón "Ver todos los cortes" ---------- */
        .ver-mas {
            display: flex;
            justify-content: center;
            margin-top: 28px;
        }

        .ver-mas .btn {
            min-width: 260px;
        }

        @media (max-width: 480px) {
            .ver-mas .btn {
                width: 100%;
            }
        }

        /* ---------- Dónde estamos ---------- */
        .ubicacion {
            background: var(--gris-1);
        }

        .ubicacion-caja {
            display: grid;
            gap: 18px;
            padding: 28px;
            background: var(--blanco);
            border-radius: 18px;
        }

        .dato {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .dato svg {
            margin-top: 3px;
        }

        .dato strong {
            display: block;
            font-size: .78rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-3);
        }

        .dato span {
            font-size: 1.08rem;
        }

        .ubicacion-caja .btn {
            justify-self: start;
            margin-top: 4px;
        }

        /* ---------- Footer ---------- */
        .footer {
            background: var(--negro);
            color: var(--gris-1);
            padding: 26px 0;
        }

        .footer-in {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .footer .marca-nombre {
            color: var(--gris-1);
            font-size: 1.3rem;
        }

        .footer .marca img {
            height: 46px;
        }

        .footer-ayuda {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .footer-ayuda p {
            font-size: .85rem;
            color: var(--gris-3);
        }

        .footer-ayuda a {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1rem;
            color: var(--gris-1);
            text-decoration: none;
            transition: color var(--transicion);
        }

        .footer-ayuda a:hover {
            color: var(--blanco);
        }

        .footer-ayuda a .wa-icono {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            display: block;
            object-fit: contain;
        }

        @media (min-width: 700px) {
            .footer-in {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .footer-ayuda {
                text-align: right;
                align-items: flex-end;
            }
        }

        /* ---------- Aparición / desvanecimiento al hacer scroll (sube y baja) ---------- */
        [data-reveal] {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 700ms ease, transform 700ms cubic-bezier(.22, .61, .36, 1);
        }

        [data-reveal].visible {
            opacity: 1;
            transform: none;
            transition-delay: var(--d, 0ms);
        }

        /* ---------- Modal de reserva ---------- */
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
            transition: opacity 450ms ease, visibility 450ms ease;
        }

        .modal.abierto {
            opacity: 1;
            visibility: visible;
        }

        .modal-caja {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 560px;
            max-height: 92vh;
            max-height: 92dvh;
            background: var(--blanco);
            border-radius: 20px 20px 0 0;
            overflow: hidden;
            transform: translateY(48px);
            transition: transform 500ms cubic-bezier(.22, .61, .36, 1);
        }

        .modal.abierto .modal-caja {
            transform: none;
        }

        @media (min-width: 640px) {
            .modal {
                align-items: center;
                padding: 24px;
            }

            .modal-caja {
                border-radius: 20px;
                transform: translateY(24px) scale(.98);
            }

            .modal.abierto .modal-caja {
                transform: none;
            }
        }

        .modal-cab {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px 14px 20px;
            border-bottom: 1px solid var(--gris-1);
            flex-shrink: 0;
        }

        .modal-cab-texto {
            flex: 1;
            min-width: 0;
        }

        .modal-titulo {
            margin: 0;
            font-size: 1.7rem;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .modal-titulo:focus {
            outline: none;
        }

        .modal-sub {
            margin-top: 2px;
            font-size: .88rem;
            color: var(--gris-4);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .icono-btn {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--gris-2);
            border-radius: 10px;
            color: var(--negro);
            cursor: pointer;
            transition: background 400ms ease;
        }

        .icono-btn:hover {
            background: var(--gris-1);
        }

        .modal-cuerpo {
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            padding: 18px 20px 22px;
        }

        .modal-pie {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px calc(14px + env(safe-area-inset-bottom, 0px));
            border-top: 1px solid var(--gris-1);
            flex-shrink: 0;
            background: var(--blanco);
        }

        .modal-pie .btn {
            flex: 1 1 auto;
            padding: 15px 18px;
        }

        .pie-info {
            flex: 1 1 0;
            min-width: 0;
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.25;
        }

        .pie-info small {
            display: block;
            font-weight: 500;
            color: var(--gris-3);
        }

        .pie-info+.btn {
            flex: none;
        }

        /* Piezas del modal */
        .sep {
            margin: 22px 0 10px;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-3);
        }

        .opcion-cualquiera {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            padding: 16px;
            background: var(--negro);
            color: var(--gris-1);
            border: 1px solid var(--negro);
            border-radius: 14px;
            font: inherit;
            text-align: left;
            cursor: pointer;
            transition: background 400ms ease;
        }

        .opcion-cualquiera:hover {
            background: var(--gris-4);
        }

        .opcion-cualquiera .cuerpo {
            flex: 1;
            min-width: 0;
            display: block;
        }

        .opcion-cualquiera strong {
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.4rem;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .opcion-cualquiera small {
            display: block;
            margin-top: 2px;
            color: var(--gris-2);
            font-size: .88rem;
        }

        .opcion-cualquiera>svg:first-child {
            width: 26px;
            height: 26px;
        }

        .opcion-card {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            padding: 14px;
            margin-bottom: 12px;
            background: var(--blanco);
            border: 1px solid var(--gris-2);
            border-radius: 14px;
            font: inherit;
            color: inherit;
            text-align: left;
            cursor: pointer;
            transition: border-color 400ms ease, box-shadow 400ms ease;
        }

        .opcion-card:hover {
            border-color: var(--negro);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
        }

        .opcion-card .cuerpo {
            flex: 1;
            min-width: 0;
            display: block;
        }

        .opcion-card .nombre {
            display: block;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.4rem;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .opcion-card .desc {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-top: 2px;
            font-size: .9rem;
            color: var(--gris-4);
        }

        .opcion-card .meta {
            display: block;
            margin-top: 2px;
            font-size: .92rem;
            color: var(--gris-4);
        }

        .chev {
            color: var(--gris-3);
        }

        .prox {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--gris-1);
            font-size: .8rem;
            font-weight: 700;
        }

        .prox svg {
            width: 14px;
            height: 14px;
        }

        .prox.nada {
            padding-left: 0;
            background: transparent;
            color: var(--gris-3);
        }

        .avatar {
            position: relative;
            width: 56px;
            height: 56px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
            background: var(--gris-1);
            color: var(--gris-4);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .avatar img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar.grande {
            width: 84px;
            height: 84px;
            font-size: 1.9rem;
        }

        .avatar.mini {
            width: 40px;
            height: 40px;
            font-size: 1.05rem;
        }

        .opcion-cualquiera .avatar {
            background: var(--gris-4);
            color: var(--gris-1);
        }

        /* Perfil del barbero */
        .perfil-cab {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .perfil-cab h3 {
            margin: 0;
            font-size: 2rem;
            text-transform: uppercase;
        }

        .perfil-cab .prox {
            margin-top: 6px;
        }

        .perfil-desc {
            color: var(--gris-4);
            white-space: pre-line;
        }

        .perfil-desc.vacia {
            color: var(--gris-3);
            font-style: italic;
        }

        .bloque {
            margin-top: 24px;
        }

        .bloque h4 {
            margin-bottom: 10px;
            font-family: 'Manrope', sans-serif;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-3);
        }

        .galeria-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .foto {
            position: relative;
            aspect-ratio: 1;
            padding: 0;
            border: 0;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            background: linear-gradient(100deg, var(--gris-1) 30%, #f4f4f4 50%, var(--gris-1) 70%);
            background-size: 200% 100%;
            animation: esqueleto 1.4s linear infinite;
        }

        .foto img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 500ms ease, transform 700ms cubic-bezier(.22, .61, .36, 1);
        }

        .foto img.cargada {
            opacity: 1;
        }

        .foto:hover img {
            transform: scale(1.06);
        }

        .ubic {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            background: var(--gris-1);
            border-radius: 12px;
        }

        .ubic .linea {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .ubic .linea svg {
            margin-top: 3px;
        }

        .nota {
            margin-top: 10px;
            font-size: .88rem;
            color: var(--gris-3);
        }

        /* Día y hora */
        .resumen-mini {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            background: var(--gris-1);
            border-radius: 12px;
        }

        .resumen-mini .cuerpo {
            flex: 1;
            min-width: 0;
        }

        .resumen-mini strong {
            display: block;
            font-size: .95rem;
            line-height: 1.25;
        }

        .resumen-mini span.sub {
            display: block;
            font-size: .85rem;
            color: var(--gris-4);
        }

        .semana-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .semana-nav strong {
            font-size: .95rem;
            text-align: center;
        }

        .semana-nav .icono-btn {
            width: 40px;
            height: 40px;
        }

        .icono-btn:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        .icono-btn:disabled:hover {
            background: transparent;
        }

        .dias {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 6px;
            padding: 2px 0 10px;
        }

        .dia {
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 10px 6px;
            background: var(--blanco);
            border: 1px solid var(--gris-2);
            border-radius: 12px;
            font: inherit;
            color: var(--negro);
            cursor: pointer;
            scroll-snap-align: start;
            transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
        }

        .dia:hover:not(:disabled):not(.sel) {
            border-color: var(--negro);
        }

        .dia.sel {
            background: var(--negro);
            color: var(--gris-1);
            border-color: var(--negro);
        }

        .dia:disabled {
            opacity: .38;
            cursor: not-allowed;
            background: var(--gris-1);
        }

        .dia-sem {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .dia-num {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.9rem;
            font-weight: 600;
            line-height: 1;
        }

        .dia-mes {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .horas-cab {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 10px;
            margin: 18px 0 10px;
        }

        .horas-cab strong {
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-3);
        }

        .horas-cab span {
            font-weight: 700;
        }

        .horas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(98px, 1fr));
            gap: 10px;
        }

        .hora {
            padding: 13px 8px;
            background: var(--blanco);
            border: 1px solid var(--gris-2);
            border-radius: 10px;
            font: inherit;
            font-weight: 700;
            color: var(--negro);
            cursor: pointer;
            transition: background 400ms ease, color 400ms ease, border-color 400ms ease;
        }

        .hora:hover:not(.sel) {
            border-color: var(--negro);
        }

        .hora.sel {
            background: var(--negro);
            color: var(--gris-1);
            border-color: var(--negro);
        }

        /* Primer espacio */
        .destacado {
            padding: 22px;
            background: var(--negro);
            color: var(--gris-1);
            border-radius: 18px;
        }

        .destacado .rotulo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-2);
        }

        .destacado .rotulo svg {
            width: 16px;
            height: 16px;
        }

        .destacado .quien {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 16px 0 10px;
        }

        .destacado .quien strong {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.6rem;
            font-weight: 600;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .destacado .cuando {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.5rem;
            font-weight: 600;
            line-height: 1;
        }

        .destacado .avatar {
            background: var(--gris-4);
            color: var(--gris-1);
        }

        .enlace-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            padding: 10px 0;
            background: none;
            border: 0;
            font: inherit;
            font-weight: 700;
            color: var(--negro);
            text-decoration: underline;
            text-underline-offset: 4px;
            cursor: pointer;
        }

        /* Mensajes (vacío / error) */
        .mensaje {
            text-align: center;
            padding: 28px 8px;
        }

        .mensaje svg {
            width: 44px;
            height: 44px;
            margin-bottom: 12px;
            color: var(--gris-3);
        }

        .mensaje h3 {
            font-size: 1.8rem;
            text-transform: uppercase;
        }

        .mensaje p {
            color: var(--gris-4);
        }

        /* Resumen de la reserva (datos y éxito) */
        .resumen-lista {
            display: grid;
            gap: 14px;
            padding: 16px;
            margin-bottom: 20px;
            background: var(--gris-1);
            border-radius: 14px;
        }

        .resumen-lista .fila {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .resumen-lista .fila svg {
            margin-top: 2px;
            color: var(--gris-4);
        }

        .resumen-lista .fila>div {
            min-width: 0;
        }

        .resumen-lista small {
            display: block;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-3);
        }

        .resumen-lista strong {
            display: block;
            font-size: 1rem;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        /* Campos del formulario */
        .campo {
            margin-bottom: 16px;
        }

        .campo label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            font-size: .92rem;
        }

        .campo input {
            width: 100%;
            padding: 14px;
            font: inherit;
            font-size: 16px;
            color: var(--negro);
            background: var(--blanco);
            border: 1px solid var(--gris-3);
            border-radius: 10px;
            transition: border-color 400ms ease, box-shadow 400ms ease;
        }

        .campo input::placeholder {
            color: var(--gris-3);
        }

        .campo input:focus {
            outline: none;
            border-color: var(--negro);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, .12);
        }

        .campo.con-error input {
            border-color: var(--error);
        }

        .campo-error {
            margin-top: 6px;
            font-size: .88rem;
            font-weight: 700;
            color: var(--error);
        }

        .campo-ayuda {
            margin-top: 6px;
            font-size: .85rem;
            color: var(--gris-3);
        }

        .campo input.pin {
            max-width: 190px;
            text-align: center;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.9rem;
            font-weight: 600;
            letter-spacing: .4em;
            padding-left: calc(14px + .4em);
        }

        .alerta-form {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 14px;
            padding: 12px 14px;
            border: 1px solid var(--error);
            border-radius: 10px;
            background: #FDECEA;
            color: var(--error);
            font-size: .92rem;
            font-weight: 700;
        }

        .alerta-form svg {
            margin-top: 2px;
        }

        /* Campo trampa para bots (los humanos no lo ven) */
        .hp {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        /* Pantalla de éxito */
        .exito-cab {
            text-align: center;
            margin-bottom: 20px;
        }

        .exito-icono {
            width: 64px;
            height: 64px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--negro);
            color: var(--blanco);
        }

        .exito-icono svg {
            width: 32px;
            height: 32px;
        }

        .exito-cab h3 {
            margin: 0;
            font-size: 2rem;
            text-transform: uppercase;
        }

        .exito-cab p {
            margin-top: 4px;
            color: var(--gris-4);
        }

        .pin-caja {
            padding: 20px;
            margin-bottom: 18px;
            background: var(--negro);
            color: var(--gris-1);
            border-radius: 18px;
            text-align: center;
        }

        .pin-caja .rotulo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gris-2);
        }

        .pin-caja .rotulo svg {
            width: 16px;
            height: 16px;
        }

        .pin-digitos {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 16px 0;
        }

        .pin-digitos span {
            width: 58px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: var(--gris-4);
            color: var(--blanco);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 3rem;
            font-weight: 600;
            line-height: 1;
        }

        .pin-aviso {
            margin-bottom: 14px;
            font-size: .92rem;
            color: var(--gris-2);
        }

        .pin-caja .btn {
            width: 100%;
        }

        .exito-acciones {
            display: grid;
            gap: 10px;
        }

        .exito-acciones .btn {
            width: 100%;
        }


        /* ---------- Ver mi cita ---------- */
        .cita-card {
            padding: 16px;
            margin-bottom: 14px;
            background: var(--blanco);
            border: 1px solid var(--gris-2);
            border-radius: 16px;
        }

        .cita-cab {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .cita-cab h3 {
            margin: 0;
            font-size: 1.7rem;
            text-transform: uppercase;
        }

        .cita-precio {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .cita-card .resumen-lista {
            margin-bottom: 12px;
        }

        .cita-botones {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .cita-botones .btn {
            padding: 12px 10px;
        }

        .cita-card .btn.mapa {
            width: 100%;
            margin-top: 10px;
        }

        .aviso-tarde {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            background: var(--gris-1);
            font-size: .9rem;
            font-weight: 600;
            color: var(--gris-4);
        }

        .aviso-tarde svg {
            margin-top: 2px;
        }

        .chip {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            background: var(--gris-1);
            font-size: .78rem;
            font-weight: 700;
        }

        /* Esqueletos de carga */
        .sk {
            background: linear-gradient(100deg, var(--gris-1) 30%, #f4f4f4 50%, var(--gris-1) 70%);
            background-size: 200% 100%;
            animation: esqueleto 1.4s linear infinite;
            border-radius: 12px;
        }

        .sk-card {
            height: 96px;
            margin-bottom: 12px;
            border-radius: 14px;
        }

        .sk-linea {
            height: 18px;
            margin-bottom: 10px;
        }

        .sk-dias {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .sk-dia {
            flex: 0 0 66px;
            height: 88px;
        }

        .sk-horas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(98px, 1fr));
            gap: 10px;
        }

        .sk-hora {
            height: 46px;
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                transition-duration: 1ms !important;
                animation-duration: 1ms !important;
            }

            [data-reveal] {
                opacity: 1;
                transform: none;
            }

            .flota {
                animation: none;
            }
        }

        /* ---------- Ajustes anti-desborde en móvil ---------- */
        html,
        body {
            max-width: 100%;
            overflow-x: clip;
        }

        .hero-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        @media (min-width: 900px) {
            .hero-grid {
                grid-template-columns: 1.15fr .85fr;
            }
        }

        .hero-grid>*,
        .banda-in>*,
        .footer-in>* {
            min-width: 0;
        }

        @media (max-width: 480px) {
            .contenedor {
                padding: 0 16px;
            }

            .header-in {
                gap: 10px;
            }

            .marca {
                gap: 10px;
                flex: 1 1 auto;
            }

            .marca img {
                flex-shrink: 0;
            }

            .marca-nombre {
                font-size: 1.25rem;
                letter-spacing: .03em;
            }

            .menu-btn {
                flex-shrink: 0;
            }

            h1 {
                font-size: clamp(2.4rem, 12vw, 3.2rem);
            }

            .pasos {
                gap: 8px;
            }

            .paso strong {
                font-size: 1.8rem;
            }

            .paso span {
                font-size: .8rem;
            }

            .modal-titulo {
                font-size: 1.5rem;
            }

            .destacado .cuando {
                font-size: 2.1rem;
            }

            .pin-digitos span {
                width: 52px;
                height: 66px;
                font-size: 2.6rem;
            }

            .cita-cab h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    {{-- ===== Header ===== --}}
    <header class="header" id="header">
        <div class="contenedor header-in">
            <a href="{{ route('inicio') }}" class="marca" aria-label="{{ $marca }}, inicio">
                <img src="{{ asset('images/logo.png') }}" alt="" onerror="this.style.display='none'">
                <span class="marca-nombre">{{ $marca }}</span>
            </a>

            <nav class="nav" aria-label="Principal">
                <a href="#cortes" class="enlace">Cortes</a>

                <a href="#mi-cita" class="enlace js-mi-cita">Ver mi cita</a>
                <a href="{{ route('login') }}" class="btn btn-claro btn-sm">
                    <i data-lucide="scissors"></i> Soy barbero
                </a>
            </nav>

            <button type="button" class="menu-btn" id="menuAbrir" aria-label="Abrir menú" aria-expanded="false"
                aria-controls="menuMovil">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </header>

    {{-- Menú móvil --}}
    <div class="menu-movil" id="menuMovil" aria-hidden="true">
        <div class="menu-movil-cab">
            <a href="{{ route('inicio') }}" class="marca">
                <img src="{{ asset('images/logo.png') }}" alt="" onerror="this.style.display='none'">
                <span class="marca-nombre">{{ $marca }}</span>
            </a>
            <button type="button" class="menu-btn" id="menuCerrar" aria-label="Cerrar menú">
                <i data-lucide="x"></i>
            </button>
        </div>
        <nav aria-label="Menú móvil">
            <a href="#cortes" class="enlace"><i data-lucide="scissors"></i> Cortes</a>

            <a href="#mi-cita" class="enlace js-mi-cita"><i data-lucide="calendar-check"></i> Ver mi cita</a>
            <a href="{{ route('login') }}" class="btn btn-claro"><i data-lucide="scissors"></i> Soy barbero</a>
        </nav>
    </div>

    <main>
        {{-- ===== Portada ===== --}}
        <section class="hero" id="inicio">
            <div class="contenedor hero-grid">
                <div class="hero-contenido" id="heroContenido">
                    <span class="etiqueta" data-reveal><i data-lucide="calendar-clock"></i> Reservas en línea</span>
                    <h1 data-reveal style="--d:80ms">Reserva tu corte en minutos</h1>
                    <p class="apoyo" data-reveal style="--d:160ms">Elige tu corte, tu barbero y tu hora. Sin crear
                        cuenta y sin hacer filas.</p>

                    <div class="hero-botones" data-reveal style="--d:240ms">
                        <a href="#cortes" class="btn"><i data-lucide="calendar-plus"></i> Reservar ahora</a>
                        <button type="button" class="btn btn-claro" id="btnPrimerEspacio">
                            <i data-lucide="zap"></i> Primer espacio disponible
                        </button>
                    </div>

                    <div class="pasos" data-reveal style="--d:320ms" aria-label="Cómo funciona">
                        <div class="paso"><strong>01</strong><span>Elige tu corte</span></div>
                        <div class="paso"><strong>02</strong><span>Elige tu hora</span></div>
                        <div class="paso"><strong>03</strong><span>Confirma tus datos</span></div>
                    </div>
                </div>

                <div class="hero-visual" data-reveal style="--d:200ms" aria-hidden="true">
                    <div class="hero-panel">
                        <img src="{{ asset('images/logo.png') }}" alt="" onerror="this.style.display='none'">
                    </div>
                    <div class="flota a"><i data-lucide="user-check"></i> Sin crear cuenta</div>
                    <div class="flota b"><i data-lucide="timer"></i> Reserva en 1 minuto</div>
                </div>
            </div>
        </section>

        {{-- ===== Banda Ver mi cita ===== --}}
        <section class="banda" id="mi-cita">
            <div class="contenedor banda-in">
                <div class="banda-texto" data-reveal>
                    <span class="banda-icono"><i data-lucide="calendar-check"></i></span>
                    <div>
                        <strong>¿Ya tienes una cita?</strong>
                        <span>Revísala, cámbiala de hora o cancélala.</span>
                    </div>
                </div>
                <div data-reveal style="--d:120ms">
                    <button type="button" class="btn btn-blanco js-mi-cita-btn">
                        <i data-lucide="search"></i> Ver mi cita
                    </button>
                </div>
            </div>
        </section>

        {{-- ===== Cortes ===== --}}
        <section class="seccion" id="cortes">
            <div class="contenedor">
                <div class="seccion-cab" data-reveal>
                    <h2>Elige tu corte</h2>
                    <p>Toca el que quieras y te mostramos los barberos y las horas disponibles.</p>
                </div>

                @if (empty($cortes))
                    <div class="vacio" data-reveal>
                        <i data-lucide="scissors"></i>
                        <p>Pronto tendremos cortes disponibles para reservar.</p>
                    </div>
                @else
                    <div class="lista-cortes">
                        @foreach ($cortes as $c)
                            <div class="corte-item" data-reveal>
                                <article class="corte js-corte" data-id="{{ $c['id'] }}"
                                    data-nombre="{{ $c['nombre'] }}">
                                    <div class="corte-img {{ $c['imagen'] ? '' : 'sin-imagen' }}">
                                        @if ($c['imagen'])
                                            <img src="{{ $c['imagen'] }}" alt="{{ $c['nombre'] }}"
                                                loading="lazy">
                                        @else
                                            <i data-lucide="scissors"></i>
                                        @endif
                                    </div>
                                    <div class="corte-cuerpo">
                                        <h3>{{ $c['nombre'] }}</h3>
                                        <div class="corte-meta">
                                            <span class="corte-precio">{{ $c['precio'] }}</span>
                                            <span><i data-lucide="clock"></i> {{ $c['duracion'] }} min</span>
                                        </div>
                                        @if ($c['descripcion'])
                                            <p class="corte-desc">{{ $c['descripcion'] }}</p>
                                        @endif
                                        <button type="button" class="btn">
                                            Reservar <i data-lucide="arrow-right"></i>
                                        </button>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>


    </main>

    {{-- ===== Footer ===== --}}
    <footer class="footer">
        <div class="contenedor footer-in">
            <a href="{{ route('inicio') }}" class="marca" aria-label="{{ $marca }}, inicio">
                <img src="{{ asset('images/logo.png') }}" alt="" onerror="this.style.display='none'">
                <span class="marca-nombre">{{ $marca }}</span>
            </a>

            <div class="footer-ayuda">
                <p>¿Tienes alguna duda?</p>
                <a href="https://wa.me/50662115194" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/whatsapp.png') }}" alt="" class="wa-icono" width="22"
                        height="22" onerror="this.style.display='none'">
                    Escríbenos · 6211 5194
                </a>
            </div>
        </div>
    </footer>

    {{-- ===== Modal (una sola ventana con pantallas internas: reservar y ver mi cita) ===== --}}
    <div class="modal" id="modalReserva" aria-hidden="true">
        <div class="modal-caja" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
            <div class="modal-cab">
                <button type="button" class="icono-btn" id="modalAtras" aria-label="Volver" hidden>
                    <i data-lucide="arrow-left"></i>
                </button>
                <div class="modal-cab-texto">
                    <h2 class="modal-titulo" id="modalTitulo" tabindex="-1"></h2>
                    <p class="modal-sub" id="modalSub" hidden></p>
                </div>
                <button type="button" class="icono-btn" id="modalCerrar" aria-label="Cerrar">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-cuerpo" id="modalCuerpo"></div>
            <div class="modal-pie" id="modalPie" hidden></div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            if (window.lucide) lucide.createIcons();

            const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // ---------- Header compacto al hacer scroll ----------
            const header = document.getElementById('header');
            const heroContenido = document.getElementById('heroContenido');
            let pendiente = false;

            function alScroll() {
                const y = window.scrollY;
                header.classList.toggle('compacto', y > 8);

                // La portada se difumina suavemente al bajar
                if (!reducir && heroContenido) {
                    const limite = Math.max(heroContenido.offsetHeight, 300);
                    const t = Math.min(Math.max(y / limite, 0), 1);
                    heroContenido.style.opacity = String(1 - t * 0.85);
                    heroContenido.style.transform = 'translateY(' + (t * -24).toFixed(1) + 'px)';
                }
                pendiente = false;
            }

            window.addEventListener('scroll', function() {
                if (!pendiente) {
                    pendiente = true;
                    requestAnimationFrame(alScroll);
                }
            }, {
                passive: true
            });
            alScroll();

            // ---------- Menú móvil ----------
            const menu = document.getElementById('menuMovil');
            const btnAbrir = document.getElementById('menuAbrir');
            const btnCerrar = document.getElementById('menuCerrar');

            function abrirMenu() {
                menu.classList.add('abierto');
                menu.setAttribute('aria-hidden', 'false');
                btnAbrir.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function cerrarMenu() {
                menu.classList.remove('abierto');
                menu.setAttribute('aria-hidden', 'true');
                btnAbrir.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            btnAbrir.addEventListener('click', abrirMenu);
            btnCerrar.addEventListener('click', cerrarMenu);
            menu.querySelectorAll('a.enlace').forEach(function(a) {
                a.addEventListener('click', cerrarMenu);
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') cerrarMenu();
            });
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 900) cerrarMenu();
            });

            // ---------- Aparece al entrar y se desvanece al salir (sube y baja) ----------
            const items = document.querySelectorAll('[data-reveal]');

            // Las tarjetas de cortes entran escalonadas
            document.querySelectorAll('.lista-cortes .corte-item').forEach(function(el, i) {
                el.style.setProperty('--d', ((i % 3) * 110) + 'ms');
            });


            // ---------- "Ver todos los cortes" (solo si hay más de 8) ----------
            (function() {
                const MAXIMO_SIN_BOTON = 8;
                const VISIBLES = 6;
                const lista = document.querySelector('.lista-cortes');
                if (!lista) return;

                const tarjetas = Array.prototype.slice.call(lista.querySelectorAll('.corte-item'));
                if (tarjetas.length <= MAXIMO_SIN_BOTON) return;

                const extras = tarjetas.slice(VISIBLES);
                let abierto = false;

                const caja = document.createElement('div');
                caja.className = 'ver-mas';

                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'btn btn-claro';
                caja.appendChild(boton);

                function pintarBoton() {
                    boton.innerHTML = abierto ?
                        '<i data-lucide="chevron-up"></i> Ver menos' :
                        '<i data-lucide="chevron-down"></i> Ver todos los cortes (' + tarjetas.length + ')';
                    boton.setAttribute('aria-expanded', String(abierto));
                    if (window.lucide) lucide.createIcons();
                }

                extras.forEach(function(el) {
                    el.hidden = true;
                });

                boton.addEventListener('click', function() {
                    abierto = !abierto;
                    extras.forEach(function(el) {
                        el.hidden = !abierto;
                    });
                    pintarBoton();
                    if (!abierto) {
                        const sec = document.getElementById('cortes');
                        if (sec) sec.scrollIntoView({
                            behavior: reducir ? 'auto' : 'smooth'
                        });
                    }
                });

                lista.insertAdjacentElement('afterend', caja);
                pintarBoton();
            })();
            if ('IntersectionObserver' in window && !reducir) {
                const obs = new IntersectionObserver(function(entradas) {
                    entradas.forEach(function(en) {
                        en.target.classList.toggle('visible', en.isIntersecting);
                    });
                }, {
                    threshold: 0.12,
                    rootMargin: '0px 0px -6% 0px'
                });
                items.forEach(function(el) {
                    obs.observe(el);
                });
            } else {
                items.forEach(function(el) {
                    el.classList.add('visible');
                });
            }

            // ---------- Esqueleto de imágenes ----------
            document.querySelectorAll('.corte-img img').forEach(function(img) {
                if (img.complete) img.classList.add('cargada');
                else {
                    img.addEventListener('load', function() {
                        img.classList.add('cargada');
                    });
                    img.addEventListener('error', function() {
                        img.closest('.corte-img').classList.add('sin-imagen');
                        img.remove();
                    });
                }
            });

            // =====================================================================
            //  MODAL: reserva y "Ver mi cita" (pantallas internas)
            // =====================================================================
            const CORTES = {{ \Illuminate\Support\Js::from($cortes) }};
            const URL_BARBEROS = {{ \Illuminate\Support\Js::from(route('reserva.barberos', 0)) }};
            const URL_DISP = {{ \Illuminate\Support\Js::from(route('reserva.disponibilidad')) }};
            const URL_PRIMER = {{ \Illuminate\Support\Js::from(route('reserva.primer')) }};
            const URL_STORE = {{ \Illuminate\Support\Js::from(route('reserva.store')) }};
            const URL_MC = {{ \Illuminate\Support\Js::from(route('reserva.mi-cita.index')) }};
            const URL_MC_VERIFICAR = {{ \Illuminate\Support\Js::from(route('reserva.mi-cita.verificar')) }};
            const URL_MC_SALIR = {{ \Illuminate\Support\Js::from(route('reserva.mi-cita.salir')) }};
            const URL_MC_OLVIDE = {{ \Illuminate\Support\Js::from(route('reserva.mi-cita.olvide')) }};
            const CSRF = document.querySelector('meta[name="csrf-token"]').content;
            const MARCA = {{ \Illuminate\Support\Js::from($marca) }};
            const ABRIR_MI_CITA = {{ \Illuminate\Support\Js::from((bool) session('abrir_mi_cita')) }};
            const ENLACE_INVALIDO = {{ \Illuminate\Support\Js::from((bool) session('enlace_invalido')) }};

            const modal = document.getElementById('modalReserva');
            const tituloEl = document.getElementById('modalTitulo');
            const subEl = document.getElementById('modalSub');
            const atrasEl = document.getElementById('modalAtras');
            const cerrarEl = document.getElementById('modalCerrar');
            const cuerpo = document.getElementById('modalCuerpo');
            const pieEl = document.getElementById('modalPie');

            let estado = nuevoEstado(null);
            let mc = nuevoMc(); // estado de "Ver mi cita"
            let pila = []; // pantallas visitadas (para el botón Atrás)
            let acciones = {}; // acciones de la pantalla actual
            let cache = {}; // respuestas del servidor mientras el modal está abierto
            let token = 0; // evita que una respuesta tardía pise otra pantalla
            let ultimoFoco = null;
            let enviando = false; // evita enviar dos veces
            let pinCopiado = false; // para avisar si cierra sin guardar el PIN

            const MENSAJES = {
                red: ['No pudimos conectarnos', 'Revisa tu conexión a internet e inténtalo de nuevo.'],
                limite: ['Muchas consultas seguidas', 'Espera un momento e inténtalo otra vez.'],
                'no-existe': ['Ya no está disponible',
                    'Este corte o barbero cambió. Actualiza la página para ver las opciones al día.'
                ],
                servidor: ['Algo salió mal', 'No es culpa tuya. Inténtalo de nuevo en unos segundos.'],
            };

            // ----- utilidades -----
            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    } [c];
                });
            }

            function iniciales(nombre) {
                return String(nombre || '?').trim().split(/\s+/).slice(0, 2)
                    .map(function(p) {
                        return p.charAt(0);
                    }).join('').toUpperCase();
            }

            function avatar(b, extra) {
                return '<span class="avatar ' + (extra || '') + '">' +
                    '<span class="ini">' + esc(iniciales(b.nombre)) + '</span>' +
                    (b.foto ? '<img src="' + esc(b.foto) + '" alt="" loading="lazy" onerror="this.remove()">' : '') +
                    '</span>';
            }

            function nuevoEstado(corte) {
                return {
                    corte: corte,
                    barbero: null,
                    fecha: null,
                    hora: null,
                    cuando: null,
                    nombre: '',
                    telefono: '',
                    email: '',
                    exito: false
                };
            }

            function nuevoMc() {
                return {
                    verificado: false,
                    citas: [],
                    cita: null,
                    barbero: null,
                    fecha: null,
                    hora: null
                };
            }

            function limpiarMomento() {
                estado.fecha = null;
                estado.hora = null;
                estado.cuando = null;
            }

            function urlBarberos(id) {
                return URL_BARBEROS.replace('/corte/0/', '/corte/' + encodeURIComponent(id) + '/');
            }

            function urlDisp(corte, barbero) {
                return URL_DISP + '?corte=' + encodeURIComponent(corte) + '&barbero=' + encodeURIComponent(barbero);
            }

            function urlPrimer(corte) {
                return URL_PRIMER + '?corte=' + encodeURIComponent(corte);
            }

            function urlMc(id, accion) {
                return URL_MC + '/' + encodeURIComponent(id) + '/' + accion;
            }

            async function pedir(url) {
                let r;
                try {
                    r = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                } catch (e) {
                    throw {
                        tipo: 'red'
                    };
                }
                if (r.status === 401) throw {
                    tipo: 'sin-sesion'
                };
                if (r.status === 404) throw {
                    tipo: 'no-existe'
                };
                if (r.status === 429) throw {
                    tipo: 'limite'
                };
                if (!r.ok) {
                    let d = null;
                    try {
                        d = await r.json();
                    } catch (e) {
                        d = null;
                    }
                    if (d && d.codigo === 'muy-tarde') throw {
                        tipo: 'muy-tarde',
                        mensaje: d.mensaje
                    };
                    throw {
                        tipo: 'servidor'
                    };
                }
                try {
                    return await r.json();
                } catch (e) {
                    throw {
                        tipo: 'servidor'
                    };
                }
            }

            // POST con JSON. Devuelve { caida } o { status, d }.
            async function enviarJson(url, cuerpoJson) {
                let r;
                try {
                    r = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(cuerpoJson || {}),
                    });
                } catch (e) {
                    return {
                        caida: true
                    };
                }
                let d = null;
                try {
                    d = await r.json();
                } catch (e) {
                    d = null;
                }
                return {
                    status: r.status,
                    d: d
                };
            }

            function cargar(url, t, ok, reintentar) {
                if (cache[url]) {
                    ok(cache[url]);
                    return;
                }
                pedir(url).then(function(d) {
                    cache[url] = d;
                    if (t === token) ok(d);
                }).catch(function(err) {
                    if (t === token) vistaError(err, reintentar);
                });
            }

            // ----- control del modal -----
            function cabecera(titulo, sub) {
                tituloEl.textContent = titulo;
                subEl.textContent = sub || '';
                subEl.hidden = !sub;
                atrasEl.hidden = pila.length < 2;
                if (modal.classList.contains('abierto')) tituloEl.focus({
                    preventScroll: true
                });
            }

            function pintar(html, pie, acc) {
                acciones = acc || {};
                cuerpo.innerHTML = html;
                cuerpo.scrollTop = 0;
                activarCalendario();
                if (pie) {
                    pieEl.innerHTML = pie;
                    pieEl.hidden = false;
                } else {
                    pieEl.innerHTML = '';
                    pieEl.hidden = true;
                }
                if (window.lucide) lucide.createIcons();
            }

            // ----- Calendario por semanas (7 días a la vez, con flechas) -----
            function calendarioHtml(dias, selDia) {
                const botones = dias.map(function(x, i) {
                    return '<button type="button" class="dia' + (i === selDia ? ' sel' : '') +
                        '" data-a="dia" data-i="' + i + '"' +
                        (x.disponible ? '' : ' disabled') + ' aria-pressed="' + (i === selDia) + '"' +
                        ' aria-label="' + esc(x.etiqueta) + (x.disponible ? '' : ', sin espacios') + '">' +
                        '<span class="dia-sem">' + esc(x.semana) + '</span>' +
                        '<span class="dia-num">' + esc(x.numero) + '</span>' +
                        '<span class="dia-mes">' + esc(x.mes) + '</span></button>';
                }).join('');

                return '<div class="semana-nav">' +
                    '<button type="button" class="icono-btn" id="semAnt" aria-label="Semana anterior"><i data-lucide="chevron-left"></i></button>' +
                    '<strong id="semTitulo" aria-live="polite"></strong>' +
                    '<button type="button" class="icono-btn" id="semSig" aria-label="Semana siguiente"><i data-lucide="chevron-right"></i></button>' +
                    '</div><div class="dias" id="dias">' + botones + '</div>';
            }

            function activarCalendario() {
                const caja = cuerpo.querySelector('#dias');
                const ant = document.getElementById('semAnt');
                const sig = document.getElementById('semSig');
                const titulo = document.getElementById('semTitulo');
                if (!caja || !ant || !sig || !titulo) return;

                const POR_PAGINA = 7;
                const botones = Array.prototype.slice.call(caja.querySelectorAll('.dia'));
                const paginas = Math.ceil(botones.length / POR_PAGINA);
                const sel = botones.findIndex(function(b) {
                    return b.classList.contains('sel');
                });
                let pagina = sel > 0 ? Math.floor(sel / POR_PAGINA) : 0;

                function texto(b) {
                    return b.querySelector('.dia-num').textContent + ' ' + b.querySelector('.dia-mes').textContent;
                }

                function mostrar() {
                    const ini = pagina * POR_PAGINA;
                    const fin = Math.min(ini + POR_PAGINA, botones.length) - 1;
                    botones.forEach(function(b, i) {
                        b.hidden = i < ini || i > fin;
                    });
                    titulo.textContent = texto(botones[ini]) + ' – ' + texto(botones[fin]);
                    ant.disabled = pagina === 0;
                    sig.disabled = pagina >= paginas - 1;
                }

                ant.parentNode.hidden = paginas <= 1;
                ant.addEventListener('click', function() {
                    if (pagina > 0) {
                        pagina--;
                        mostrar();
                    }
                });
                sig.addEventListener('click', function() {
                    if (pagina < paginas - 1) {
                        pagina++;
                        mostrar();
                    }
                });
                mostrar();
            }

            function ir(vista) {
                pila.push(vista);
                vista();
            }

            function atras() {
                if (pila.length > 1) {
                    pila.pop();
                    pila[pila.length - 1]();
                }
            }

            function abrirModal() {
                ultimoFoco = document.activeElement;
                cuerpo.innerHTML = '';
                pieEl.hidden = true;
                modal.classList.add('abierto');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function cerrarModal() {
                if (!modal.classList.contains('abierto')) return;
                token++;
                modal.classList.remove('abierto');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';

                // Si había entrado a "Ver mi cita", cerramos esa verificación en el servidor.
                if (mc.verificado) {
                    try {
                        fetch(URL_MC_SALIR, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            keepalive: true,
                        }).catch(function() {});
                    } catch (e) {
                        /* sin hacer nada */
                    }
                }

                pila = [];
                cache = {};
                enviando = false;
                estado = nuevoEstado(null); // borra nombre y teléfono escritos
                mc = nuevoMc();
                if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus({
                    preventScroll: true
                });
            }

            // Cierre "amable": si ya reservó y no copió el PIN, confirma antes de cerrar.
            function intentarCerrar() {
                if (estado.exito && !pinCopiado && window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: '¿Guardaste tu PIN?',
                        text: 'No lo volveremos a mostrar. Lo necesitas para ver, cambiar o cancelar tu cita.',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, ya lo guardé',
                        cancelButtonText: 'Volver',
                        confirmButtonColor: '#000000',
                        cancelButtonColor: '#525252',
                        reverseButtons: true,
                        focusCancel: true,
                    }).then(function(r) {
                        if (r.isConfirmed) cerrarModal();
                    });
                    return;
                }
                cerrarModal();
            }

            function despachar(e) {
                const el = e.target.closest('[data-a]');
                if (!el || el.disabled) return;
                const f = acciones[el.dataset.a];
                if (f) f(el);
            }

            cuerpo.addEventListener('click', despachar);
            pieEl.addEventListener('click', despachar);
            atrasEl.addEventListener('click', atras);
            cerrarEl.addEventListener('click', intentarCerrar);

            // Cierra al hacer clic fuera (solo si el clic empezó y terminó en el fondo)
            let bajoEn = null;
            modal.addEventListener('mousedown', function(e) {
                bajoEn = e.target;
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal && bajoEn === modal) intentarCerrar();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('abierto') && !(window.Swal && Swal
                        .isVisible())) {
                    intentarCerrar();
                }
            });

            // Mantiene el foco dentro del modal con Tab
            modal.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') return;
                const f = Array.prototype.filter.call(
                    modal.querySelectorAll(
                        'button:not([disabled]), a[href], input:not([tabindex="-1"]), [tabindex]:not([tabindex="-1"])'
                    ),
                    function(x) {
                        return x.offsetParent !== null;
                    }
                );
                if (!f.length) return;
                const primero = f[0],
                    ultimo = f[f.length - 1];
                if (e.shiftKey && (document.activeElement === primero || document.activeElement === tituloEl)) {
                    e.preventDefault();
                    ultimo.focus();
                } else if (!e.shiftKey && document.activeElement === ultimo) {
                    e.preventDefault();
                    primero.focus();
                }
            });

            // ----- esqueletos -----
            function esqueletoLista(n) {
                let h = '';
                for (let i = 0; i < n; i++) h += '<div class="sk sk-card"></div>';
                return h;
            }

            function esqueletoHora() {
                let dias = '',
                    horas = '';
                for (let i = 0; i < 6; i++) dias += '<div class="sk sk-dia"></div>';
                for (let i = 0; i < 8; i++) horas += '<div class="sk sk-hora"></div>';
                return '<div class="sk sk-card" style="height:64px"></div><div class="sk-dias">' + dias + '</div>' +
                    '<div class="sk sk-linea" style="width:40%"></div><div class="sk-horas">' + horas + '</div>';
            }

            function esqueletoPrimer() {
                return '<div class="sk" style="height:170px;border-radius:18px;margin-bottom:20px"></div>' +
                    esqueletoLista(2);
            }

            // ----- mensajes -----
            function vistaMensaje(icono, titulo, texto, pie, acc) {
                pintar(
                    '<div class="mensaje"><i data-lucide="' + icono + '"></i><h3>' + esc(titulo) + '</h3><p>' + esc(
                        texto) + '</p></div>',
                    pie, acc
                );
            }

            function vistaError(err, reintentar) {
                const tipo = err && err.tipo;

                // Errores propios de "Ver mi cita"
                if (tipo === 'sin-sesion') {
                    vistaMensaje('lock', 'Tu sesión terminó', 'Escribe tu teléfono y tu PIN otra vez para continuar.',
                        '<button type="button" class="btn" data-a="acceder">Escribir mi PIN</button>', {
                            acceder: function() {
                                pila = [];
                                mc = nuevoMc();
                                ir(vistaMcAcceso);
                            },
                        });
                    return;
                }

                if (mc.verificado && (tipo === 'muy-tarde' || tipo === 'no-existe')) {
                    const texto = tipo === 'muy-tarde' && err.mensaje ?
                        err.mensaje :
                        'Esa cita o ese barbero ya no está disponible.';
                    vistaMensaje('clock', tipo === 'muy-tarde' ? 'Ya no se puede cambiar' : 'Ya no está disponible',
                        texto,
                        '<button type="button" class="btn" data-a="lista">Volver a mis citas</button>', {
                            lista: recargarLista,
                        });
                    return;
                }

                const m = MENSAJES[tipo] || MENSAJES.servidor;
                const sinRetry = tipo === 'no-existe';
                const pie = sinRetry ?
                    '<button type="button" class="btn" data-a="refrescar">Actualizar página</button>' :
                    '<button type="button" class="btn btn-claro" data-a="cerrar">Cerrar</button>' +
                    '<button type="button" class="btn" data-a="reintentar">Reintentar</button>';
                vistaMensaje('wifi-off', m[0], m[1], pie, {
                    reintentar: function() {
                        reintentar();
                    },
                    cerrar: cerrarModal,
                    refrescar: function() {
                        window.location.reload();
                    },
                });
            }

            // =====================================================================
            //  PANTALLAS DE RESERVA
            // =====================================================================

            // --- Elegir corte (solo cuando se entra por "Primer espacio disponible") ---
            function vistaElegirCorte() {
                ++token;
                cabecera('Elige tu corte', 'Para buscar el primer espacio libre');

                if (!CORTES.length) {
                    vistaMensaje('scissors', 'Aún no hay cortes', 'Pronto tendremos cortes disponibles para reservar.',
                        '<button type="button" class="btn" data-a="cerrar">Cerrar</button>', {
                            cerrar: cerrarModal
                        });
                    return;
                }

                pintar(CORTES.map(function(c, i) {
                    return '<button type="button" class="opcion-card" data-a="corte" data-i="' + i + '">' +
                        '<span class="cuerpo"><span class="nombre">' + esc(c.nombre) + '</span>' +
                        '<span class="meta">' + esc(c.duracion) + ' min · ' + esc(c.precio) +
                        '</span></span>' +
                        '<i data-lucide="chevron-right" class="chev"></i></button>';
                }).join(''), '', {
                    corte: function(el) {
                        estado.corte = CORTES[+el.dataset.i];
                        estado.barbero = null;
                        limpiarMomento();
                        ir(vistaPrimer);
                    },
                });
            }

            // --- Elegir barbero ---
            function cardBarbero(b, i) {
                return '<button type="button" class="opcion-card" data-a="barbero" data-i="' + i + '">' +
                    avatar(b) +
                    '<span class="cuerpo"><span class="nombre">' + esc(b.nombre) + '</span>' +
                    (b.descripcion ? '<span class="desc">' + esc(b.descripcion) + '</span>' : '') +
                    '</span><i data-lucide="chevron-right" class="chev"></i></button>';
            }

            function vistaBarberos() {
                const t = ++token;
                cabecera('Elige tu barbero', estado.corte.nombre + ' · ' + estado.corte.precio);
                pintar(esqueletoLista(3), '', {});

                cargar(urlBarberos(estado.corte.id), t, function(d) {
                    const lista = d.barberos || [];

                    if (!lista.length) {
                        vistaMensaje('user-x', 'Sin barberos por ahora',
                            'No hay barberos disponibles para este corte. Prueba con otro.',
                            '<button type="button" class="btn" data-a="cerrar">Cerrar</button>', {
                                cerrar: cerrarModal
                            });
                        return;
                    }

                    let html = '';
                    if (lista.length > 1) {
                        html += '<button type="button" class="opcion-cualquiera" data-a="cualquiera">' +
                            '<i data-lucide="zap"></i>' +
                            '<span class="cuerpo"><strong>Cualquier barbero</strong><small>Te mostramos el primer espacio libre</small></span>' +
                            '<i data-lucide="chevron-right"></i></button>' +
                            '<p class="sep">O elige a tu barbero</p>';
                    }
                    html += lista.map(cardBarbero).join('');

                    pintar(html, '', {
                        cualquiera: function() {
                            estado.barbero = null;
                            limpiarMomento();
                            ir(vistaPrimer);
                        },
                        barbero: function(el) {
                            estado.barbero = lista[+el.dataset.i];
                            limpiarMomento();
                            ir(vistaPerfil);
                        },
                    });
                }, vistaBarberos);
            }

            // --- Perfil del barbero ---
            function vistaPerfil() {
                ++token;
                const b = estado.barbero;
                cabecera('Tu barbero', estado.corte.nombre);

                const prox = b.proximo ?
                    '<span class="prox"><i data-lucide="clock"></i> ' + esc(b.proximo.texto) + '</span>' :
                    '<span class="prox nada">Sin espacios por ahora</span>';

                let html = '<div class="perfil-cab">' + avatar(b, 'grande') +
                    '<div><h3>' + esc(b.nombre) + '</h3>' + prox + '</div></div>';

                html += b.descripcion ?
                    '<p class="perfil-desc">' + esc(b.descripcion) + '</p>' :
                    '<p class="perfil-desc vacia">Este barbero todavía no ha escrito su descripción.</p>';

                const gal = b.galeria || [];
                if (gal.length) {
                    html += '<div class="bloque"><h4>Sus trabajos</h4><div class="galeria-grid">' +
                        gal.map(function(g, i) {
                            return '<button type="button" class="foto" data-a="foto" data-i="' + i +
                                '" aria-label="Ver foto ' + (i + 1) + '">' +
                                '<img src="' + esc(g.url) + '" alt="" loading="lazy" ' +
                                'onload="this.classList.add(\'cargada\')" onerror="this.closest(\'.foto\').remove()"></button>';
                        }).join('') + '</div></div>';
                }


                if (!b.proximo) {
                    html += '<p class="nota">Este barbero no tiene espacios en los próximos días. Prueba con otro.</p>';
                }

                const pie = '<button type="button" class="btn" data-a="elegir"' + (b.proximo ? '' : ' disabled') + '>' +
                    'Elegir a ' + esc(b.nombre) + ' <i data-lucide="arrow-right"></i></button>';

                pintar(html, pie, {
                    elegir: function() {
                        ir(vistaHora);
                    },
                    foto: function(el) {
                        const g = gal[+el.dataset.i];
                        if (!g || !window.Swal) return;
                        Swal.fire({
                            imageUrl: g.url,
                            imageAlt: g.descripcion || 'Trabajo de ' + b.nombre,
                            title: g.descripcion || undefined,
                            width: 'min(92vw, 560px)',
                            showConfirmButton: false,
                            showCloseButton: true,
                        });
                    },
                });
            }

            // --- Día y hora ---
            function vistaHora() {
                const t = ++token;
                const b = estado.barbero;
                cabecera('Día y hora', estado.corte.nombre + ' · ' + b.nombre);
                pintar(esqueletoHora(), '', {});

                cargar(urlDisp(estado.corte.id, b.id), t, function(d) {
                    const dias = d.dias || [];
                    let selDia = -1,
                        selHora = null;

                    // Conserva la elección si el cliente volvió atrás
                    if (estado.fecha) {
                        selDia = dias.findIndex(function(x) {
                            return x.fecha === estado.fecha && x.disponible;
                        });
                        if (selDia >= 0) selHora = estado.hora;
                    }
                    if (selDia < 0) selDia = dias.findIndex(function(x) {
                        return x.disponible;
                    });

                    if (selDia < 0) {
                        vistaMensaje('calendar-x', 'Sin espacios', b.nombre +
                            ' no tiene horas libres en los próximos días. Prueba con otro barbero.',
                            '<button type="button" class="btn" data-a="volver">Elegir otro barbero</button>', {
                                volver: atras
                            });
                        return;
                    }

                    const resumen = '<div class="resumen-mini">' + avatar(b, 'mini') +
                        '<div class="cuerpo"><strong>' + esc(estado.corte.nombre) + ' · ' + esc(estado.corte
                            .precio) + '</strong>' +
                        '<span class="sub">con ' + esc(b.nombre) + ' · ' + esc(estado.corte.duracion) +
                        ' min</span></div></div>';

                    const html = resumen + calendarioHtml(dias, selDia) +
                        '<div class="horas-cab"><strong>Horas disponibles</strong><span id="horasDia"></span></div>' +
                        '<div class="horas" id="horas"></div>';

                    const pie = '<div class="pie-info" id="pieInfo"></div>' +
                        '<button type="button" class="btn" id="pieBtn" data-a="continuar" disabled>Continuar <i data-lucide="arrow-right"></i></button>';

                    function textoHora(dia, valor) {
                        const h = dia.horas.find(function(x) {
                            return x.valor === valor;
                        });
                        return h ? h.texto : '';
                    }

                    function actualizarPie() {
                        const info = document.getElementById('pieInfo');
                        const btn = document.getElementById('pieBtn');
                        if (selHora) {
                            info.innerHTML = esc(dias[selDia].etiqueta) + ' · ' + esc(textoHora(dias[selDia],
                                selHora));
                            btn.disabled = false;
                        } else {
                            info.innerHTML = 'Elige una hora<small>Toca una de las horas libres</small>';
                            btn.disabled = true;
                        }
                    }

                    function pintarHoras() {
                        const dia = dias[selDia];
                        cuerpo.querySelectorAll('.dia').forEach(function(b2, i) {
                            b2.classList.toggle('sel', i === selDia);
                            b2.setAttribute('aria-pressed', String(i === selDia));
                        });
                        document.getElementById('horasDia').textContent = dia.etiqueta;
                        document.getElementById('horas').innerHTML = dia.horas.map(function(h) {
                            return '<button type="button" class="hora' + (h.valor === selHora ? ' sel' :
                                    '') + '" data-a="hora" data-v="' + esc(h.valor) + '"' +
                                ' aria-pressed="' + (h.valor === selHora) + '">' + esc(h.texto) +
                                '</button>';
                        }).join('');
                        actualizarPie();
                    }

                    pintar(html, pie, {
                        dia: function(el) {
                            const i = +el.dataset.i;
                            if (!dias[i].disponible || i === selDia) return;
                            selDia = i;
                            selHora = null;
                            pintarHoras();
                        },
                        hora: function(el) {
                            selHora = el.dataset.v;
                            cuerpo.querySelectorAll('.hora').forEach(function(h) {
                                const activa = h.dataset.v === selHora;
                                h.classList.toggle('sel', activa);
                                h.setAttribute('aria-pressed', String(activa));
                            });
                            actualizarPie();
                        },
                        continuar: function() {
                            if (!selHora) return;
                            const dia = dias[selDia];
                            estado.fecha = dia.fecha;
                            estado.hora = selHora;
                            estado.cuando = dia.etiqueta + ' · ' + textoHora(dia, selHora);
                            ir(vistaDatos);
                        },
                    });

                    pintarHoras();
                    const sel = cuerpo.querySelector('.dia.sel');
                    if (sel) sel.scrollIntoView({
                        inline: 'center',
                        block: 'nearest'
                    });
                }, vistaHora);
            }

            // --- Primer espacio disponible ---
            function vistaPrimer() {
                const t = ++token;
                cabecera('Primer espacio', estado.corte.nombre + ' · ' + estado.corte.precio);
                pintar(esqueletoPrimer(), '', {});

                cargar(urlPrimer(estado.corte.id), t, function(d) {
                    if (!d.disponible) {
                        vistaMensaje('calendar-x', 'Sin espacios por ahora',
                            'No hay horas libres para este corte en los próximos días. Vuelve a intentarlo más tarde.',
                            '<button type="button" class="btn" data-a="cerrar">Cerrar</button>', {
                                cerrar: cerrarModal
                            });
                        return;
                    }

                    const p = d.principal;
                    const otros = d.otros || [];

                    let html = '<div class="destacado">' +
                        '<span class="rotulo"><i data-lucide="zap"></i> El espacio más cercano</span>' +
                        '<div class="quien">' + avatar(p.barbero, 'mini') + '<strong>' + esc(p.barbero.nombre) +
                        '</strong></div>' +
                        '<div class="cuando">' + esc(p.texto) + '</div></div>' +
                        '<button type="button" class="enlace-btn" data-a="otraHora"><i data-lucide="calendar-days"></i> Elegir otra hora con ' +
                        esc(p.barbero.nombre) + '</button>';

                    if (otros.length) {
                        html += '<p class="sep">Otros barberos con espacio</p>' + otros.map(function(m, i) {
                            return '<button type="button" class="opcion-card" data-a="otro" data-i="' +
                                i + '">' +
                                avatar(m.barbero) +
                                '<span class="cuerpo"><span class="nombre">' + esc(m.barbero.nombre) +
                                '</span>' +
                                '<span class="prox"><i data-lucide="clock"></i> ' + esc(m.texto) +
                                '</span></span>' +
                                '<i data-lucide="chevron-right" class="chev"></i></button>';
                        }).join('');
                    }

                    const pie =
                        '<button type="button" class="btn" data-a="reservar">Reservar este espacio <i data-lucide="arrow-right"></i></button>';

                    function elegirMomento(m) {
                        estado.barbero = m.barbero;
                        estado.fecha = m.fecha;
                        estado.hora = m.hora;
                        estado.cuando = m.texto;
                        ir(vistaDatos);
                    }

                    pintar(html, pie, {
                        reservar: function() {
                            elegirMomento(p);
                        },
                        otro: function(el) {
                            elegirMomento(otros[+el.dataset.i]);
                        },
                        otraHora: function() {
                            estado.barbero = p.barbero;
                            limpiarMomento();
                            ir(vistaHora);
                        },
                    });
                }, vistaPrimer);
            }

            // --- Tus datos y confirmar ---
            function filaResumen(icono, rotulo, valor) {
                return '<div class="fila"><i data-lucide="' + icono + '"></i><div><small>' + esc(rotulo) +
                    '</small><strong>' + esc(valor) + '</strong></div></div>';
            }

            function errorCampo(campo, mensaje) {
                const caja = document.getElementById('c' + campo);
                const msg = document.getElementById('e' + campo);
                if (!caja || !msg) return;
                caja.classList.add('con-error');
                msg.textContent = mensaje;
                msg.hidden = false;
                const inp = document.getElementById('f' + campo);
                if (inp) inp.setAttribute('aria-invalid', 'true');
            }

            function limpiarErrores() {
                ['Nombre', 'Telefono', 'Email', 'Pin'].forEach(function(c) {
                    const caja = document.getElementById('c' + c);
                    const msg = document.getElementById('e' + c);
                    const inp = document.getElementById('f' + c);
                    if (caja) caja.classList.remove('con-error');
                    if (msg) {
                        msg.hidden = true;
                        msg.textContent = '';
                    }
                    if (inp) inp.removeAttribute('aria-invalid');
                });
                const banner = document.getElementById('formError');
                if (banner) banner.hidden = true;
            }

            function bannerError(texto) {
                const banner = document.getElementById('formError');
                if (!banner) return;
                banner.querySelector('span').textContent = texto;
                banner.hidden = false;
                banner.scrollIntoView({
                    block: 'nearest',
                    behavior: reducir ? 'auto' : 'smooth'
                });
            }

            function vistaDatos() {
                ++token;
                enviando = false;
                cabecera('Tus datos', 'Último paso');

                const html =
                    '<div class="resumen-lista">' +
                    filaResumen('scissors', 'Corte', estado.corte.nombre + ' · ' + estado.corte.precio) +
                    filaResumen('user', 'Barbero', estado.barbero.nombre) +
                    filaResumen('calendar-days', 'Cuándo', estado.cuando) +
                    '</div>' +
                    '<div class="campo" id="cNombre">' +
                    '<label for="fNombre">Tu nombre</label>' +
                    '<input id="fNombre" type="text" maxlength="80" autocomplete="name" placeholder="Por ejemplo: Carlos Mora" value="' +
                    esc(estado.nombre) + '">' +
                    '<p class="campo-error" id="eNombre" role="alert" hidden></p>' +
                    '</div>' +
                    '<div class="campo" id="cTelefono">' +
                    '<label for="fTelefono">Tu teléfono</label>' +
                    '<input id="fTelefono" type="tel" inputmode="tel" maxlength="20" autocomplete="tel-national" placeholder="7084 2740" value="' +
                    esc(estado.telefono) + '">' +
                    '<p class="campo-ayuda">Número de Costa Rica, 8 dígitos.</p>' +
                    '<p class="campo-error" id="eTelefono" role="alert" hidden></p>' +
                    '</div>' +
                    '<div class="campo" id="cEmail">' +
                    '<label for="fEmail">Tu correo</label>' +
                    '<input id="fEmail" type="email" inputmode="email" maxlength="120" autocomplete="email" autocapitalize="none" spellcheck="false" placeholder="nombre@gmail.com" value="' +
                    esc(estado.email) + '">' +
                    '<p class="campo-ayuda">Aquí te enviamos tu PIN y los recordatorios. Si no lo ves, revisa la carpeta de spam.</p>' +
                    '<p class="campo-error" id="eEmail" role="alert" hidden></p>' +
                    '</div>' +
                    '<div class="hp" aria-hidden="true"><label for="fSitioWeb">No llenes este campo</label>' +
                    '<input id="fSitioWeb" type="text" name="sitio_web" tabindex="-1" autocomplete="off"></div>' +
                    '<div class="alerta-form" id="formError" role="alert" hidden><i data-lucide="alert-circle"></i><span></span></div>' +
                    '<p class="nota">Al confirmar te daremos un PIN de 4 dígitos para ver, cambiar o cancelar tu cita.</p>';

                const pie = '<button type="button" class="btn" id="btnConfirmar" data-a="confirmar">' +
                    '<i data-lucide="check"></i> Confirmar reserva</button>';

                pintar(html, pie, {
                    confirmar: enviarReserva
                });

                const inNombre = document.getElementById('fNombre');
                const inTel = document.getElementById('fTelefono');
                const inEmail = document.getElementById('fEmail');

                inNombre.addEventListener('input', function() {
                    estado.nombre = inNombre.value;
                });
                inTel.addEventListener('input', function() {
                    estado.telefono = inTel.value;
                });
                inEmail.addEventListener('input', function() {
                    estado.email = inEmail.value;
                });
                [inNombre, inTel, inEmail].forEach(function(inp) {
                    inp.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            enviarReserva();
                        }
                    });
                });

                if (!estado.nombre) inNombre.focus({
                    preventScroll: true
                });
            }

            async function enviarReserva() {
                if (enviando) return;

                const inNombre = document.getElementById('fNombre');
                const inTel = document.getElementById('fTelefono');
                const inEmail = document.getElementById('fEmail');
                const inHp = document.getElementById('fSitioWeb');
                const btn = document.getElementById('btnConfirmar');
                if (!inNombre || !inTel || !inEmail || !btn) return;

                limpiarErrores();

                const nombre = inNombre.value.trim().replace(/\s+/g, ' ');
                const telefono = inTel.value.trim();
                const email = inEmail.value.trim().toLowerCase();
                let hayError = false;

                if (nombre.length < 2) {
                    errorCampo('Nombre', 'Escribe tu nombre.');
                    hayError = true;
                }
                if (telefono.replace(/\D/g, '').length < 8) {
                    errorCampo('Telefono', 'Escribe tu teléfono de 8 dígitos, por ejemplo 7084 2740.');
                    hayError = true;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
                    errorCampo('Email', 'Escribe un correo válido, por ejemplo nombre@gmail.com.');
                    hayError = true;
                }
                if (hayError) {
                    (nombre.length < 2 ? inNombre : (telefono.replace(/\D/g, '').length < 8 ? inTel : inEmail))
                    .focus();
                    return;
                }

                const miToken = token;
                enviando = true;
                btn.disabled = true;
                btn.innerHTML = 'Reservando…';

                let r = null,
                    d = null,
                    caida = false;

                try {
                    r = await fetch(URL_STORE, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            corte: estado.corte.id,
                            barbero: estado.barbero.id,
                            fecha: estado.fecha,
                            hora: estado.hora,
                            nombre: nombre,
                            telefono: telefono,
                            email: email,
                            sitio_web: inHp ? inHp.value : '',
                        }),
                    });
                    try {
                        d = await r.json();
                    } catch (e) {
                        d = null;
                    }
                } catch (e) {
                    caida = true;
                }

                enviando = false;

                // Si el cliente cerró el modal o cambió de pantalla mientras esperaba, no tocamos nada.
                if (miToken !== token || !modal.classList.contains('abierto')) return;

                const btn2 = document.getElementById('btnConfirmar');

                function reactivar() {
                    if (!btn2) return;
                    btn2.disabled = false;
                    btn2.innerHTML = '<i data-lucide="check"></i> Confirmar reserva';
                    if (window.lucide) lucide.createIcons();
                }

                if (caida) {
                    reactivar();
                    bannerError('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
                    return;
                }

                if (r.status === 201 && d && d.ok) {
                    pila = [];
                    cache = {};
                    ir(function() {
                        vistaExito(d);
                    });
                    return;
                }

                if (r.status === 409) {
                    cache = {}; // las horas cambiaron: se vuelven a pedir
                    Swal.fire({
                        icon: 'info',
                        title: 'Esa hora ya no está disponible',
                        text: 'Alguien la tomó hace un momento. Elige otra, por favor.',
                        confirmButtonText: 'Elegir otra hora',
                        confirmButtonColor: '#000000',
                    }).then(function() {
                        if (!modal.classList.contains('abierto')) return;
                        limpiarMomento();
                        atras();
                    });
                    return;
                }

                reactivar();

                if (r.status === 422 && d) {
                    const errs = d.errores || {};
                    let pintado = false;
                    if (errs.nombre) {
                        errorCampo('Nombre', errs.nombre[0]);
                        pintado = true;
                    }
                    if (errs.telefono) {
                        errorCampo('Telefono', errs.telefono[0]);
                        pintado = true;
                    }
                    if (errs.email) {
                        errorCampo('Email', errs.email[0]);
                        pintado = true;
                    }
                    if (pintado) {
                        (errs.nombre ? inNombre : (errs.telefono ? inTel : inEmail)).focus();
                    } else {
                        bannerError(d.mensaje || 'Revisa los datos e inténtalo de nuevo.');
                    }
                    return;
                }

                if (r.status === 419) {
                    bannerError('Tu sesión caducó. Recarga la página e inténtalo de nuevo.');
                    return;
                }

                if (r.status === 429) {
                    bannerError('Demasiados intentos seguidos. Espera un minuto e inténtalo otra vez.');
                    return;
                }

                bannerError((d && d.mensaje) || 'Algo salió mal. Inténtalo de nuevo en unos segundos.');
            }

            // --- Reserva confirmada (con el PIN) ---
            function copiarTexto(texto, ok) {
                function respaldo() {
                    const ta = document.createElement('textarea');
                    ta.value = texto;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    let listo = false;
                    try {
                        listo = document.execCommand('copy');
                    } catch (e) {
                        listo = false;
                    }
                    document.body.removeChild(ta);
                    if (listo) ok();
                }

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(texto).then(ok, respaldo);
                } else {
                    respaldo();
                }
            }

            function vistaExito(d) {
                ++token;
                estado.exito = true;
                pinCopiado = false;
                cabecera('Reserva confirmada');


                const c = d.cita;
                const pin = String(d.pin || '');

                const html =
                    '<div class="exito-cab">' +
                    '<div class="exito-icono"><i data-lucide="check"></i></div>' +
                    '<h3>¡Listo, ' + esc(c.cliente.nombre.split(' ')[0]) + '!</h3>' +
                    '<p>Tu cita quedó reservada.</p>' +
                    '</div>' +
                    '<div class="pin-caja">' +
                    '<span class="rotulo"><i data-lucide="key-round"></i> Tu PIN</span>' +
                    '<div class="pin-digitos" aria-label="Tu PIN es ' + esc(pin.split('').join(' ')) + '">' +
                    pin.split('').map(function(n) {
                        return '<span>' + esc(n) + '</span>';
                    }).join('') +
                    '</div>' +
                    '<p class="pin-aviso">Guárdalo: lo necesitas para ver, cambiar o cancelar tu cita. Si lo olvidas, te enviamos uno nuevo a tu correo.</p>' +
                    '<button type="button" class="btn btn-blanco" data-a="copiar"><i data-lucide="copy"></i> Copiar PIN</button>' +
                    '</div>' +
                    '<div class="resumen-lista">' +
                    filaResumen('scissors', 'Corte', c.corte.nombre + ' · ' + c.corte.precio) +
                    filaResumen('user', 'Barbero', c.barbero.nombre) +
                    filaResumen('calendar-days', 'Cuándo', c.cuando) +
                    filaResumen('phone', 'Tu teléfono', c.cliente.telefono) +

                    '</div>' +

                    '<p class="nota">También puedes tomar una captura de pantalla para no olvidarlo.</p>';
                const pie = '<button type="button" class="btn" data-a="listo">Listo</button>';

                pintar(html, pie, {
                    copiar: function(el) {
                        copiarTexto(pin, function() {
                            pinCopiado = true;
                            el.innerHTML = '<i data-lucide="check"></i> PIN copiado';
                            if (window.lucide) lucide.createIcons();
                        });
                    },
                    listo: intentarCerrar,
                });
            }

            // =====================================================================
            //  PANTALLAS DE "VER MI CITA"
            // =====================================================================

            function alerta(icono, titulo, texto) {
                return Swal.fire({
                    icon: icono,
                    title: titulo,
                    text: texto,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#000000',
                });
            }

            function avisoOk(texto) {
                if (!window.Swal) return;
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: texto,
                    showConfirmButton: false,
                    timer: 2800,
                    timerProgressBar: true,
                });
            }

            // Vuelve a pedir la lista de citas al servidor (si la verificación sigue vigente).
            async function recargarLista() {
                const miToken = ++token;
                cabecera('Tus citas');
                pintar(esqueletoLista(2), '', {});

                try {
                    const d = await pedir(URL_MC);
                    if (miToken !== token || !modal.classList.contains('abierto')) return;
                    mc.citas = d.citas || [];
                    pila = [];
                    ir(vistaMcLista);
                } catch (err) {
                    if (miToken !== token || !modal.classList.contains('abierto')) return;
                    vistaError(err, recargarLista);
                }
            }

            // --- Teléfono + PIN ---
            function vistaMcAcceso() {
                ++token;
                enviando = false;
                mc = nuevoMc();
                cabecera('Ver mi cita', 'Escribe tu teléfono y tu PIN');

                const html =
                    '<div class="campo" id="cTelefono">' +
                    '<label for="fTelefono">Tu teléfono</label>' +
                    '<input id="fTelefono" type="tel" inputmode="tel" maxlength="20" autocomplete="tel-national" placeholder="7084 2740">' +
                    '<p class="campo-error" id="eTelefono" role="alert" hidden></p>' +
                    '</div>' +
                    '<div class="campo" id="cPin">' +
                    '<label for="fPin">Tu PIN de 4 dígitos</label>' +
                    '<input id="fPin" class="pin" type="password" inputmode="numeric" maxlength="4" autocomplete="off" placeholder="••••">' +
                    '<p class="campo-ayuda">Es el PIN que te dimos al reservar.</p>' +
                    '<p class="campo-error" id="ePin" role="alert" hidden></p>' +
                    '</div>' +
                    '<div class="hp" aria-hidden="true"><label for="fSitioWeb">No llenes este campo</label>' +
                    '<input id="fSitioWeb" type="text" name="sitio_web" tabindex="-1" autocomplete="off"></div>' +
                    '<div class="alerta-form" id="formError" role="alert" hidden><i data-lucide="alert-circle"></i><span></span></div>' +
                    '<button type="button" class="enlace-btn" id="btnOlvide" data-a="olvide"><i data-lucide="message-circle"></i> Olvidé mi código</button>' +
                    '<p class="nota">Escribe tu teléfono y toca el botón: te enviamos un código nuevo a tu correo.</p>';

                const pie = '<button type="button" class="btn" id="btnVerificar" data-a="verificar">' +
                    '<i data-lucide="search"></i> Ver mi cita</button>';

                pintar(html, pie, {
                    verificar: enviarAcceso,
                    olvide: enviarOlvide
                });

                const inTel = document.getElementById('fTelefono');
                const inPin = document.getElementById('fPin');

                // El PIN solo acepta números
                inPin.addEventListener('input', function() {
                    inPin.value = inPin.value.replace(/\D/g, '').slice(0, 4);
                });
                [inTel, inPin].forEach(function(inp) {
                    inp.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            enviarAcceso();
                        }
                    });
                });

                inTel.focus({
                    preventScroll: true
                });
            }

            async function enviarAcceso() {
                if (enviando) return;

                const inTel = document.getElementById('fTelefono');
                const inPin = document.getElementById('fPin');
                const inHp = document.getElementById('fSitioWeb');
                const btn = document.getElementById('btnVerificar');
                if (!inTel || !inPin || !btn) return;

                limpiarErrores();

                const telefono = inTel.value.trim();
                const pin = inPin.value.trim();
                let hayError = false;

                if (telefono.replace(/\D/g, '').length < 8) {
                    errorCampo('Telefono', 'Escribe tu teléfono de 8 dígitos, por ejemplo 7084 2740.');
                    hayError = true;
                }
                if (!/^\d{4}$/.test(pin)) {
                    errorCampo('Pin', 'Tu PIN tiene 4 dígitos.');
                    hayError = true;
                }
                if (hayError) {
                    (telefono.replace(/\D/g, '').length < 8 ? inTel : inPin).focus();
                    return;
                }

                const miToken = token;
                enviando = true;
                btn.disabled = true;
                btn.innerHTML = 'Buscando…';

                const res = await enviarJson(URL_MC_VERIFICAR, {
                    telefono: telefono,
                    pin: pin,
                    sitio_web: inHp ? inHp.value : '',
                });

                enviando = false;
                if (miToken !== token || !modal.classList.contains('abierto')) return;

                const btn2 = document.getElementById('btnVerificar');

                function reactivar() {
                    if (!btn2) return;
                    btn2.disabled = false;
                    btn2.innerHTML = '<i data-lucide="search"></i> Ver mi cita';
                    if (window.lucide) lucide.createIcons();
                }

                if (res.caida) {
                    reactivar();
                    bannerError('No pudimos conectarnos. Revisa tu internet e inténtalo de nuevo.');
                    return;
                }

                const d = res.d;

                if (res.status === 200 && d && d.ok) {
                    mc.verificado = true;
                    mc.citas = d.citas || [];
                    pila = [];
                    ir(vistaMcLista);
                    return;
                }

                reactivar();
                inPin.value = '';

                if (res.status === 422 && d) {
                    const errs = d.errores || {};
                    let pintado = false;
                    if (errs.telefono) {
                        errorCampo('Telefono', errs.telefono[0]);
                        pintado = true;
                    }
                    if (errs.pin) {
                        errorCampo('Pin', errs.pin[0]);
                        pintado = true;
                    }
                    if (!pintado) bannerError(d.mensaje || 'Revisa los datos e inténtalo de nuevo.');
                    (errs.telefono ? inTel : inPin).focus();
                    return;
                }

                if (res.status === 419) {
                    bannerError('Tu sesión caducó. Recarga la página e inténtalo de nuevo.');
                    return;
                }

                if (res.status === 429) {
                    bannerError((d && d.mensaje) ||
                        'Demasiados intentos seguidos. Espera un momento e inténtalo otra vez.');
                    return;
                }

                bannerError((d && d.mensaje) || 'Algo salió mal. Inténtalo de nuevo en unos segundos.');
            }


            // --- "Olvidé mi código" ---
            async function enviarOlvide() {
                if (enviando) return;

                const inTel = document.getElementById('fTelefono');
                const inHp = document.getElementById('fSitioWeb');
                const btn = document.getElementById('btnOlvide');
                if (!inTel || !btn) return;

                limpiarErrores();

                const telefono = inTel.value.trim();

                if (telefono.replace(/\D/g, '').length < 8) {
                    await alerta('info', 'Escribe tu teléfono',
                        'Primero escribe tu número de teléfono y después toca «Olvidé mi código». Así te enviamos uno nuevo a tu correo.'
                    );
                    if (modal.classList.contains('abierto')) inTel.focus();
                    return;
                }

                const miToken = token;
                enviando = true;
                btn.disabled = true;
                btn.innerHTML = 'Enviando…';

                const res = await enviarJson(URL_MC_OLVIDE, {
                    telefono: telefono,
                    sitio_web: inHp ? inHp.value : '',
                });

                enviando = false;
                if (miToken !== token || !modal.classList.contains('abierto')) return;

                const btn2 = document.getElementById('btnOlvide');
                if (btn2) {
                    btn2.disabled = false;
                    btn2.innerHTML = '<i data-lucide="message-circle"></i> Olvidé mi código';
                    if (window.lucide) lucide.createIcons();
                }

                if (res.caida) {
                    alerta('error', 'No pudimos conectarnos', 'Revisa tu internet e inténtalo de nuevo.');
                    return;
                }

                const d = res.d;

                if (res.status === 200 && d && d.ok) {
                    alerta('success', 'Revisa tu correo', d.mensaje).then(function() {
                        const inPin = document.getElementById('fPin');
                        if (modal.classList.contains('abierto') && inPin) inPin.focus();
                    });
                    return;
                }

                if (res.status === 422 && d && d.errores && d.errores.telefono) {
                    errorCampo('Telefono', d.errores.telefono[0]);
                    inTel.focus();
                    return;
                }

                if (res.status === 419) {
                    alerta('error', 'Sesión caducada', 'Recarga la página e inténtalo de nuevo.');
                    return;
                }

                if (res.status === 429) {
                    alerta('error', 'Demasiadas solicitudes', (d && d.mensaje) ||
                        'Espera un momento e inténtalo otra vez.');
                    return;
                }

                alerta('info', 'No se pudo', (d && d.mensaje) ||
                    'Algo salió mal. Inténtalo de nuevo en unos segundos.');
            }

            // --- Lista de citas ---
            function tarjetaCita(c, i) {
                const filas =
                    filaResumen('calendar-days', 'Cuándo', c.cuando) +
                    filaResumen('user', 'Barbero', c.barbero.nombre) +
                    filaResumen('clock', 'Duración', c.corte.duracion + ' min');

                const acciones = c.puede_modificar ?
                    '<div class="cita-botones">' +
                    '<button type="button" class="btn btn-claro" data-a="cambiar" data-i="' + i +
                    '"><i data-lucide="calendar-clock"></i> Cambiar</button>' +
                    '<button type="button" class="btn btn-cancelar" data-a="cancelar" data-i="' + i +
                    '"><i data-lucide="x"></i> Cancelar</button>' +
                    '</div>' :
                    '<p class="aviso-tarde"><i data-lucide="clock"></i><span>Tu cita es en menos de 1 hora. Para hacer cambios, comunícate con el barbero.</span></p>';


                return '<article class="cita-card">' +
                    '<div class="cita-cab"><h3>' + esc(c.corte.nombre) + '</h3>' +
                    (c.corte.precio ? '<span class="cita-precio">' + esc(c.corte.precio) + '</span>' : '') + '</div>' +
                    '<div class="resumen-lista">' + filas + '</div>' +
                    acciones +
                    '</article>';
            }

            function vistaMcLista() {
                ++token;
                enviando = false;
                const n = mc.citas.length;
                cabecera('Tus citas', n === 0 ? 'Sin citas pendientes' : (n === 1 ? '1 cita pendiente' : n +
                    ' citas pendientes'));

                if (!n) {
                    vistaMensaje('calendar-x', 'No tienes citas pendientes', 'Cuando reserves una, la verás aquí.',
                        '<button type="button" class="btn btn-claro" data-a="cerrar">Cerrar</button>' +
                        '<button type="button" class="btn" data-a="reservar"><i data-lucide="calendar-plus"></i> Reservar un corte</button>', {
                            cerrar: cerrarModal,
                            reservar: function() {
                                cerrarModal();
                                const sec = document.getElementById('cortes');
                                if (sec) sec.scrollIntoView({
                                    behavior: reducir ? 'auto' : 'smooth'
                                });
                            },
                        });
                    return;
                }

                pintar(mc.citas.map(tarjetaCita).join(''),
                    '<button type="button" class="btn" data-a="cerrar">Listo</button>', {
                        cerrar: cerrarModal,
                        cambiar: function(el) {
                            const c = mc.citas[+el.dataset.i];
                            if (!c) return;
                            mc.cita = c;
                            mc.barbero = null;
                            mc.fecha = null;
                            mc.hora = null;
                            ir(vistaMcBarbero);
                        },
                        cancelar: function(el) {
                            cancelarCita(+el.dataset.i);
                        },
                    });
            }

            // Procesa la respuesta de cancelar/cambiar. Devuelve true si salió bien.
            function procesarAccion(res, textoOk) {
                if (!modal.classList.contains('abierto')) return true;

                if (res.caida) {
                    alerta('error', 'No pudimos conectarnos', 'Revisa tu internet e inténtalo de nuevo.');
                    return false;
                }

                const d = res.d;
                if (d && d.citas) mc.citas = d.citas;

                if (res.status === 200 && d && d.ok) {
                    cache = {};
                    avisoOk(textoOk);
                    pila = [];
                    ir(vistaMcLista);
                    return true;
                }

                if (res.status === 401) {
                    alerta('info', 'Tu sesión terminó', 'Escribe tu teléfono y tu PIN otra vez.').then(function() {
                        if (!modal.classList.contains('abierto')) return;
                        pila = [];
                        mc = nuevoMc();
                        ir(vistaMcAcceso);
                    });
                    return true;
                }

                if (res.status === 419) {
                    alerta('error', 'Sesión caducada', 'Recarga la página e inténtalo de nuevo.');
                    return false;
                }

                if (res.status === 429) {
                    alerta('error', 'Demasiados intentos', 'Espera un momento e inténtalo otra vez.');
                    return false;
                }

                const codigo = d && d.codigo;
                const texto = (d && d.mensaje) || 'Algo salió mal. Inténtalo de nuevo en unos segundos.';

                // Casos en los que la cita cambió: volvemos a la lista al día.
                if (codigo === 'muy-tarde' || codigo === 'no-existe' || codigo === 'no-disponible') {
                    cache = {};
                    alerta('info', 'No se pudo', texto).then(function() {
                        if (!modal.classList.contains('abierto')) return;
                        if (d && d.citas) {
                            pila = [];
                            ir(vistaMcLista);
                        } else recargarLista();
                    });
                    return true;
                }

                alerta('info', 'No se pudo', texto);
                return false;
            }

            async function cancelarCita(i) {
                const c = mc.citas[i];
                if (!c || enviando || !window.Swal) return;

                const r = await Swal.fire({
                    icon: 'warning',
                    title: '¿Cancelar tu cita?',
                    text: c.corte.nombre + ' con ' + c.barbero.nombre + ' · ' + c.cuando,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No, mantenerla',
                    confirmButtonColor: '#000000',
                    cancelButtonColor: '#525252',
                    reverseButtons: true,
                    focusCancel: true,
                });
                if (!r.isConfirmed || !modal.classList.contains('abierto')) return;

                enviando = true;
                const res = await enviarJson(urlMc(c.id, 'cancelar'), {});
                enviando = false;

                procesarAccion(res, 'Tu cita fue cancelada.');
            }

            // --- Cambiar: elegir barbero ---
            function vistaMcBarbero() {
                const t = ++token;
                const c = mc.cita;
                cabecera('Cambiar tu cita', c.corte.nombre + ' · ' + c.cuando);
                pintar(esqueletoLista(2), '', {});

                cargar(urlMc(c.id, 'barberos'), t, function(d) {
                    const lista = d.barberos || [];

                    // Con un solo barbero no hay nada que elegir: pasamos directo a día y hora.
                    if (lista.length <= 1) {
                        mc.barbero = lista[0] || {
                            id: c.barbero.id,
                            nombre: c.barbero.nombre,
                            foto: null
                        };
                        pila.pop();
                        ir(vistaMcHora);
                        return;
                    }

                    const html = '<p class="sep" style="margin-top:0">¿Con quién quieres tu cita?</p>' +
                        lista.map(function(b, i) {
                            return '<button type="button" class="opcion-card" data-a="barbero" data-i="' +
                                i + '">' +
                                avatar(b) +
                                '<span class="cuerpo"><span class="nombre">' + esc(b.nombre) + '</span>' +
                                (b.actual ? '<span class="chip">Tu barbero actual</span>' : '') +
                                '</span>' +
                                '<i data-lucide="chevron-right" class="chev"></i></button>';
                        }).join('');

                    pintar(html, '', {
                        barbero: function(el) {
                            mc.barbero = lista[+el.dataset.i];
                            mc.fecha = null;
                            mc.hora = null;
                            ir(vistaMcHora);
                        },
                    });
                }, vistaMcBarbero);
            }

            // --- Cambiar: elegir día y hora ---
            function vistaMcHora() {
                const t = ++token;
                const c = mc.cita;
                const b = mc.barbero;
                cabecera('Nuevo día y hora', c.corte.nombre + ' · ' + b.nombre);
                pintar(esqueletoHora(), '', {});

                cargar(urlMc(c.id, 'disponibilidad') + '?barbero=' + encodeURIComponent(b.id), t, function(d) {
                    const dias = d.dias || [];
                    const bd = d.barbero || b;
                    mc.barbero = bd;
                    let selDia = -1,
                        selHora = null;

                    if (mc.fecha) {
                        selDia = dias.findIndex(function(x) {
                            return x.fecha === mc.fecha && x.disponible;
                        });
                        if (selDia >= 0) selHora = mc.hora;
                    }
                    if (selDia < 0) selDia = dias.findIndex(function(x) {
                        return x.disponible;
                    });

                    if (selDia < 0) {
                        vistaMensaje('calendar-x', 'Sin espacios', bd.nombre +
                            ' no tiene otras horas libres en los próximos días.',
                            '<button type="button" class="btn" data-a="volver">Volver</button>', {
                                volver: atras
                            });
                        return;
                    }

                    const resumen = '<div class="resumen-mini">' + avatar(bd, 'mini') +
                        '<div class="cuerpo"><strong>Tu cita actual: ' + esc(c.cuando) + '</strong>' +
                        '<span class="sub">' + esc(c.corte.nombre) + ' · ' + esc(c.corte.duracion) +
                        ' min · con ' + esc(bd.nombre) + '</span></div></div>';

                    const html = resumen + calendarioHtml(dias, selDia) +
                        '<div class="horas-cab"><strong>Horas disponibles</strong><span id="horasDia"></span></div>' +
                        '<div class="horas" id="horas"></div>';
                    const pie = '<div class="pie-info" id="pieInfo"></div>' +
                        '<button type="button" class="btn" id="pieBtn" data-a="confirmar" disabled>Confirmar cambio <i data-lucide="check"></i></button>';

                    function textoHora(dia, valor) {
                        const h = dia.horas.find(function(x) {
                            return x.valor === valor;
                        });
                        return h ? h.texto : '';
                    }

                    function actualizarPie() {
                        const info = document.getElementById('pieInfo');
                        const btn = document.getElementById('pieBtn');
                        if (!info || !btn) return;
                        if (selHora) {
                            info.innerHTML = esc(dias[selDia].etiqueta) + ' · ' + esc(textoHora(dias[selDia],
                                selHora));
                            btn.disabled = false;
                        } else {
                            info.innerHTML = 'Elige una hora<small>Toca una de las horas libres</small>';
                            btn.disabled = true;
                        }
                    }

                    function pintarHoras() {
                        const dia = dias[selDia];
                        cuerpo.querySelectorAll('.dia').forEach(function(b2, i) {
                            b2.classList.toggle('sel', i === selDia);
                            b2.setAttribute('aria-pressed', String(i === selDia));
                        });
                        document.getElementById('horasDia').textContent = dia.etiqueta;
                        document.getElementById('horas').innerHTML = dia.horas.map(function(h) {
                            return '<button type="button" class="hora' + (h.valor === selHora ? ' sel' :
                                    '') + '" data-a="hora" data-v="' + esc(h.valor) + '"' +
                                ' aria-pressed="' + (h.valor === selHora) + '">' + esc(h.texto) +
                                '</button>';
                        }).join('');
                        actualizarPie();
                    }

                    async function confirmarCambio() {
                        if (enviando || !selHora) return;
                        const dia = dias[selDia];
                        const btn = document.getElementById('pieBtn');

                        mc.fecha = dia.fecha;
                        mc.hora = selHora;

                        enviando = true;
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = 'Cambiando…';
                        }

                        const res = await enviarJson(urlMc(c.id, 'cambiar'), {
                            barbero: bd.id,
                            fecha: dia.fecha,
                            hora: selHora,
                        });

                        enviando = false;
                        if (!modal.classList.contains('abierto')) return;

                        // La hora se ocupó mientras elegía: se recarga el calendario.
                        if (res.status === 409) {
                            cache = {};
                            mc.fecha = null;
                            mc.hora = null;
                            alerta('info', 'Esa hora ya no está disponible',
                                'Alguien la tomó hace un momento. Elige otra, por favor.').then(
                                function() {
                                    if (modal.classList.contains('abierto')) vistaMcHora();
                                });
                            return;
                        }

                        const bien = procesarAccion(res, 'Tu cita fue cambiada.');

                        if (!bien) {
                            const btn2 = document.getElementById('pieBtn');
                            if (btn2) {
                                btn2.innerHTML = 'Confirmar cambio <i data-lucide="check"></i>';
                                btn2.disabled = !selHora;
                                if (window.lucide) lucide.createIcons();
                            }
                        }
                    }

                    pintar(html, pie, {
                        dia: function(el) {
                            const i = +el.dataset.i;
                            if (!dias[i].disponible || i === selDia) return;
                            selDia = i;
                            selHora = null;
                            pintarHoras();
                        },
                        hora: function(el) {
                            selHora = el.dataset.v;
                            cuerpo.querySelectorAll('.hora').forEach(function(h) {
                                const activa = h.dataset.v === selHora;
                                h.classList.toggle('sel', activa);
                                h.setAttribute('aria-pressed', String(activa));
                            });
                            actualizarPie();
                        },
                        confirmar: confirmarCambio,
                        volver: atras,
                    });

                    pintarHoras();
                    const sel = cuerpo.querySelector('.dia.sel');
                    if (sel) sel.scrollIntoView({
                        inline: 'center',
                        block: 'nearest'
                    });
                }, vistaMcHora);
            }

            // ----- puntos de entrada -----
            function abrirReserva(corteId, nombre) {
                const c = CORTES.find(function(x) {
                    return String(x.id) === String(corteId);
                });
                if (!c) return;
                estado = nuevoEstado(c);
                mc = nuevoMc();
                pila = [];
                cache = {};
                abrirModal();
                ir(vistaBarberos);
            }

            function abrirPrimerEspacio() {
                estado = nuevoEstado(null);
                mc = nuevoMc();
                pila = [];
                cache = {};
                abrirModal();
                ir(vistaElegirCorte);
            }

            function abrirMiCita() {
                if (modal.classList.contains('abierto')) return;
                estado = nuevoEstado(null);
                mc = nuevoMc();
                pila = [];
                cache = {};
                abrirModal();
                ir(vistaMcAcceso);
            }

            document.querySelectorAll('.js-corte').forEach(function(tarjeta) {
                tarjeta.addEventListener('click', function() {
                    abrirReserva(tarjeta.dataset.id, tarjeta.dataset.nombre);
                });
            });

            document.getElementById('btnPrimerEspacio').addEventListener('click', abrirPrimerEspacio);

            document.querySelectorAll('.js-mi-cita-btn').forEach(function(b) {
                b.addEventListener('click', abrirMiCita);
            });

            document.querySelectorAll('a.js-mi-cita').forEach(function(a) {
                a.addEventListener('click', function() {
                    setTimeout(abrirMiCita, 450);
                });
            });

            // ----- Si llegó desde su enlace privado -----
            if (ABRIR_MI_CITA) {
                estado = nuevoEstado(null);
                mc = nuevoMc();
                mc.verificado = true; // así se cierra la verificación al cerrar el modal
                pila = [];
                cache = {};
                abrirModal();
                recargarLista();
            } else if (ENLACE_INVALIDO && window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: 'Este enlace ya no sirve',
                    text: 'La cita ya terminó o fue cancelada. Puedes reservar otra, o usar «Ver mi cita» con tu teléfono y PIN.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#000000',
                });
            }
        })();
    </script>
</body>

</html>
