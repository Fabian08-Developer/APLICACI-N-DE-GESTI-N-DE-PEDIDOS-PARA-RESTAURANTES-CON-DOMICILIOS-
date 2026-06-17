<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $empresa->nombre }} – Pide a Domicilio</title>
    <meta name="description" content="{{ $apariencia['descripcion'] }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Leaflet CSS (para el mapa) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <!-- Estilos dinámicos de la marca -->
    <style>
        :root {
            --cp: {{ $apariencia['color_primario'] }};
            --cs: {{ $apariencia['color_secundario'] }};
            --cp-rgb: {{ implode(',', sscanf(ltrim($apariencia['color_primario'], '#'), '%02x%02x%02x') ?? [230, 57, 70]) }};
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #1a1612; color: #e2e8f0; overflow-x: hidden; }

        /* ── HERO ───────────────────────────────────────────────────── */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2.5rem 1.5rem;
        }
        .hero-bg {
            position: absolute; inset: 0; z-index: 0;
            @if($apariencia['banner_url'])
            background: url('{{ asset('storage/'.$apariencia['banner_url']) }}') center/cover no-repeat;
            @else
            background: linear-gradient(135deg, var(--cs) 0%, #1a1612 60%);
            @endif
        }
        .hero-bg::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(15,15,19,.55) 0%, rgba(15,15,19,.92) 100%);
        }
        .hero-content { position: relative; z-index: 1; max-width: 720px; width: 100%; }
        .hero-logo {
            width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
            border: 4px solid var(--cp); margin: 0 auto 1.5rem; display: block;
            box-shadow: 0 0 40px rgba(var(--cp-rgb),.45);
        }
        .hero-logo-placeholder {
            width: 110px; height: 110px; border-radius: 50%;
            background: linear-gradient(135deg, var(--cp), var(--cs));
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 900; color: #fff;
            margin: 0 auto 1.5rem; border: 4px solid rgba(255,255,255,.15);
            box-shadow: 0 0 40px rgba(var(--cp-rgb),.45);
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 900;
            line-height: 1.1; letter-spacing: -0.03em;
            background: linear-gradient(135deg, #fff 40%, var(--cp));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.15rem; color: rgba(255,255,255,.72); max-width: 520px;
            margin: 0 auto 1.5rem; line-height: 1.6;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
            backdrop-filter: blur(12px); padding: .5rem 1.2rem; border-radius: 999px;
            font-size: .85rem; color: rgba(255,255,255,.7); margin-bottom: 1.5rem;
        }
        .hero-badge span { color: var(--cp); font-weight: 600; }
        .scroll-hint {
            position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: .4rem;
            color: rgba(255,255,255,.4); font-size: .78rem; animation: bounce 2s infinite;
        }
        @keyframes bounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(8px)} }

        /* ── SEARCH BAR ────────────────────────────────────────────── */
        .search-container {
            position: relative;
            max-width: 480px;
            width: 100%;
            margin: 1.5rem auto 0;
            z-index: 10;
        }
        .search-wrapper {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 0.25rem 0.5rem 0.25rem 1.2rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .search-wrapper:focus-within {
            border-color: var(--cp);
            box-shadow: 0 0 20px rgba(var(--cp-rgb), 0.25);
        }
        .search-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 0.95rem;
            height: 44px;
        }
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.45);
        }
        .search-btn {
            background: var(--cp);
            border: none;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
        }
        .search-btn:hover { opacity: 0.9; }
        .search-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0; right: 0;
            background: #221c16;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            max-height: 240px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            z-index: 99;
        }
        .search-item {
            padding: 0.85rem 1.2rem;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.9rem;
            cursor: pointer;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.2s;
        }
        .search-item:last-child { border-bottom: none; }
        .search-item:hover {
            background: rgba(var(--cp-rgb), 0.15);
            color: #fff;
        }

        /* ── MODAL COBERTURA ────────────────────────────────────────── */
        .modal-cobertura {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .modal-cobertura.active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-card {
            background: #1f1914;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2.2rem;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            color: #e2e8f0;
            text-align: center;
        }
        .modal-cobertura.active .modal-card {
            transform: translateY(0);
        }
        .modal-close {
            position: absolute;
            top: 1.25rem; right: 1.25rem;
            background: rgba(255,255,255,0.06);
            border: none;
            width: 32px; height: 32px;
            border-radius: 50%;
            color: rgba(255,255,255,0.6);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .modal-close:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }
        .modal-icon {
            font-size: 3rem;
            color: var(--cp);
            margin-bottom: 1.25rem;
        }
        .modal-titulo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.75rem;
        }
        .modal-barrio {
            color: var(--cp);
            font-weight: 700;
        }
        .modal-info-box {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 1.25rem;
            margin: 1.5rem 0;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .modal-info-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }
        .modal-info-row span {
            color: rgba(255,255,255,0.5);
        }
        .modal-info-row strong {
            color: #fff;
            font-weight: 600;
        }

        /* ── SECCIÓN SUCURSALES ────────────────────────────────────── */
        .section { padding: 5rem 1.5rem; max-width: 1200px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-header h2 {
            font-size: 2.2rem; font-weight: 800; letter-spacing: -.02em;
            color: #fff; margin-bottom: .6rem;
        }
        .section-header p { color: rgba(255,255,255,.5); font-size: 1.05rem; }
        .pill-label {
            display: inline-block; background: rgba(var(--cp-rgb),.15);
            color: var(--cp); border: 1px solid rgba(var(--cp-rgb),.3);
            padding: .25rem .85rem; border-radius: 999px; font-size: .78rem;
            font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
            margin-bottom: .85rem;
        }

        .sucursales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.75rem;
        }
        .card-sucursal {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            backdrop-filter: blur(12px);
            border-radius: 24px; padding: 1.85rem;
            transition: transform .25s, border-color .25s, box-shadow .25s;
            position: relative; overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-sucursal::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(var(--cp-rgb),.06) 0%, transparent 60%);
            opacity: 0; transition: opacity .25s;
            pointer-events: none;
        }
        .card-sucursal:hover { transform: translateY(-4px); border-color: rgba(var(--cp-rgb),.4); box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .card-sucursal:hover::before { opacity: 1; }
        .card-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; }
        .card-nombre { font-size: 1.25rem; font-weight: 700; color: #fff; }
        .badge-estado {
            font-size: .72rem; font-weight: 600; padding: .35rem .85rem; border-radius: 999px;
        }
        .badge-abierta { background: rgba(52,211,153,.15); color: #34d399; border: 1px solid rgba(52,211,153,.25); }
        .badge-cerrada { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
        .card-info { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1.5rem; }
        .card-info-row { display: flex; align-items: flex-start; gap: .6rem; color: rgba(255,255,255,.6); font-size: .88rem; }
        .card-info-row svg { flex-shrink: 0; margin-top: 2px; }
        .card-info-row span { color: rgba(255,255,255,.45); min-width: 75px; }
        .card-stat {
            display: flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.05); border-radius: 10px; padding: .6rem 1rem;
            margin-bottom: 1.5rem; font-size: .84rem; color: rgba(255,255,255,.55);
        }
        .card-stat strong { color: var(--cp); }
        
        .card-actions {
            display: flex;
            gap: 0.75rem;
        }
        .btn-pedir {
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            flex: 1; padding: .85rem 1rem; border-radius: 12px; border: none;
            background: linear-gradient(135deg, var(--cp), color-mix(in srgb, var(--cp) 70%, var(--cs)));
            color: #fff; font-size: 0.9rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 20px rgba(var(--cp-rgb),.35);
            width: 100%;
        }
        .btn-pedir:hover { opacity: .9; transform: scale(1.02); }
        .btn-pedir.disabled {
            background: rgba(255,255,255,.08); color: rgba(255,255,255,.35);
            box-shadow: none; cursor: not-allowed;
            flex: 1;
        }
        .btn-pedir.disabled:hover { transform: none; opacity: 1; }

        .btn-reservar {
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            flex: 1; padding: .85rem 1rem; border-radius: 12px;
            border: 1px solid rgba(var(--cp-rgb), 0.5);
            background: transparent;
            color: #fff; font-size: 0.9rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: background 0.2s, border-color 0.2s, transform 0.15s;
        }
        .btn-reservar:hover {
            background: rgba(var(--cp-rgb), 0.1);
            border-color: var(--cp);
            transform: scale(1.02);
        }

        /* ── CÓMO FUNCIONA ────────────────────────────────────────── */
        .como-funciona-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.75rem;
        }
        .card-paso {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2.2rem 1.75rem;
            text-align: center;
            transition: transform 0.2s;
        }
        .card-paso:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.04);
        }
        .paso-numero {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: rgba(var(--cp-rgb), 0.12);
            color: var(--cp);
            font-size: 1.25rem;
            font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            border: 1px solid rgba(var(--cp-rgb), 0.2);
        }
        .paso-titulo {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.6rem;
        }
        .paso-desc {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.5;
        }

        /* ── PRODUCTOS DESTACADOS ─────────────────────────────────────── */
        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.75rem;
        }
        .card-producto {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.25s, border-color 0.25s, box-shadow 0.25s;
            display: flex;
            flex-direction: column;
        }
        .card-producto:hover {
            transform: translateY(-4px);
            border-color: rgba(var(--cp-rgb), 0.3);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        .producto-img-container {
            position: relative;
            height: 180px;
            background: #2a221a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .producto-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .producto-img-placeholder {
            font-size: 3rem;
        }
        .tag-sede {
            position: absolute;
            top: 0.75rem; left: 0.75rem;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(6px);
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .tag-oferta {
            position: absolute;
            top: 0.75rem; right: 0.75rem;
            background: var(--cp);
            padding: 0.3rem 0.65rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #fff;
            box-shadow: 0 2px 8px rgba(var(--cp-rgb), 0.4);
        }
        .producto-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .producto-nombre {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.4rem;
        }
        .producto-desc {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.55);
            line-height: 1.4;
            margin-bottom: 1.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
        }
        .producto-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .producto-precios {
            display: flex;
            flex-direction: column;
        }
        .producto-precio-actual {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--cp);
        }
        .producto-precio-antes {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            text-decoration: line-through;
            margin-bottom: 0.1rem;
        }
        .btn-ver-menu {
            font-size: 0.78rem;
            color: #fff;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-ver-menu:hover {
            background: rgba(var(--cp-rgb), 0.15);
            border-color: var(--cp);
        }

        /* ── CALIFICACIONES / SOCIAL PROOF ────────────────────────────── */
        .calificaciones-section {
            border-top: 1px solid rgba(255,255,255,.05);
            padding: 5rem 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .calificaciones-wrapper {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
            align-items: start;
        }
        .calificaciones-resumen {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .calificaciones-score {
            font-size: 4rem;
            font-weight: 900;
            color: #fff;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .calificaciones-estrellas {
            color: #fbbf24;
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }
        .calificaciones-texto {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }
        .reviews-lista {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .card-review {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .review-autor {
            font-weight: 600;
            color: #fff;
            font-size: 0.9rem;
        }
        .review-stars {
            color: #fbbf24;
            font-size: 0.85rem;
        }
        .review-comment {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.5;
            font-style: italic;
        }

        /* ── MAPA ──────────────────────────────────────────────────── */
        .map-section { padding: 0 1.5rem 5rem; }
        .map-wrapper { max-width: 1200px; margin: 0 auto; }
        .map-wrapper h2 { font-size: 1.7rem; font-weight: 800; color: #fff; margin-bottom: 1.25rem; text-align: center; }
        #mapa-empresa {
            height: 400px; border-radius: 20px; overflow: hidden;
            border: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }

        /* ── REDES SOCIALES / FOOTER ───────────────────────────────── */
        .redes-section {
            border-top: 1px solid rgba(255,255,255,.07);
            padding: 3rem 1.5rem;
            text-align: center;
        }
        .redes-inner { max-width: 600px; margin: 0 auto; }
        .redes-inner h3 { font-size: 1.3rem; font-weight: 700; color: #fff; margin-bottom: 1.25rem; }
        .redes-links { display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; }
        .red-btn {
            display: inline-flex; align-items: center; gap: .55rem;
            padding: .65rem 1.25rem; border-radius: 12px;
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
            color: rgba(255,255,255,.8); font-size: .88rem; font-weight: 600;
            text-decoration: none; transition: background .2s, transform .15s;
        }
        .red-btn:hover { background: rgba(255,255,255,.12); transform: translateY(-2px); }
        .footer-copy {
            margin-top: 3rem; text-align: center; font-size: .78rem;
            color: rgba(255,255,255,.2); padding-bottom: 2rem;
        }

        /* ── WHATSAPP FLOTANTE ─────────────────────────────────────── */
        .wa-float {
            position: fixed; bottom: 1.75rem; right: 1.75rem; z-index: 999;
            width: 58px; height: 58px; border-radius: 50%; background: #25d366;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 24px rgba(37,211,102,.45); text-decoration: none;
            transition: transform .2s, box-shadow .2s;
        }
        .wa-float:hover { transform: scale(1.1); box-shadow: 0 8px 32px rgba(37,211,102,.6); }

        @media(max-width: 768px) {
            .hero h1 { font-size: 2.2rem; }
            .section { padding: 3.5rem 1rem; }
            .calificaciones-wrapper { grid-template-columns: 1fr; gap: 2rem; }
        }
    </style>
</head>
<body>

{{-- ── HERO ──────────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        @if($apariencia['logo_url'])
            <img src="{{ asset('storage/' . $apariencia['logo_url']) }}"
                 alt="Logo {{ $empresa->nombre }}" class="hero-logo">
        @else
            <div class="hero-logo-placeholder">
                {{ strtoupper(substr($empresa->nombre, 0, 1)) }}
            </div>
        @endif

        <div class="hero-badge">
            <svg width="14" height="14" fill="var(--cp)" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            <span>{{ $sucursales->count() }}</span>
            sede{{ $sucursales->count() != 1 ? 's' : '' }} disponible{{ $sucursales->count() != 1 ? 's' : '' }}
        </div>

        <h1>{{ $apariencia['titulo_tienda'] }}</h1>
        <p>{{ $apariencia['descripcion'] }}</p>

        <!-- Buscador de barrios de Neiva -->
        <div class="search-container">
            <div class="search-wrapper">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: rgba(255,255,255,0.4); margin-right: 0.5rem;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" class="search-input" id="input-barrio" placeholder="¿Llegamos a tu zona? Digita tu barrio de Neiva..." autocomplete="off">
                <button class="search-btn" type="button" title="Buscar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
            <div class="search-dropdown" id="dropdown-barrios"></div>
        </div>
    </div>

    <div class="scroll-hint">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M7 10l5 5 5-5"/>
        </svg>
        Ver sedes
    </div>
</section>

{{-- ── CÓMO FUNCIONA ─────────────────────────────────────────────── --}}
<div class="section" style="padding-bottom: 2rem;">
    <div class="section-header" style="margin-bottom: 2.5rem;">
        <div class="pill-label">Paso a paso</div>
        <h2>¿Cómo funciona?</h2>
        <p>Realizar tu pedido o reservar es rápido y seguro.</p>
    </div>

    <div class="como-funciona-grid">
        <div class="card-paso">
            <div class="paso-numero">1</div>
            <div class="paso-titulo">Selecciona tu Sede</div>
            <div class="paso-desc">Elige la sucursal más cercana en el mapa o búscala por tu barrio.</div>
        </div>
        <div class="card-paso">
            <div class="paso-numero">2</div>
            <div class="paso-titulo">Elige tu Orden o Reserva</div>
            <div class="paso-desc">Explora el menú y arma tu carrito de domicilio, o reserva tu mesa en segundos.</div>
        </div>
        <div class="card-paso">
            <div class="paso-numero">3</div>
            <div class="paso-titulo">Disfruta tu Pedido</div>
            <div class="paso-desc">Paga de forma fácil y sigue el estado de tu pedido hasta que llegue a tu puerta.</div>
        </div>
    </div>
</div>

{{-- ── SUCURSALES ───────────────────────────────────────────────── --}}
@if($apariencia['mostrar_sucursales'] && $sucursales->isNotEmpty())
<div class="section" id="seccion-sedes">
    <div class="section-header">
        <div class="pill-label">Nuestras Sedes</div>
        <h2>¿Dónde quieres pedir?</h2>
        <p>Elige la sede más cercana y realiza tu pedido a domicilio o reserva tu mesa.</p>
    </div>

    <div class="sucursales-grid">
        @foreach($sucursales as $sucursal)
        @php $abierta = $sucursal->estaAbierta(); @endphp
        <div class="card-sucursal" id="sede-{{ $sucursal->id }}">
            <div>
                <div class="card-top">
                    <div class="card-nombre">{{ $sucursal->nombre }}</div>
                    <div class="badge-estado {{ $abierta ? 'badge-abierta' : 'badge-cerrada' }}">
                        {{ $abierta ? '● Abierta' : '○ Cerrada' }}
                    </div>
                </div>

                <div class="card-info">
                    @if($sucursal->direccion)
                    <div class="card-info-row">
                        <svg width="15" height="15" fill="rgba(255,255,255,.45)" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
                        </svg>
                        {{ $sucursal->direccion }}{{ $sucursal->ciudad ? ', ' . $sucursal->ciudad : '' }}
                    </div>
                    @endif
                    @if($sucursal->telefono)
                    <div class="card-info-row">
                        <svg width="15" height="15" fill="rgba(255,255,255,.45)" viewBox="0 0 24 24">
                            <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.4 11.4 0 003.57.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.01l-2.2 2.21z"/>
                        </svg>
                        {{ $sucursal->telefono }}
                    </div>
                    @endif
                    @if($sucursal->hora_apertura && $sucursal->hora_cierre)
                    <div class="card-info-row">
                        <svg width="15" height="15" fill="rgba(255,255,255,.45)" viewBox="0 0 24 24">
                            <path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm.5 11h-3a.5.5 0 010-1H12V7a.5.5 0 011 0v5.5a.5.5 0 01-.5.5z"/>
                        </svg>
                        {{ \Carbon\Carbon::createFromTimeString($sucursal->hora_apertura)->format('g:i a') }}
                        – {{ \Carbon\Carbon::createFromTimeString($sucursal->hora_cierre)->format('g:i a') }}
                    </div>
                    @endif
                </div>

                @if($sucursal->total_barrios > 0)
                <div class="card-stat">
                    <svg width="15" height="15" fill="var(--cp)" viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5A1.5 1.5 0 014.5 17c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9A1.5 1.5 0 0116.5 17c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                    Domicilios a <strong>{{ $sucursal->total_barrios }}</strong> barrio{{ $sucursal->total_barrios != 1 ? 's' : '' }}
                </div>
                @endif
            </div>

            <div class="card-actions">
                @if($abierta)
                <a href="{{ route('cliente.domicilio', ['empresa_slug' => $empresa->slug, 'sucursal_slug' => $sucursal->slug]) }}"
                   class="btn-pedir">
                    Pedir Domicilio
                </a>
                @else
                <div class="btn-pedir disabled">
                    Cerrada
                </div>
                @endif

                @if($sucursal->tieneReservasActivas())
                <a href="{{ route('cliente.reservas.formulario', ['slug' => $sucursal->slug]) }}"
                   class="btn-reservar" title="Reservar mesa en esta sede">
                    Reservar
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── PRODUCTOS RECOMENDADOS ──────────────────────────────────── --}}
@if($productosDestacados->isNotEmpty())
<div class="section" style="padding-top: 2rem;">
    <div class="section-header">
        <div class="pill-label">Los favoritos</div>
        <h2>Recomendados del Chef</h2>
        <p>Échale un vistazo a las especialidades preferidas de nuestros clientes.</p>
    </div>

    <div class="productos-grid">
        @foreach($productosDestacados as $producto)
        <div class="card-producto">
            <div class="producto-img-container">
                <span class="tag-sede">{{ $producto->sucursal->nombre }}</span>
                @if($producto->precio_oferta && $producto->precio_oferta < $producto->precio)
                    <span class="tag-oferta">Oferta</span>
                @endif
                @if($producto->imagen)
                    <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                @else
                    <span class="producto-img-placeholder">☕</span>
                @endif
            </div>
            <div class="producto-body">
                <h3 class="producto-nombre">{{ $producto->nombre }}</h3>
                <p class="producto-desc">{{ $producto->descripcion ?? 'Exquisito producto preparado con ingredientes de la más alta calidad.' }}</p>
                
                <div class="producto-footer">
                    <div class="producto-precios">
                        @if($producto->precio_oferta && $producto->precio_oferta < $producto->precio)
                            <span class="producto-precio-antes">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                            <span class="producto-precio-actual">${{ number_format($producto->precio_oferta, 0, ',', '.') }}</span>
                        @else
                            <span class="producto-precio-actual">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <a href="{{ route('cliente.domicilio', ['empresa_slug' => $empresa->slug, 'sucursal_slug' => $producto->sucursal->slug]) }}" class="btn-ver-menu">
                        Ver Menú
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── CALIFICACIONES Y TESTIMONIOS (SOCIAL PROOF) ──────────────── --}}
<div class="calificaciones-section">
    <div class="section-header" style="margin-bottom: 3rem;">
        <div class="pill-label">Opinión de clientes</div>
        <h2>Experiencias Reales</h2>
        <p>Nuestra mayor recompensa es tu felicidad. Esto es lo que dicen de nosotros.</p>
    </div>

    <div class="calificaciones-wrapper">
        <div class="calificaciones-resumen">
            <div class="calificaciones-score">{{ $promedioCalificacion }}</div>
            <div class="calificaciones-estrellas">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($promedioCalificacion))
                        ★
                    @else
                        ☆
                    @endif
                @endfor
            </div>
            <div class="calificaciones-texto">Calificación promedio basada en la opinión de nuestros clientes.</div>
        </div>

        <div class="reviews-lista">
            @if($calificacionesList->isNotEmpty())
                @foreach($calificacionesList as $calificacion)
                <div class="card-review">
                    <div class="review-header">
                        <div class="review-autor">{{ $calificacion->cliente ? $calificacion->cliente->nombre : 'Cliente Verificado' }}</div>
                        <div class="review-stars">{{ str_repeat('★', $calificacion->puntuacion) }}{{ str_repeat('☆', 5 - $calificacion->puntuacion) }}</div>
                    </div>
                    <div class="review-comment">"{{ $calificacion->comentario ?? 'Excelente atención, rapidez en la entrega del domicilio y los productos estaban deliciosos.' }}"</div>
                </div>
                @endforeach
            @else
                <!-- Testimonios de respaldo estéticos si no hay calificaciones en DB -->
                <div class="card-review">
                    <div class="review-header">
                        <div class="review-autor">María Alejandra Gómez</div>
                        <div class="review-stars">★★★★★</div>
                    </div>
                    <div class="review-comment">"Me encanta pedir a domicilio aquí. La comida llega caliente, los domiciliarios son muy educados y la plataforma es súper fácil de usar."</div>
                </div>
                <div class="card-review">
                    <div class="review-header">
                        <div class="review-autor">Juan Camilo Restrepo</div>
                        <div class="review-stars">★★★★★</div>
                    </div>
                    <div class="review-comment">"Hice la reservación de mesa para celebrar un cumpleaños y todo fue excelente. El depósito del pago de reserva fue seguro y el menú estaba genial."</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── MAPA ─────────────────────────────────────────────────────── --}}
