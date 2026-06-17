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
            padding: 2rem;
            overflow: hidden;
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
        .hero-content { position: relative; z-index: 1; max-width: 720px; }
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
            margin: 0 auto 2rem; line-height: 1.6;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
            backdrop-filter: blur(12px); padding: .5rem 1.2rem; border-radius: 999px;
            font-size: .85rem; color: rgba(255,255,255,.7); margin-bottom: 2rem;
        }
        .hero-badge span { color: var(--cp); font-weight: 600; }
        .scroll-hint {
            position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: .4rem;
            color: rgba(255,255,255,.4); font-size: .78rem; animation: bounce 2s infinite;
        }
        @keyframes bounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(8px)} }

        /* ── SECCIÓN SUCURSALES ────────────────────────────────────── */
        .section { padding: 5rem 1.5rem; max-width: 1200px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 3rem; }
        .section-header h2 {
            font-size: 2rem; font-weight: 800; letter-spacing: -.02em;
            color: #fff; margin-bottom: .6rem;
        }
        .section-header p { color: rgba(255,255,255,.5); font-size: 1rem; }
        .pill-label {
            display: inline-block; background: rgba(var(--cp-rgb),.15);
            color: var(--cp); border: 1px solid rgba(var(--cp-rgb),.3);
            padding: .25rem .85rem; border-radius: 999px; font-size: .78rem;
            font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
            margin-bottom: .85rem;
        }

        .sucursales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .card-sucursal {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px; padding: 1.75rem;
            transition: transform .25s, border-color .25s, box-shadow .25s;
            position: relative; overflow: hidden;
        }
        .card-sucursal::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(var(--cp-rgb),.06) 0%, transparent 60%);
            opacity: 0; transition: opacity .25s;
            pointer-events: none;
        }
        .card-sucursal:hover { transform: translateY(-4px); border-color: rgba(var(--cp-rgb),.4); box-shadow: 0 20px 60px rgba(0,0,0,.4); }
        .card-sucursal:hover::before { opacity: 1; }
        .card-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
        .card-nombre { font-size: 1.2rem; font-weight: 700; color: #fff; }
        .badge-estado {
            font-size: .72rem; font-weight: 600; padding: .3rem .75rem; border-radius: 999px;
        }
        .badge-abierta { background: rgba(52,211,153,.15); color: #34d399; border: 1px solid rgba(52,211,153,.25); }
        .badge-cerrada { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
        .card-info { display: flex; flex-direction: column; gap: .55rem; margin-bottom: 1.5rem; }
        .card-info-row { display: flex; align-items: flex-start; gap: .6rem; color: rgba(255,255,255,.6); font-size: .88rem; }
        .card-info-row svg { flex-shrink: 0; margin-top: 2px; }
        .card-info-row span { color: rgba(255,255,255,.45); min-width: 75px; }
        .card-stat {
            display: flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.05); border-radius: 10px; padding: .6rem 1rem;
            margin-bottom: 1.25rem; font-size: .84rem; color: rgba(255,255,255,.55);
        }
        .card-stat strong { color: var(--cp); }
        .btn-pedir {
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            width: 100%; padding: .85rem 1.5rem; border-radius: 12px; border: none;
            background: linear-gradient(135deg, var(--cp), color-mix(in srgb, var(--cp) 70%, var(--cs)));
            color: #fff; font-size: .95rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: opacity .2s, transform .15s;
            box-shadow: 0 4px 20px rgba(var(--cp-rgb),.35);
        }
        .btn-pedir:hover { opacity: .9; transform: scale(1.02); }
        .btn-pedir.disabled {
            background: rgba(255,255,255,.08); color: rgba(255,255,255,.35);
            box-shadow: none; cursor: not-allowed;
        }
        .btn-pedir.disabled:hover { transform: none; opacity: 1; }

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

        @media(max-width: 600px) {
            .hero h1 { font-size: 2.2rem; }
            .section { padding: 3.5rem 1rem; }
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
    </div>

    <div class="scroll-hint">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M7 10l5 5 5-5"/>
        </svg>
        Ver sedes
    </div>
</section>

{{-- ── SUCURSALES ───────────────────────────────────────────────── --}}
@if($apariencia['mostrar_sucursales'] && $sucursales->isNotEmpty())
<div class="section">
    <div class="section-header">
        <div class="pill-label">Nuestras Sedes</div>
        <h2>¿Dónde quieres pedir?</h2>
        <p>Elige la sede más cercana y realiza tu pedido a domicilio.</p>
    </div>

    <div class="sucursales-grid">
        @foreach($sucursales as $sucursal)
        @php $abierta = $sucursal->estaAbierta(); @endphp
        <div class="card-sucursal">
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

            @if($abierta)
            <a href="{{ route('cliente.domicilio', ['empresa_slug' => $empresa->slug, 'sucursal_slug' => $sucursal->slug]) }}"
               class="btn-pedir">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5A1.5 1.5 0 014.5 17c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9A1.5 1.5 0 0116.5 17c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
                Pedir a Domicilio
            </a>
            @else
            <div class="btn-pedir disabled">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm0 18a8 8 0 110-16 8 8 0 010 16zm.5-13h-1v6l5.25 3.15.75-1.23-4.5-2.67V7h-.5z"/>
                </svg>
                Sede Cerrada Ahora
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── MAPA ─────────────────────────────────────────────────────── --}}
@php
    $sucursalesConCoords = $sucursales->filter(fn($s) => $s->latitud && $s->longitud);
@endphp
@if($apariencia['mostrar_mapa'] && $sucursalesConCoords->isNotEmpty())
<div class="map-section">
    <div class="map-wrapper">
        <h2>Ubicacion de Sucursales</h2>
        <div id="mapa-empresa"></div>
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

{{-- ── SCRIPTS (MAPA) ───────────────────────────────────────────── --}}
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
</body>
</html>
