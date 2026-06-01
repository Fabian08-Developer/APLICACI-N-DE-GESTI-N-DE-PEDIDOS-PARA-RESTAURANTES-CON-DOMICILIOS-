<div class="w-full pb-10"
     x-data="{
        initFlatpickr() {
            flatpickr($refs.startPicker, {
                dateFormat: 'Y-m-d',
                locale: 'es',
                theme: 'dark',
                onChange: (selectedDates, dateStr) => {
                    @this.set('startDate', dateStr);
                }
            });
            flatpickr($refs.endPicker, {
                dateFormat: 'Y-m-d',
                locale: 'es',
                theme: 'dark',
                onChange: (selectedDates, dateStr) => {
                    @this.set('endDate', dateStr);
                }
            });
        }
     }"
     x-init="initFlatpickr()"
>
    <!-- Flatpickr Assets (Dark Theme) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <!-- Custom CSS Styles for Printing -->
    <style>
        @media print {
            body {
                background: #FFFFFF !important;
                color: #0C0A09 !important;
                font-family: sans-serif !important;
            }
            /* Ocultar barra lateral, topbar, filtros y botones de acción */
            header, aside, .no-print, .shadcn-filters-container, button, select, input, .flex-actions {
                display: none !important;
            }
            /* Ocupar ancho completo */
            main {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            div {
                box-shadow: none !important;
            }
            .md\:pl-64 {
                padding-left: 0 !important;
            }
            /* Estilizar tarjetas para impresión */
            .bg-\[\#1C1917\] {
                background: #F5F5F4 !important;
                border: 1px solid #D6D3D1 !important;
                color: #0C0A09 !important;
            }
            .text-white {
                color: #0C0A09 !important;
            }
            .text-stone-500 {
                color: #44403C !important;
            }
            .text-stone-600 {
                color: #57534E !important;
            }
            .text-\[\#A85507\] {
                color: #78350F !important;
            }
            /* Tablas en impresión */
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th {
                background-color: #E7E5E4 !important;
                color: #0C0A09 !important;
                border: 1px solid #D6D3D1 !important;
            }
            td {
                border: 1px solid #D6D3D1 !important;
                color: #0C0A09 !important;
            }
            .progreso-vacia {
                background-color: #E7E5E4 !important;
                border: 1px solid #D6D3D1 !important;
            }
            .progreso-llena {
                background-color: #78350F !important;
            }
        }
    </style>

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 no-print">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight uppercase">Reportes Globales</h1>
            <p class="text-stone-500 mt-1 text-base font-medium">Visualización agregada y análisis operativo de tus sedes</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Reset Filters -->
            <button wire:click="resetFilters" class="inline-flex items-center px-4 py-3 bg-stone-900 hover:bg-stone-800 text-stone-300 hover:text-white font-bold rounded-xl transition-all border border-[#292524] uppercase text-[10px] tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 10H19.5" />
                </svg>
                Limpiar
            </button>

            <!-- Export PDF / Print -->
            <button onclick="window.print()" class="inline-flex items-center px-4 py-3 bg-stone-900 hover:bg-stone-800 text-stone-300 hover:text-white font-bold rounded-xl transition-all border border-[#292524] uppercase text-[10px] tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                PDF / Imprimir
            </button>

            <!-- Export CSV -->
            <button wire:click="exportCSV" class="inline-flex items-center px-6 py-3 bg-[#A85507] hover:bg-[#78350F] text-white font-black rounded-xl transition-all shadow-xl uppercase text-[10px] tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Exportar Reporte
            </button>
        </div>
    </div>

    <!-- Print Title Header (Only visible when printing) -->
    <div class="hidden print:block mb-8 border-b border-stone-200 pb-4">
        <h1 class="text-3xl font-bold text-stone-900 uppercase">Reporte Operativo Global</h1>
        <p class="text-stone-600 font-medium">Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p class="text-stone-600 font-medium">Rango de fechas: {{ $startDate ?: 'Inicio' }} al {{ $endDate ?: 'Fin' }}</p>
        <p class="text-stone-600 font-medium">Filtro de Sede: {{ $sucursalFilter === 'all' ? 'Todas las Sedes Activas' : (App\Models\Sucursal::find($sucursalFilter)->nombre ?? 'N/A') }}</p>
    </div>

    <!-- Filter Bar (RF-G70) -->
    <div class="bg-[#1C1917] rounded-[24px] p-4 border border-[#292524] shadow-xl mb-8 flex flex-col md:flex-row gap-4 items-center no-print">
        <!-- Branch Selector (RF-G70) -->
        <div class="w-full md:w-auto flex-1">
            <label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-1.5 pl-1">Filtrar por Sede</label>
            <div class="relative w-full">
                <select wire:model.live="sucursalFilter" class="block w-full bg-[#0C0A09] border-transparent rounded-xl text-stone-300 pl-4 pr-10 py-3.5 focus:ring-2 focus:ring-[#A85507] transition-all cursor-pointer font-bold uppercase text-[10px] tracking-widest">
                    <option value="all">Todas las Sedes Activas</option>
                    @foreach($sucursalesSelector as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }} {{ !$suc->activo ? '(Inactiva)' : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Start Date -->
        <div class="w-full md:w-auto min-w-[160px]">
            <label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-1.5 pl-1">Fecha Inicio</label>
            <input type="text" x-ref="startPicker" value="{{ $startDate }}" readonly
                class="block w-full px-4 py-3 bg-[#0C0A09] border-transparent rounded-xl text-stone-300 focus:ring-2 focus:ring-[#A85507] transition-all font-medium text-sm cursor-pointer">
        </div>

        <!-- End Date -->
        <div class="w-full md:w-auto min-w-[160px]">
            <label class="block text-[9px] font-black text-stone-600 uppercase tracking-widest mb-1.5 pl-1">Fecha Fin</label>
            <input type="text" x-ref="endPicker" value="{{ $endDate }}" readonly
                class="block w-full px-4 py-3 bg-[#0C0A09] border-transparent rounded-xl text-stone-300 focus:ring-2 focus:ring-[#A85507] transition-all font-medium text-sm cursor-pointer">
        </div>
    </div>

    <!-- Metrics Cards Grid (RF-G66) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Sales -->
        <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-stone-600 text-[9px] font-black uppercase tracking-widest">Total Ventas</p>
                    <span class="p-1.5 rounded-lg bg-[#A85507]/10 text-[#A85507] no-print">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-3xl font-black text-white tracking-tighter leading-tight">$ {{ number_format($metrics['ventas_totales'], 0, ',', '.') }}</h3>
            </div>
            <p class="text-[10px] text-stone-500 mt-4 font-semibold uppercase tracking-wider">
                {{ $sucursalFilter === 'all' ? 'Suma de sedes activas' : 'Sede individual' }}
            </p>
        </div>

        <!-- Total Orders -->
        <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-stone-600 text-[9px] font-black uppercase tracking-widest">Total Pedidos</p>
                    <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-400 no-print">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-3xl font-black text-white tracking-tighter leading-tight">{{ number_format($metrics['total_pedidos'], 0, ',', '.') }}</h3>
            </div>
            <p class="text-[10px] text-stone-500 mt-4 font-semibold uppercase tracking-wider">
                Excluye cancelados
            </p>
        </div>

        <!-- Average Ticket -->
        <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-stone-600 text-[9px] font-black uppercase tracking-widest">Ticket Promedio</p>
                    <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 no-print">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-3xl font-black text-white tracking-tighter leading-tight">$ {{ number_format($metrics['ticket_promedio'], 0, ',', '.') }}</h3>
            </div>
            <p class="text-[10px] text-stone-500 mt-4 font-semibold uppercase tracking-wider">
                Ventas / Pedidos
            </p>
        </div>

        <!-- Active Branches -->
        <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-stone-600 text-[9px] font-black uppercase tracking-widest">Sucursales Activas</p>
                    <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400 no-print">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                </div>
                <h3 class="text-3xl font-black text-white tracking-tighter leading-tight">{{ $metrics['sucursales_activas'] }}</h3>
            </div>
            <p class="text-[10px] text-stone-500 mt-4 font-semibold uppercase tracking-wider">
                En operación actual
            </p>
        </div>
    </div>

    <!-- Comparative Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Table: Sales Comparison classified by % of sales (RF-G67) -->
        <div class="bg-[#1C1917] rounded-[32px] border border-[#292524] overflow-hidden shadow-2xl">
            <div class="px-6 py-5 border-b border-[#292524] bg-stone-900/30">
                <h3 class="text-lg font-black text-white uppercase tracking-tight">Comparativa de Ventas</h3>
                <p class="text-stone-500 text-xs mt-0.5 font-bold">Clasificación de sedes según % de contribución</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-stone-900/50 border-b border-[#292524]">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Posición</th>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Sede</th>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-right">Ventas ($)</th>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-center">% de Ventas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#292524]">
                        @forelse($sucursalesData as $index => $sucursal)
                            <tr class="hover:bg-stone-900/40 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black
                                        @if($index === 0) bg-[#A85507]/20 text-[#A85507] border border-[#A85507]/30
                                        @elseif($index === 1) bg-stone-800 text-stone-300
                                        @else bg-stone-900 text-stone-500
                                        @endif"
                                    >
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-black text-white text-sm tracking-tight leading-tight uppercase">{{ $sucursal['nombre'] }}</p>
                                    <div class="inline-flex items-center gap-1.5 mt-1">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $sucursal['activo'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                        <span class="text-[8px] text-stone-500 font-bold uppercase tracking-wider">{{ $sucursal['activo'] ? 'Activa' : 'Inactiva' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-black text-white text-sm tracking-tighter">$ {{ number_format($sucursal['ventas'], 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="font-black text-xs text-[#A85507]">{{ number_format($sucursal['porcentaje_ventas'], 1, ',', '.') }}%</span>
                                        <div class="w-24 progreso-vacia h-1.5 bg-stone-900 rounded-full overflow-hidden">
                                            <div class="progreso-llena h-full bg-[#A85507] rounded-full" style="width: {{ $sucursal['porcentaje_ventas'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <p class="text-stone-600 text-sm font-black uppercase tracking-widest">Sin datos de sucursales</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table: Top Products across all branches (RF-G68) -->
        <div class="bg-[#1C1917] rounded-[32px] border border-[#292524] overflow-hidden shadow-2xl">
            <div class="px-6 py-5 border-b border-[#292524] bg-stone-900/30">
                <h3 class="text-lg font-black text-white uppercase tracking-tight">Top 5 Productos</h3>
                <p class="text-stone-500 text-xs mt-0.5 font-bold">Los 5 mejores productos de las sedes</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-stone-900/50 border-b border-[#292524]">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Rank</th>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Producto</th>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-center">Unidades</th>
                            <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-right">Recaudado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#292524]">
                        @forelse($topProducts as $index => $producto)
                            <tr class="hover:bg-stone-900/40 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black
                                        @if($index === 0) bg-amber-500/20 text-amber-500 border border-amber-500/30
                                        @elseif($index === 1) bg-slate-300/20 text-slate-300 border border-slate-300/30
                                        @elseif($index === 2) bg-amber-700/20 text-amber-700 border border-amber-700/30
                                        @else bg-stone-900 text-stone-500
                                        @endif"
                                    >
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-black text-white text-sm tracking-tight uppercase">{{ $producto->nombre_producto }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-black text-white text-xs bg-stone-900 border border-[#292524] px-2.5 py-1 rounded-lg">{{ $producto->cantidad_vendida }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-black text-[#A85507] text-sm tracking-tighter">$ {{ number_format($producto->total_ventas, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <p class="text-stone-600 text-sm font-black uppercase tracking-widest">Sin registros de ventas en este período</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Table: Branch Comparison Table showing equal % distribution (RF-G69) -->
    <div class="bg-[#1C1917] rounded-[32px] border border-[#292524] overflow-hidden shadow-2xl mb-8">
        <div class="px-6 py-5 border-b border-[#292524] bg-stone-900/30">
            <h3 class="text-lg font-black text-white uppercase tracking-tight">Comparativa de Sucursales</h3>
            <p class="text-stone-500 text-xs mt-0.5 font-bold">Ventas, cantidad de pedidos y % equitativo por sucursal</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-stone-900/50 border-b border-[#292524]">
                    <tr>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest">Nombre Sede</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-right">Total Ventas ($)</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-center">Cantidad de Pedidos</th>
                        <th class="px-6 py-4 text-[9px] font-black text-stone-600 uppercase tracking-widest text-center">% Dividido (Representación)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#292524]">
                    @forelse($sucursalesData as $sucursal)
                        <tr class="hover:bg-stone-900/40 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="font-black text-white text-base tracking-tight leading-tight uppercase">{{ $sucursal['nombre'] }}</p>
                                <div class="inline-flex items-center gap-1.5 mt-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sucursal['activo'] ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-rose-500 shadow-[0_0_8px_#f43f5e]' }}"></div>
                                    <span class="text-[8px] text-stone-500 font-bold uppercase tracking-wider">{{ $sucursal['activo'] ? 'Activa' : 'Inactiva' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-black text-white text-base tracking-tighter">$ {{ number_format($sucursal['ventas'], 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-white text-sm bg-stone-900 border border-[#292524] px-3.5 py-1.5 rounded-xl">{{ number_format($sucursal['pedidos'], 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-[#A85507] text-sm bg-[#A85507]/10 border border-[#A85507]/20 px-3 py-1.5 rounded-xl">
                                    {{ number_format($sucursal['porcentaje_dividido'], 2, ',', '.') }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center">
                                <p class="text-stone-600 text-sm font-black uppercase tracking-widest">Sin datos de sucursales</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