@php
    $sucursalesConCoords = $sucursales->filter(fn($s) => $s->latitud && $s->longitud);
@endphp
@if($apariencia['mostrar_mapa'] && $sucursalesConCoords->isNotEmpty())
<div class="map-section">
    <div class="map-wrapper">
        <h2>Ubicación de Sucursales</h2>
        <div id="mapa-empresa" style="margin-top: 1.5rem;"></div>
    </div>
</div>
@endif

{{-- ── REDES SOCIALES ───────────────────────────────────────────── --}}
@php
    $redesConfiguradas = collect([
        'whatsapp'  => $apariencia['whatsapp'],
        'instagram' => $apariencia['instagram'],
        'facebook'  => $apariencia['facebook'],
        'tiktok'    => $apariencia['tiktok'],
    ])->filter();
@endphp
@if($redesConfiguradas->isNotEmpty())
<div class="redes-section">
    <div class="redes-inner">
        <h3>Síguenos y escríbenos</h3>
        <div class="redes-links">
            @if($apariencia['whatsapp'])
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $apariencia['whatsapp']) }}" target="_blank" rel="noopener" class="red-btn" style="background:rgba(37,211,102,.12);border-color:rgba(37,211,102,.25);color:#25d366">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            @endif
            @if($apariencia['instagram'])
            <a href="{{ $apariencia['instagram'] }}" target="_blank" rel="noopener" class="red-btn" style="background:rgba(225,48,108,.1);border-color:rgba(225,48,108,.25);color:#e1306c">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                Instagram
            </a>
            @endif
            @if($apariencia['facebook'])
            <a href="{{ $apariencia['facebook'] }}" target="_blank" rel="noopener" class="red-btn" style="background:rgba(24,119,242,.1);border-color:rgba(24,119,242,.25);color:#1877f2">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook
            </a>
            @endif
            @if($apariencia['tiktok'])
            <a href="{{ $apariencia['tiktok'] }}" target="_blank" rel="noopener" class="red-btn">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.26 8.26 0 004.83 1.54V6.79a4.85 4.85 0 01-1.06-.1z"/></svg>
                TikTok
            </a>
            @endif
        </div>
    </div>
