<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="max-w-screen-2xl mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Mapa de Sedes y Cobertura
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Visualiza la ubicación de todas tus sedes y sus zonas de cobertura</p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ count($sucursalesData) }} sede(s) registradas
            </div>
        </div>
    </div>

    <div class="max-w-screen-2xl mx-auto px-6 py-6 flex flex-col lg:flex-row gap-6">

        {{-- Leyenda de sedes --}}
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Leyenda de Sedes</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($sucursalesData as $sede)
                    <div class="px-4 py-3 flex items-start gap-3">
                        <div class="mt-1 flex-shrink-0 w-3.5 h-3.5 rounded-full border-2 border-white shadow"
                             style="background-color: {{ $sede['color'] }};"></div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $sede['nombre'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $sede['direccion'] }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $sede['activo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $sede['activo'] ? 'Activa' : 'Inactiva' }}
                                </span>
                                <span class="text-xs text-gray-400">{{ count($sede['barrios']) }} barrio(s)</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-6 text-center text-sm text-gray-400">No hay sedes con coordenadas registradas.</div>
                    @endforelse
                </div>
            </div>

            {{-- Info barrios sin ubicación --}}
            @php
                $totalBarriosSinUbicacion = collect($sucursalesData)->sum(fn($s) => 0);
            @endphp
            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-3">
                <p class="text-xs text-amber-700 dark:text-amber-400">
                    <strong>💡 Tip:</strong> Los barrios sin coordenadas no se muestran en el mapa. Puedes configurar su ubicación desde <strong>Gestión de Zonas → Editar barrio</strong>.
                </p>
            </div>
        </aside>

        {{-- Mapa --}}
        <div class="flex-1 min-h-[600px]">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full" style="min-height: 600px;" wire:ignore>
                <div id="mapa-gerente" class="w-full" style="height: 600px;"></div>
            </div>
        </div>
    </div>
    <script>
    function initMapaGerente() {
        const mapContainer = document.getElementById('mapa-gerente');
        if (!mapContainer) return;

        // Evitar el error "Map container is already initialized"
        if (window.miMapaGerenteInstance) {
            window.miMapaGerenteInstance.remove();
        }

        const sucursales = @json($sucursalesData);

        // Inicializar mapa centrado en Colombia
        const mapa = L.map('mapa-gerente', {
            zoomControl: true,
            scrollWheelZoom: true,
        }).setView([4.7110, -74.0721], 12);

        window.miMapaGerenteInstance = mapa;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapa);

        const bounds = [];

        sucursales.forEach(function (sede) {
            const color = sede.color;

            if (sede.latitud && sede.longitud) {
                const iconSede = L.divIcon({
                    className: '',
                    html: `<div style="
                        width: 36px; height: 36px;
                        background: ${color};
                        border: 3px solid white;
                        border-radius: 50% 50% 50% 0;
                        transform: rotate(-45deg);
                        box-shadow: 0 2px 8px rgba(0,0,0,0.35);
                        display: flex; align-items: center; justify-content: center;
                    ">
                        <div style="transform: rotate(45deg); font-size: 14px;">🏪</div>
                    </div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36],
                    popupAnchor: [0, -36],
                });

                L.marker([sede.latitud, sede.longitud], { icon: iconSede })
                    .addTo(mapa)
                    .bindPopup(`
                        <div style="min-width: 180px; font-family: system-ui, sans-serif;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background: ${color}; flex-shrink: 0;"></div>
                                <strong style="font-size: 14px;">${sede.nombre}</strong>
                            </div>
                            <p style="margin: 4px 0; font-size: 12px; color: #6b7280;">📍 ${sede.direccion}</p>
                            ${sede.telefono !== '—' ? `<p style="margin: 4px 0; font-size: 12px; color: #6b7280;">📞 ${sede.telefono}</p>` : ''}
                            <p style="margin: 8px 0 0; font-size: 11px; color: #9ca3af;">${sede.barrios.length} barrio(s) con cobertura</p>
                            <span style="display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600;
                                background: ${sede.activo ? '#d1fae5' : '#fee2e2'}; color: ${sede.activo ? '#065f46' : '#991b1b'};">
                                ${sede.activo ? 'Activa' : 'Inactiva'}
                            </span>
                        </div>
                    `);

                bounds.push([sede.latitud, sede.longitud]);
            }

            sede.barrios.forEach(function (barrio) {
                if (!barrio.latitud || !barrio.longitud) return;

                const iconBarrio = L.divIcon({
                    className: '',
                    html: `<div style="
                        width: 22px; height: 22px;
                        background: ${color};
                        border: 2.5px solid white;
                        border-radius: 50%;
                        opacity: 0.85;
                        box-shadow: 0 1px 4px rgba(0,0,0,0.25);
                    "></div>`,
                    iconSize: [22, 22],
                    iconAnchor: [11, 11],
                    popupAnchor: [0, -12],
                });

                L.marker([barrio.latitud, barrio.longitud], { icon: iconBarrio })
                    .addTo(mapa)
                    .bindPopup(`
                        <div style="font-family: system-ui, sans-serif;">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: ${color}; flex-shrink: 0;"></div>
                                <strong style="font-size: 13px;">${barrio.nombre}</strong>
                            </div>
                            <p style="margin: 2px 0; font-size: 11px; color: #6b7280;">Barrio — ${sede.nombre}</p>
                        </div>
                    `);

                bounds.push([barrio.latitud, barrio.longitud]);
            });
        });

        if (bounds.length > 0) {
            mapa.fitBounds(bounds, { padding: [40, 40] });
        }
        
        // Forzar recalcular tamaño por si hubo cambios de layout
        setTimeout(() => mapa.invalidateSize(), 100);
    }

    function loadLeafletAndInit() {
        if (typeof L !== 'undefined') {
            initMapaGerente();
            return;
        }

        if (!document.getElementById('leaflet-css')) {
            const link = document.createElement('link');
            link.id = 'leaflet-css';
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
        }

        if (!document.getElementById('leaflet-js')) {
            const script = document.createElement('script');
            script.id = 'leaflet-js';
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = () => initMapaGerente();
            document.head.appendChild(script);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadLeafletAndInit);
    } else {
        loadLeafletAndInit();
    }

    document.addEventListener('livewire:navigated', loadLeafletAndInit);
    </script>
</div>
