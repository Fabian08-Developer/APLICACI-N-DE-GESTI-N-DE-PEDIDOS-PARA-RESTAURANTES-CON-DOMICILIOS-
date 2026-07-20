<div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.2rem;">Mapa de Cobertura</h1>
            @if($sucursalData)
                <p class="page-subtitle">Sede: <strong style="color: var(--text-main);">{{ $sucursalData['nombre'] }}</strong></p>
            @endif
        </div>
    </div>

    @if(!$sucursalData)
    <div style="background: #fff; border-radius: 1rem; border: 1px solid var(--border); padding: 4rem 2rem; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Sin sede asignada</h3>
        <p style="color: var(--text-sec);">No tienes una sede asignada. Contacta al gerente para que te asigne una.</p>
    </div>
    @else
    <style>
        .map-container-main {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .map-grid {
            display: flex;
            flex-direction: column; /* Sidebar arriba, mapa abajo en móvil */
            gap: 1.5rem;
        }
        .map-wrapper-box {
            height: 400px;
            background: #fff;
            border-radius: 1rem;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        @media(min-width: 992px) {
            .map-container-main {
                height: calc(100vh - 180px);
                min-height: 600px;
                margin-bottom: 0;
            }
            .map-grid {
                display: grid;
                grid-template-columns: 320px 1fr;
                height: 100%;
            }
            .map-wrapper-box {
                height: 100%;
                min-height: 100%;
            }
        }
    </style>
    <div class="map-container-main">
        <div class="map-grid">

        {{-- Panel lateral --}}
        <aside style="background: #fff; border-radius: 1rem; border: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: rgba(0,0,0,0.01);">
                <h2 style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-sec); margin: 0;">Tu Sede</h2>
            </div>
            <div style="padding: 1.25rem; flex: 1; overflow-y: auto;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 1.25rem; height: 1.25rem; border-radius: 50%; background-color: {{ $sucursalData['color'] }}; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);"></div>
                    <span style="font-size: 1.2rem; font-weight: 700; color: var(--text-main);">{{ $sucursalData['nombre'] }}</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--text-sec); margin-bottom: 0.5rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                    <span>📍</span> <span>{{ $sucursalData['direccion'] }}</span>
                </p>
                @if($sucursalData['telefono'] !== '—')
                <p style="font-size: 0.9rem; color: var(--text-sec); margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                    <span>📞</span> <span>{{ $sucursalData['telefono'] }}</span>
                </p>
                @endif
                
                <div style="background: rgba(44, 36, 27, 0.03); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem;">
                    <div style="font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.75rem;">
                        <strong style="font-size: 1.25rem; color: var(--primary);">{{ count($sucursalData['barrios']) }}</strong><br>
                        barrio(s) con coordenadas en el mapa
                    </div>
                    
                    <h4 style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-sec); margin-bottom: 0.5rem; margin-top: 1rem;">Barrios con pin</h4>
                    <div style="max-height: 200px; overflow-y: auto; padding-right: 0.5rem;">
                        @forelse($sucursalData['barrios'] as $barrio)
                            @if($barrio['latitud'] && $barrio['longitud'])
                            <div style="font-size: 0.85rem; color: var(--text-main); padding: 0.4rem 0; border-bottom: 1px solid var(--border);">
                                • {{ $barrio['nombre'] }}
                            </div>
                            @endif
                        @empty
                            <p style="font-size: 0.85rem; color: var(--text-sec); font-style: italic;">Ninguno</p>
                        @endforelse
                    </div>
                </div>

                <div style="margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-sec); padding: 0.75rem; background: rgba(230, 181, 102, 0.1); border-radius: 0.5rem; line-height: 1.4;">
                    Para agregar barrios al mapa, ve a <strong>Gestión de Zonas</strong> y edita cada barrio para asignarle coordenadas.
                </div>
            </div>
        </aside>

        {{-- Contenedor del mapa --}}
        <div class="map-wrapper-box">
            <div id="mapa-admin" style="width: 100%; height: 100%; z-index: 1;"></div>
        </div>
        </div>
    </div>
    @endif

    @if($sucursalData)
    <div wire:ignore id="mapa-admin-wrapper">
        {{-- Leaflet se carga dinámicamente --}}
    </div>
    <script>
    (function() {
        function cargarLeaflet(cb) {
            if (!document.getElementById('leaflet-css-main')) {
                var css = document.createElement('link');
                css.id  = 'leaflet-css-main';
                css.rel = 'stylesheet';
                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(css);
            }
            if (window.L) { cb(); return; }
            var s = document.createElement('script');
            s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            s.crossOrigin = '';
            s.onload = cb;
            document.head.appendChild(s);
        }

        function initMapa() {
            var el = document.getElementById('mapa-admin');
            if (!el || !window.L) return;

            var sede  = @json($sucursalData);
            var mapa  = L.map('mapa-admin', { zoomControl: true }).setView([4.7110, -74.0721], 12);
            var color = sede.color;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(mapa);

            var bounds = [];

            if (sede.latitud && sede.longitud) {
                var iconSede = L.divIcon({
                    className: '',
                    html: '<div style="width:40px;height:40px;background:' + color + ';border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 12px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;"><div style="transform:rotate(45deg);font-size:16px;">🏪</div></div>',
                    iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -40]
                });
                L.marker([sede.latitud, sede.longitud], { icon: iconSede })
                    .addTo(mapa)
                    .bindPopup('<strong>' + sede.nombre + '</strong><br><small>' + sede.direccion + '</small>')
                    .openPopup();
                bounds.push([sede.latitud, sede.longitud]);
            }

            sede.barrios.forEach(function(barrio) {
                if (!barrio.latitud || !barrio.longitud) return;
                var iconBarrio = L.divIcon({
                    className: '',
                    html: '<div style="width:24px;height:24px;background:' + color + ';border:2.5px solid white;border-radius:50%;opacity:0.85;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
                    iconSize: [24, 24], iconAnchor: [12, 12], popupAnchor: [0, -14]
                });
                L.marker([barrio.latitud, barrio.longitud], { icon: iconBarrio })
                    .addTo(mapa)
                    .bindPopup('<strong>' + barrio.nombre + '</strong><br><small>Barrio con cobertura</small>');
                bounds.push([barrio.latitud, barrio.longitud]);
            });

            if (bounds.length > 0) {
                mapa.fitBounds(bounds, { padding: [40, 40] });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { cargarLeaflet(initMapa); });
        } else {
            cargarLeaflet(initMapa);
        }
    })();
    </script>
    @endif
</div>
