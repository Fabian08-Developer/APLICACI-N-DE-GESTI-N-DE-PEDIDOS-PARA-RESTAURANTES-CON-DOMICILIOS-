<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Domicilio — {{ $sucursal->nombre }}</title>
    <meta name="description" content="Regístrate para realizar tu pedido a domicilio en {{ $sucursal->nombre }}.">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/acceso.css'])
    <style>
        .form-grupo {
            width: 100%;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .form-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--latte);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(193, 127, 62, 0.25);
            border-radius: 12px;
            color: var(--crema);
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--caramelo);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 12px rgba(193, 127, 62, 0.25);
        }
        .form-control::placeholder { color: rgba(242, 232, 217, 0.3); }
        select.form-control option { background: #1a0a04; color: var(--crema); }

        .btn-submit {
            width: 100%;
            padding: 1.1rem 1.4rem;
            background: linear-gradient(135deg, rgba(193, 127, 62, 0.2), rgba(193, 127, 62, 0.05));
            border: 1px solid rgba(193, 127, 62, 0.4);
            border-radius: 18px;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            color: var(--crema);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, rgba(193, 127, 62, 0.3), rgba(193, 127, 62, 0.1));
            border-color: rgba(193, 127, 62, 0.7);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(193, 127, 62, 0.2);
        }
        .btn-submit:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none;
        }
        .form-card {
            width: 100%;
            background: rgba(31, 16, 8, 0.45);
            border: 1px solid rgba(193, 127, 62, 0.15);
            border-radius: 24px;
            padding: 2rem 1.75rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        /* ─── Tarjeta de envío dinámica ─── */
        #card-envio {
            margin-bottom: 1.25rem;
            padding: 1rem 1.2rem;
            background: rgba(193, 127, 62, 0.08);
            border: 1px solid rgba(193, 127, 62, 0.25);
            border-radius: 14px;
            display: none;
            transition: all 0.3s ease;
        }
        #card-envio.visible { display: block; }
        #card-envio.sin-cobertura {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.3);
        }
        .envio-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.35rem;
        }
        .envio-label { font-size: 0.78rem; color: var(--latte); text-transform: uppercase; letter-spacing: 0.05em; }
        .envio-val { font-size: 1rem; font-weight: 600; color: var(--crema); }
        .envio-sede { font-size: 0.8rem; color: rgba(242, 232, 217, 0.6); margin-top: 0.2rem; }
        .envio-error { font-size: 0.85rem; color: #f87171; }
        .envio-spinner {
            display: inline-block; width: 16px; height: 16px;
            border: 2px solid rgba(193, 127, 62, 0.3);
            border-top-color: var(--caramelo);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle; margin-right: 6px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* Location button */
        .btn-location {
            position: absolute; right: 0.8rem; top: 50%;
            transform: translateY(-50%);
            background: rgba(193, 127, 62, 0.15);
            border: 1px solid rgba(193, 127, 62, 0.3);
            color: var(--caramelo); width: 38px; height: 38px;
            border-radius: 10px; display: flex; align-items: center;
            justify-content: center; cursor: pointer; transition: all 0.3s ease;
        }
        .btn-location:hover { background: rgba(193, 127, 62, 0.3); color: var(--crema); }
        .suggestions-list {
            position: absolute; top: calc(100% + 5px); left: 0; width: 100%;
            background: rgba(25, 12, 6, 0.95); border: 1px solid rgba(193, 127, 62, 0.3);
            border-radius: 12px; backdrop-filter: blur(10px); z-index: 50;
            list-style: none; padding: 0.5rem 0; margin: 0; max-height: 200px;
            overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5); display: none;
        }
        .suggestions-list li {
            padding: 0.8rem 1.2rem; color: var(--crema); font-size: 0.85rem;
            cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;
        }
        .suggestions-list li:last-child { border-bottom: none; }
        .suggestions-list li:hover { background: rgba(193, 127, 62, 0.2); }
    </style>
</head>
<body>
    <div class="fondo-grano"></div>
    <div class="fondo-glow"></div>

    <div class="contenedor">
        {{-- Logo --}}
        <div class="logo">
            <div class="logo-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M12 2v9"></path>
                    <path d="M8 5h8"></path>
                </svg>
            </div>
            <span class="logo-nombre">{{ $sucursal->nombre }}</span>
        </div>

        <div class="linea-deco">
            <span></span><span class="punto"></span><span></span>
        </div>

        <div class="bienvenida">
            <p class="saludo">Pedido a Domicilio</p>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 500; color: var(--crema); margin-bottom: 0.5rem;">Identificación</h2>
            <p style="font-size: 0.85rem; color: var(--tenue); max-width: 280px; margin: 0 auto 1.5rem;">
                Ingresa tus datos y selecciona tu barrio para calcular el costo de envío.
            </p>
        </div>

        @if ($errors->any())
        <div class="alerta-error" style="opacity: 1; transform: none; margin-bottom: 1rem;">
            <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('error'))
        <div class="alerta-error" style="opacity: 1; transform: none; margin-bottom: 1rem;">
            {{ session('error') }}
        </div>
        @endif

        <div class="form-card">
            <form id="form-domicilio" action="{{ route('cliente.domicilio.registro', ['sucursal_slug' => $sucursal->slug]) }}" method="POST">
                @csrf

                {{-- Nombre --}}
                <div class="form-grupo">
                    <label for="nombre_cliente" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control"
                           placeholder="Ej. Juan Pérez" value="{{ old('nombre_cliente') }}" required>
                </div>

                {{-- Teléfono --}}
                <div class="form-grupo">
                    <label for="telefono_cliente" class="form-label">Teléfono / Celular</label>
                    <input type="tel" id="telefono_cliente" name="telefono_cliente" class="form-control"
                           placeholder="Ej. 3001234567" value="{{ old('telefono_cliente') }}" required>
                </div>

                {{-- Selector de Barrio --}}
                @if($barrios->isNotEmpty())
                <div class="form-grupo">
                    <label for="barrio_id" class="form-label">Barrio</label>
                    <select id="barrio_id" name="barrio_id" class="form-control" required>
                        <option value="">— Selecciona tu barrio —</option>
                        @foreach($barrios->groupBy('zona.nombre') as $zonaNombre => $barriosZona)
                            <optgroup label="{{ $zonaNombre ?? 'Sin zona' }}">
                                @foreach($barriosZona as $barrio)
                                <option value="{{ $barrio->id }}" {{ old('barrio_id') === $barrio->id ? 'selected' : '' }}>
                                    {{ $barrio->nombre }}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                @else
                {{-- Si no hay barrios configurados, campo libre --}}
                <input type="hidden" name="barrio_id" value="">
                @endif

                {{-- Tarjeta de envío dinámica --}}
                <div id="card-envio">
                    <div id="envio-loading" style="display:none; font-size:0.85rem; color:var(--latte);">
                        <span class="envio-spinner"></span> Calculando costo de envío...
                    </div>
                    <div id="envio-info" style="display:none;">
                        <div class="envio-row">
                            <span class="envio-label">Sede asignada</span>
                            <span class="envio-val" id="envio-sede-nombre">—</span>
                        </div>
                        <div class="envio-row">
                            <span class="envio-label">Costo de envío</span>
                            <span class="envio-val" id="envio-costo">—</span>
                        </div>
                        <div class="envio-row">
                            <span class="envio-label">Tiempo estimado</span>
                            <span class="envio-val" id="envio-tiempo">—</span>
                        </div>
                    </div>
                    <div id="envio-error" style="display:none;">
                        <span class="envio-error" id="envio-error-msg"></span>
                    </div>
                </div>

                {{-- Dirección (campo complementario) --}}
                <div class="form-grupo">
                    <label for="direccion_cliente" class="form-label">Dirección Exacta</label>
                    <div style="position: relative;">
                        <input type="text" id="direccion_cliente" name="direccion_cliente" class="form-control"
                               style="padding-right: 3.5rem;"
                               placeholder="Ej. Calle 45 # 12-30, Apto 301"
                               value="{{ old('direccion_cliente') }}" autocomplete="off" required>
                        <input type="hidden" id="latitud_entrega" name="latitud_entrega" value="{{ old('latitud_entrega') }}">
                        <input type="hidden" id="longitud_entrega" name="longitud_entrega" value="{{ old('longitud_entrega') }}">
                        <button type="button" id="btn-location" class="btn-location" title="Usar mi ubicación actual">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                            </svg>
                        </button>
                        <ul id="suggestions-list" class="suggestions-list"></ul>
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="btn-submit">
                    <span>Ver Menú Digital</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>
        </div>

        <div class="pie">
            <p>SGPD © {{ date('Y') }}</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectBarrio     = document.getElementById('barrio_id');
        const cardEnvio        = document.getElementById('card-envio');
        const envioLoading     = document.getElementById('envio-loading');
        const envioInfo        = document.getElementById('envio-info');
        const envioError       = document.getElementById('envio-error');
        const btnSubmit        = document.getElementById('btn-submit');
        const inputDireccion   = document.getElementById('direccion_cliente');
        const btnLocation      = document.getElementById('btn-location');
        const suggestionsList  = document.getElementById('suggestions-list');

        let debounceTimer;
        let tieneCobertura = true; // Si no hay selector de barrios, asumir que sí hay cobertura

        // ─── 1. SELECTOR DE BARRIO → precio dinámico ─────────────────
        if (selectBarrio) {
            selectBarrio.addEventListener('change', async () => {
                const barrioId = selectBarrio.value;
                if (!barrioId) {
                    cardEnvio.classList.remove('visible', 'sin-cobertura');
                    btnSubmit.disabled = true;
                    tieneCobertura = false;
                    return;
                }

                // Mostrar loading
                cardEnvio.classList.add('visible');
                cardEnvio.classList.remove('sin-cobertura');
                envioLoading.style.display = 'block';
                envioInfo.style.display    = 'none';
                envioError.style.display   = 'none';
                btnSubmit.disabled = true;

                try {
                    const res  = await fetch(`/api/v1/barrio/${barrioId}/sede`);
                    const data = await res.json();

                    envioLoading.style.display = 'none';

                    if (data.tiene_cobertura) {
                        tieneCobertura = true;
                        envioInfo.style.display = 'block';
                        document.getElementById('envio-sede-nombre').textContent = data.sucursal.nombre;
                        document.getElementById('envio-costo').textContent =
                            data.costo_envio > 0
                                ? '$' + new Intl.NumberFormat('es-CO').format(data.costo_envio)
                                : 'Gratis';
                        document.getElementById('envio-tiempo').textContent =
                            data.tiempo_estimado + ' min aprox.';
                        btnSubmit.disabled = false;
                    } else {
                        tieneCobertura = false;
                        cardEnvio.classList.add('sin-cobertura');
                        envioError.style.display = 'block';
                        document.getElementById('envio-error-msg').textContent =
                            data.mensaje || 'No hay cobertura en tu barrio en este momento.';
                        btnSubmit.disabled = true;
                    }
                } catch (e) {
                    envioLoading.style.display = 'none';
                    envioError.style.display   = 'block';
                    document.getElementById('envio-error-msg').textContent = 'Error al verificar cobertura. Intenta de nuevo.';
                    btnSubmit.disabled = false; // Permitir continuar en error de red
                }
            });

            // Deshabilitar submit al inicio hasta seleccionar barrio
            btnSubmit.disabled = true;
        }

        // ─── 2. GPS REVERSE GEOCODING ─────────────────────────────────
        if (btnLocation) {
            btnLocation.addEventListener('click', () => {
                if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
                btnLocation.innerHTML = `<svg class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>`;
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    const { latitude: lat, longitude: lon } = pos.coords;
                    try {
                        const res  = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`);
                        const data = await res.json();
                        if (data?.display_name) {
                            inputDireccion.value = data.display_name;
                            document.getElementById('latitud_entrega').value  = lat;
                            document.getElementById('longitud_entrega').value = lon;
                        }
                    } catch(e) { alert('Error al obtener la dirección.'); }
                    finally { resetLocationIcon(); }
                }, () => { resetLocationIcon(); alert('No se pudo obtener tu ubicación.'); }, { timeout: 10000, enableHighAccuracy: true });
            });
        }

        function resetLocationIcon() {
            if (btnLocation) btnLocation.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>`;
        }

        // ─── 3. AUTOCOMPLETADO DE DIRECCIÓN ───────────────────────────
        if (inputDireccion) {
            inputDireccion.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();
                if (query.length < 4) { suggestionsList.style.display = 'none'; return; }
                debounceTimer = setTimeout(async () => {
                    try {
                        const res  = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=co&limit=5`);
                        const data = await res.json();
                        suggestionsList.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.display_name;
                                li.addEventListener('click', () => {
                                    inputDireccion.value = item.display_name;
                                    document.getElementById('latitud_entrega').value  = item.lat;
                                    document.getElementById('longitud_entrega').value = item.lon;
                                    suggestionsList.style.display = 'none';
                                });
                                suggestionsList.appendChild(li);
                            });
                            suggestionsList.style.display = 'block';
                        } else { suggestionsList.style.display = 'none'; }
                    } catch(e) { console.error('Error sugerencias', e); }
                }, 600);
            });

            document.addEventListener('click', (e) => {
                if (!inputDireccion.contains(e.target) && !suggestionsList.contains(e.target))
                    suggestionsList.style.display = 'none';
            });
        }
    });
    </script>
</body>
</html>

    <style>
        .form-grupo {
            width: 100%;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .form-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--latte);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(193, 127, 62, 0.25);
            border-radius: 12px;
            color: var(--crema);
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--caramelo);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 12px rgba(193, 127, 62, 0.25);
        }
        .form-control::placeholder {
            color: rgba(242, 232, 217, 0.3);
        }
        .btn-submit {
            width: 100%;
            padding: 1.1rem 1.4rem;
            background: linear-gradient(135deg, rgba(193, 127, 62, 0.2), rgba(193, 127, 62, 0.05));
            border: 1px solid rgba(193, 127, 62, 0.4);
            border-radius: 18px;
            cursor: pointer;
            font-family: 'Outfit', sans-serif;
            font-weight: 500;
            color: var(--crema);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, rgba(193, 127, 62, 0.3), rgba(193, 127, 62, 0.1));
            border-color: rgba(193, 127, 62, 0.7);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(193, 127, 62, 0.2);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .form-card {
            width: 100%;
            background: rgba(31, 16, 8, 0.45);
            border: 1px solid rgba(193, 127, 62, 0.15);
            border-radius: 24px;
            padding: 2rem 1.75rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        
        /* Ubicación y Sugerencias */
        .btn-location {
            position: absolute;
            right: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(193, 127, 62, 0.15);
            border: 1px solid rgba(193, 127, 62, 0.3);
            color: var(--caramelo);
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-location:hover {
            background: rgba(193, 127, 62, 0.3);
            color: var(--crema);
        }
        .suggestions-list {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            width: 100%;
            background: rgba(25, 12, 6, 0.95);
            border: 1px solid rgba(193, 127, 62, 0.3);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            z-index: 50;
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .suggestions-list li {
            padding: 0.8rem 1.2rem;
            color: var(--crema);
            font-size: 0.85rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: background 0.2s;
        }
        .suggestions-list li:last-child {
            border-bottom: none;
        }
        .suggestions-list li:hover {
            background: rgba(193, 127, 62, 0.2);
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    {{-- Fondo con textura y glow --}}
    <div class="fondo-grano"></div>
    <div class="fondo-glow"></div>

    <div class="contenedor">
        
        {{-- Logo --}}
        <div class="logo">
            <div class="logo-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M12 2v9"></path>
                    <path d="M8 5h8"></path>
                </svg>
            </div>
            <span class="logo-nombre">{{ $sucursal->nombre }}</span>
        </div>

        <div class="linea-deco">
            <span></span>
            <span class="punto"></span>
            <span></span>
        </div>

        {{-- Bienvenida --}}
        <div class="bienvenida">
            <p class="saludo">Pedido a Domicilio</p>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 500; color: var(--crema); margin-bottom: 0.5rem;">Identificación</h2>
            <p style="font-size: 0.85rem; color: var(--tenue); max-width: 280px; margin: 0 auto 1.5rem;">
                Por favor, ingresa tus datos para continuar al menú digital y realizar tu pedido.
            </p>
        </div>

        {{-- Alertas de error --}}
        @if ($errors->any())
            <div class="alerta-error" style="opacity: 1; transform: none; margin-bottom: 1rem;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario --}}
        <div class="form-card">
            <form action="{{ route('cliente.domicilio.registro', ['sucursal_slug' => $sucursal->slug]) }}" method="POST">
                @csrf
                
                <div class="form-grupo">
                    <label for="nombre_cliente" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" placeholder="Ej. Juan Pérez" value="{{ old('nombre_cliente') }}" required>
                </div>

                <div class="form-grupo">
                    <label for="telefono_cliente" class="form-label">Teléfono / Celular</label>
                    <input type="tel" id="telefono_cliente" name="telefono_cliente" class="form-control" placeholder="Ej. 3001234567" value="{{ old('telefono_cliente') }}" required>
                </div>

                <div class="form-grupo">
                    <label for="direccion_cliente" class="form-label">Dirección de Entrega</label>
                    <div style="position: relative;">
                        <input type="text" id="direccion_cliente" name="direccion_cliente" class="form-control" style="padding-right: 3.5rem;" placeholder="Escribe para buscar o usa el GPS" value="{{ old('direccion_cliente') }}" autocomplete="off" required>
                        <input type="hidden" id="latitud_entrega" name="latitud_entrega" value="{{ old('latitud_entrega') }}">
                        <input type="hidden" id="longitud_entrega" name="longitud_entrega" value="{{ old('longitud_entrega') }}">
                        <button type="button" id="btn-location" class="btn-location" title="Usar mi ubicación actual">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                            </svg>
                        </button>
                        <ul id="suggestions-list" class="suggestions-list" style="display: none;"></ul>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Ver Menú Digital</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Pie --}}
        <div class="pie">
            <p>SGPD © {{ date('Y') }}</p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputDireccion = document.getElementById('direccion_cliente');
            const btnLocation = document.getElementById('btn-location');
            const suggestionsList = document.getElementById('suggestions-list');
            
            let debounceTimer;

            // ----- 1. GEOLOCALIZACION (Reverse Geocoding) -----
            btnLocation.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    alert("Tu navegador no soporta geolocalización.");
                    return;
                }

                // Cambiar icono a cargando
                btnLocation.innerHTML = `<svg class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>`;
                
                navigator.geolocation.getCurrentPosition(async (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`);
                        const data = await response.json();
                        
                        if (data && data.display_name) {
                            inputDireccion.value = data.display_name;
                            document.getElementById('latitud_entrega').value = lat;
                            document.getElementById('longitud_entrega').value = lon;
                        } else {
                            alert("No pudimos obtener tu dirección exacta desde el GPS.");
                        }
                    } catch (error) {
                        alert("Error de red al buscar la dirección.");
                    } finally {
                        resetLocationIcon();
                    }

                }, (error) => {
                    resetLocationIcon();
                    if(error.code === 1) alert("Denegaste el acceso a tu ubicación.");
                    else alert("No se pudo obtener tu ubicación actual. Comprueba que el GPS esté activo.");
                }, { timeout: 10000, enableHighAccuracy: true });
            });

            function resetLocationIcon() {
                btnLocation.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>`;
            }

            // ----- 2. AUTOCOMPLETADO (Search Nominatim) -----
            inputDireccion.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();
                
                if (query.length < 4) { // Esperar al menos 4 caracteres para buscar
                    suggestionsList.style.display = 'none';
                    return;
                }

                debounceTimer = setTimeout(async () => {
                    try {
                        // countrycodes=co limita la busqueda a Colombia
                        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=co&limit=5`);
                        const data = await response.json();
                        
                        suggestionsList.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.textContent = item.display_name;
                                li.addEventListener('click', () => {
                                    inputDireccion.value = item.display_name;
                                    document.getElementById('latitud_entrega').value = item.lat;
                                    document.getElementById('longitud_entrega').value = item.lon;
                                    suggestionsList.style.display = 'none';
                                });
                                suggestionsList.appendChild(li);
                            });
                            suggestionsList.style.display = 'block';
                        } else {
                            suggestionsList.style.display = 'none';
                        }
                    } catch (error) {
                        console.error("Error al buscar sugerencias", error);
                    }
                }, 600); // 600ms debounce
            });

            // Ocultar sugerencias si hace clic fuera
            document.addEventListener('click', (e) => {
                if (!inputDireccion.contains(e.target) && !suggestionsList.contains(e.target)) {
                    suggestionsList.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