</div>
@endif

<div class="footer-copy">
    &copy; {{ date('Y') }} {{ $empresa->nombre }} · Powered by SGPD
</div>

{{-- ── WHATSAPP FLOTANTE ────────────────────────────────────────── --}}
@if($apariencia['whatsapp'])
<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $apariencia['whatsapp']) }}"
   target="_blank" rel="noopener" class="wa-float" title="Escríbenos por WhatsApp">
    <svg width="30" height="30" fill="#fff" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>
@endif

{{-- ── MODAL COBERTURA ────────────────────────────────────────── --}}
<div class="modal-cobertura" id="modal-cobertura">
    <div class="modal-card">
        <button class="modal-close" id="btn-cerrar-modal">✕</button>
        
        <!-- Vista Éxito: Sede Encontrada -->
        <div id="modal-cobertura-ok">
            <div class="modal-icon">📍</div>
            <h3 class="modal-titulo">¡Sede Encontrada!</h3>
            <p style="font-size: 0.9rem; color: rgba(255,255,255,0.7); line-height: 1.5;">Tenemos cobertura en <strong class="modal-barrio" id="modal-barrio-nombre" style="color:var(--cp)"></strong> para tu pedido.</p>
            
            <div class="modal-info-box">
                <div class="modal-info-row">
                    <span>Sucursal Encargada:</span>
                    <strong id="modal-sucursal-nombre"></strong>
                </div>
                <div class="modal-info-row">
                    <span>Costo del Domicilio:</span>
                    <strong id="modal-costo-envio"></strong>
                </div>
                <div class="modal-info-row">
                    <span>Tiempo Estimado:</span>
                    <strong id="modal-tiempo-estimado"></strong>
                </div>
            </div>

            <a href="" class="btn-pedir" id="modal-btn-pedir" style="margin-top: 1rem;">
                Realizar Pedido Aquí
            </a>
        </div>

        <!-- Vista Fallo: Sin Cobertura -->
        <div id="modal-cobertura-fail" style="display: none;">
            <div class="modal-icon" style="color: #ef4444;">✕</div>
            <h3 class="modal-titulo" style="color:#fff">Sin Cobertura</h3>
            <p style="font-size: 0.9rem; color: rgba(255,255,255,0.7); line-height: 1.5;">Lo sentimos, actualmente no contamos con cobertura de entrega en el barrio <strong id="modal-barrio-nombre-fail" style="color:#ef4444"></strong>.</p>
            
            <p style="font-size: 0.85rem; color: rgba(255,255,255,0.5); margin: 1.5rem 0 1rem; line-height: 1.5;">Te invitamos a visitarnos en nuestras sedes físicas o realizar tu pedido para llevar.</p>

            <button class="btn-pedir" id="modal-btn-ver-sedes" style="margin-top: 1rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color:#fff">
                Ver Sedes Físicas
            </button>
        </div>
    </div>
</div>

{{-- ── SCRIPTS (BUSCADOR Y MAPA) ────────────────────────────────── --}}
@if($apariencia['mostrar_mapa'] && $sucursalesConCoords->isNotEmpty())
@php
    $sedesData = $sucursalesConCoords->map(fn($s) => [
        'lat'       => (float) $s->latitud,
        'lng'       => (float) $s->longitud,
        'nombre'    => $s->nombre,
        'direccion' => $s->direccion,
        'abierta'   => $s->estaAbierta(),
    ])->values()->toArray();
@endphp
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sedes = @json($sedesData);

    if (!sedes.length) return;

    const map = L.map('mapa-empresa', { zoomControl: true, scrollWheelZoom: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);

    const color = '{{ $apariencia['color_primario'] }}';
    const icon = (abierta) => L.divIcon({
        className: '',
        html: `<div style="width:36px;height:36px;border-radius:50%;background:${abierta ? color : '#64748b'};border:3px solid #fff;box-shadow:0 2px 12px rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;">
                 <svg width="16" height="16" fill="#fff" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>
               </div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -38],
    });

    const bounds = [];
    sedes.forEach(s => {
        const marker = L.marker([s.lat, s.lng], { icon: icon(s.abierta) }).addTo(map);
        marker.bindPopup(`<strong style="color:#1e293b">${s.nombre}</strong><br><small style="color:#64748b">${s.direccion ?? ''}</small><br><small>${s.abierta ? '🟢 Abierta' : '🔴 Cerrada'}</small>`);
        bounds.push([s.lat, s.lng]);
    });

    if (bounds.length === 1) {
        map.setView(bounds[0], 15);
    } else {
        map.fitBounds(bounds, { padding: [40, 40] });
    }
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Lista completa de barrios representativos de Neiva
    const barriosNeiva = [
        // Comuna 1
        "Santa Inés", "Cándido Leguízamo", "Las Mercedes", "Las Ferias", "Chicalá", 
        "Camilo Torres", "Minuto de Dios", "Acapulco", "El Triángulo", "El Progreso", 
        "Aeropuerto", "La Inmaculada", "Rodrigo Lara", "Valle de las Ceibas",
        // Comuna 2
        "Álvaro Sánchez Silva", "Los Andes", "Las Granjas", "Gualanday", "Villa Milena", 
        "Los Pinos", "Villa María", "La Clarita", "Los Cipreses", "Villas de San Ignacio",
        // Comuna 3
        "El Lago", "Caracolí", "Leesburg", "Brisas del Magdalena", "Quirinal", 
        "Campo Núñez", "Tenerife", "Sevilla", "Rojas Pinilla", "San Vicente de Paul",
        // Comuna 4
        "Centro", "Diego de Ospina", "Los Mártires", "San Pedro", "Altico", 
        "Estación", "Eustasio Rivera", "Los Comuneros",
        // Comuna 5
        "Buganviles", "La Florida", "Ipanema", "El Vergel", "Los Álamos", 
        "Las Catleyas", "Monserrate", "La Esperanza", "Jardín", "La Rioja", 
        "El Morado", "El Tesoro",
        // Comuna 6
        "San Luis", "Bosques de San Luis", "Puertas del Sol", "San Jorge", "Canaima", 
        "Los Almendros", "El Limonar", "Loma Linda", "Timanco", "Calixto Leyva", 
        "El Oasis", "Manzanares",
        // Comuna 7
        "Calixto", "Las Brisas", "La Gaitana", "El Prado", "Ventila", "Obrero", 
        "San Martín",
        // Comuna 8
        "Alberto Galindo", "Los Parques", "Uribe Uribe", "Las Américas", "El Peñón",
        // Comuna 9
        "Santa Rosa", "Luis Eduardo Vanegas", "Villa Constanza", "Darío Echandía",
        // Comuna 10
        "El Caguán", "Las Palmas", "Santander", "Mirasierra", "Granjas de San Luis"
    ];

    const barriosCobertura = @json($barriosCobertura);

    const input = document.getElementById('input-barrio');
    const dropdown = document.getElementById('dropdown-barrios');
    const modal = document.getElementById('modal-cobertura');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal');

    // Elementos del modal de ÉXITO
    const modalOk = document.getElementById('modal-cobertura-ok');
    const modalBarrioNombre = document.getElementById('modal-barrio-nombre');
    const modalSucursalNombre = document.getElementById('modal-sucursal-nombre');
    const modalCostoEnvio = document.getElementById('modal-costo-envio');
    const modalTiempoEstimado = document.getElementById('modal-tiempo-estimado');
    const modalBtnPedir = document.getElementById('modal-btn-pedir');

    // Elementos del modal de FALLO
    const modalFail = document.getElementById('modal-cobertura-fail');
    const modalBarrioNombreFail = document.getElementById('modal-barrio-nombre-fail');
    const modalBtnVerSedes = document.getElementById('modal-btn-ver-sedes');

    const normalizar = (str) => {
        return str ? str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim() : '';
    };

    // Agregar eventos de entrada para el autocompletado
    input.addEventListener('input', function() {
        const val = normalizar(this.value);
        dropdown.innerHTML = '';
        if (!val) {
            dropdown.style.display = 'none';
            return;
        }

        // Filtrar lista estática de todos los barrios de Neiva
        const matches = barriosNeiva.filter(nombre => normalizar(nombre).includes(val));

        if (matches.length === 0) {
            dropdown.innerHTML = '<div class="search-item" style="cursor:default;color:rgba(255,255,255,0.4);">No encontramos este barrio en Neiva.</div>';
            dropdown.style.display = 'block';
            return;
        }

        matches.slice(0, 5).forEach(nombre => {
            const div = document.createElement('div');
            div.className = 'search-item';
            div.textContent = nombre;
            div.addEventListener('click', function() {
                verificarCoberturaBarrio(nombre);
                dropdown.style.display = 'none';
                input.value = nombre;
            });
            dropdown.appendChild(div);
        });
        dropdown.style.display = 'block';
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            dropdown.style.display = 'none';
        }
    });

    // Buscar si el barrio seleccionado de Neiva tiene cobertura en la base de datos
    function verificarCoberturaBarrio(nombreBarrio) {
        const normalSelected = normalizar(nombreBarrio);
        
        // Buscar coincidencia en la cobertura de la empresa
        const match = barriosCobertura.find(b => normalizar(b.nombre) === normalSelected);

        if (match) {
            // MOSTRAR MODAL DE ÉXITO (Con Cobertura)
            modalBarrioNombre.textContent = match.nombre;
            modalSucursalNombre.textContent = match.sucursal_nombre;
            modalCostoEnvio.textContent = match.costo_envio > 0 ? '$' + match.costo_envio.toLocaleString('es-CO', { minimumFractionDigits: 0 }) : 'Gratis';
            modalTiempoEstimado.textContent = match.tiempo_estimado + ' min';
            
            // Generar enlace dinámico para pedir a domicilio
            const routeTemplate = "{{ route('cliente.domicilio', ['empresa_slug' => $empresa->slug, 'sucursal_slug' => 'SLUG_PLACEHOLDER']) }}";
            modalBtnPedir.href = routeTemplate.replace('SLUG_PLACEHOLDER', match.sucursal_slug);

            modalOk.style.display = 'block';
            modalFail.style.display = 'none';
        } else {
            // MOSTRAR MODAL DE FALLO (Sin Cobertura)
            modalBarrioNombreFail.textContent = nombreBarrio;
            
            modalOk.style.display = 'none';
            modalFail.style.display = 'block';
        }

        // Abrir modal
        modal.classList.add('active');
    }

    // Botón de ver sedes en caso de fallo: scroll a la sección
    modalBtnVerSedes.addEventListener('click', function() {
        modal.classList.remove('active');
        const seccionSedes = document.getElementById('seccion-sedes');
        if (seccionSedes) {
            seccionSedes.scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Cerrar modal
    btnCerrarModal.addEventListener('click', function() {
        modal.classList.remove('active');
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
});
</script>

</body>
</html>
