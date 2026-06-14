<div class="w-full pb-10"
     style="font-family: 'Geist', sans-serif;"
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
    <!-- CSS Styles -->
    <style>
        .font-serif-elegant { font-family: 'Playfair Display', serif; }
        .card-light {
            background: #FFFFFF;
            border: 1px solid rgba(44,36,27,0.08);
            box-shadow: 0 4px 6px -1px rgba(44,36,27,0.05), 0 2px 4px -1px rgba(44,36,27,0.03);
        }
        .text-terracotta { color: #E07A5F; }
        .bg-terracotta-glow { background: radial-gradient(circle, rgba(224, 122, 95, 0.08) 0%, transparent 70%); }
        
        @media print {
            body { background: #FFFFFF !important; color: #0C0A09 !important; font-family: sans-serif !important; }
            header, aside, .no-print, .shadcn-filters-container, button, select, input, .flex-actions { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            div { box-shadow: none !important; }
            .card-light { background: #FFFFFF !important; border: 1px solid #D6D3D1 !important; color: #0C0A09 !important; }
            .text-white, .text-[#2C241B] { color: #0C0A09 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th { background-color: #E7E5E4 !important; color: #0C0A09 !important; border: 1px solid #D6D3D1 !important; }
            td { border: 1px solid #D6D3D1 !important; color: #0C0A09 !important; }
        }
    </style>

    <!-- Flatpickr Assets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8 no-print">
        <div>
            <div class="inline-flex items-center gap-2 mb-2">
                <div class="w-1.5 h-1.5 rounded-full bg-[#E07A5F]"></div>
                <h2 class="text-[10px] font-black text-[#E07A5F] uppercase tracking-widest">Inteligencia de Negocios</h2>
            </div>
            <h1 class="font-serif-elegant text-4xl font-bold text-[#2C241B] tracking-tight leading-none">Reportes Globales</h1>
            <p class="text-[#5C5246] mt-2 text-sm font-medium tracking-wide">Análisis operativo y financiero de todas tus sucursales.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Reset Filters -->
            <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2.5 bg-[#FDFBF7] hover:bg-[#F5F2ED] text-[#8B8175] hover:text-[#E07A5F] font-bold rounded-xl transition-all border border-[#2C241B]/10 uppercase text-[9px] tracking-widest shadow-sm">
                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 10H19.5" /></svg>
                Limpiar
            </button>

            <!-- Export PDF / Print -->
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2.5 bg-[#FDFBF7] hover:bg-[#F5F2ED] text-[#5C5246] hover:text-[#2C241B] font-bold rounded-xl transition-all border border-[#2C241B]/10 uppercase text-[9px] tracking-widest shadow-sm">
                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Imprimir
            </button>

            <!-- Export CSV -->
            <button wire:click="exportCSV" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-[#E07A5F] to-[#D97706] hover:from-[#D97706] hover:to-[#E07A5F] text-white font-black rounded-xl transition-all shadow-sm hover:shadow-md uppercase text-[9px] tracking-widest">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Exportar Excel
            </button>
        </div>
    </div>

    <!-- Print Title Header -->
    <div class="hidden print:block mb-8 border-b border-stone-200 pb-4">
        <h1 class="text-3xl font-bold text-stone-900 uppercase">Reporte Operativo Global</h1>
        <p class="text-stone-600 font-medium">Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p class="text-stone-600 font-medium">Rango: {{ $startDate ?: 'Inicio' }} al {{ $endDate ?: 'Fin' }}</p>
        <p class="text-stone-600 font-medium">Sede: {{ $sucursalFilter === 'all' ? 'Todas' : (App\Models\Sucursal::find($sucursalFilter)->nombre ?? 'N/A') }}</p>
    </div>

    <!-- Filter Bar Executive -->
    <div class="card-light rounded-[24px] p-5 mb-10 flex flex-col md:flex-row gap-5 items-end no-print relative overflow-hidden">
        <div class="absolute inset-0 bg-terracotta-glow opacity-20 pointer-events-none"></div>
        
        <div class="w-full md:w-auto flex-1 relative z-10">
            <label class="block text-[9px] font-black text-[#8B8175] uppercase tracking-widest mb-2 pl-1">Seleccionar Sede</label>
            <select wire:model.live="sucursalFilter" class="block w-full bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] pl-4 pr-10 py-3.5 focus:ring-1 focus:ring-[#E07A5F] focus:border-[#E07A5F] transition-all cursor-pointer font-bold uppercase text-[10px] tracking-widest shadow-sm">
                <option value="all">Todas las Sedes Activas</option>
                @foreach($sucursalesSelector as $suc)
                    <option value="{{ $suc->id }}">{{ $suc->nombre }} {{ !$suc->activo ? '(Inactiva)' : '' }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full md:w-auto min-w-[180px] relative z-10">
            <label class="block text-[9px] font-black text-[#8B8175] uppercase tracking-widest mb-2 pl-1">Fecha Inicio</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#E07A5F]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <input type="text" x-ref="startPicker" value="{{ $startDate }}" readonly
                    class="block w-full pl-10 pr-4 py-3 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] focus:ring-1 focus:ring-[#E07A5F] focus:border-[#E07A5F] transition-all font-bold text-[11px] uppercase tracking-wider cursor-pointer shadow-sm">
            </div>
        </div>

        <div class="w-full md:w-auto min-w-[180px] relative z-10">
            <label class="block text-[9px] font-black text-[#8B8175] uppercase tracking-widest mb-2 pl-1">Fecha Fin</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#E07A5F]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <input type="text" x-ref="endPicker" value="{{ $endDate }}" readonly
                    class="block w-full pl-10 pr-4 py-3 bg-[#FDFBF7] border border-[#2C241B]/10 rounded-xl text-[#2C241B] focus:ring-1 focus:ring-[#E07A5F] focus:border-[#E07A5F] transition-all font-bold text-[11px] uppercase tracking-wider cursor-pointer shadow-sm">
            </div>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Sales -->
        <div class="card-light rounded-[24px] p-6 relative group hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#E07A5F]/10 border border-[#E07A5F]/20 flex items-center justify-center text-[#E07A5F]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <p class="text-[#8B8175] text-[9px] font-black uppercase tracking-widest mb-1">Ingresos Totales</p>
            <h3 class="font-serif-elegant text-3xl font-bold text-[#2C241B] tracking-tight leading-none mb-3">$ {{ number_format($metrics['ventas_totales'], 0, ',', '.') }}</h3>
            <p class="text-[9px] text-[#5C5246] font-bold uppercase tracking-wider border-t border-[#2C241B]/10 pt-3">{{ $sucursalFilter === 'all' ? 'Consolidado todas las sedes' : 'Sede individual' }}</p>
        </div>

        <!-- Total Orders -->
        <div class="card-light rounded-[24px] p-6 relative group hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#81B29A]/10 border border-[#81B29A]/20 flex items-center justify-center text-[#81B29A]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
            </div>
            <p class="text-[#8B8175] text-[9px] font-black uppercase tracking-widest mb-1">Volumen de Pedidos</p>
            <h3 class="font-serif-elegant text-3xl font-bold text-[#2C241B] tracking-tight leading-none mb-3">{{ number_format($metrics['total_pedidos'], 0, ',', '.') }}</h3>
            <p class="text-[9px] text-[#5C5246] font-bold uppercase tracking-wider border-t border-[#2C241B]/10 pt-3">Comandas Exitosas</p>
        </div>

        <!-- Average Ticket -->
        <div class="card-light rounded-[24px] p-6 relative group hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#3D5A80]/10 border border-[#3D5A80]/20 flex items-center justify-center text-[#3D5A80]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                </div>
            </div>
            <p class="text-[#8B8175] text-[9px] font-black uppercase tracking-widest mb-1">Ticket Promedio</p>
            <h3 class="font-serif-elegant text-3xl font-bold text-[#2C241B] tracking-tight leading-none mb-3">$ {{ number_format($metrics['ticket_promedio'], 0, ',', '.') }}</h3>
            <p class="text-[9px] text-[#5C5246] font-bold uppercase tracking-wider border-t border-[#2C241B]/10 pt-3">Ingreso Medio por Pedido</p>
        </div>

        <!-- Active Branches -->
        <div class="card-light rounded-[24px] p-6 relative group hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#D97706]/10 border border-[#D97706]/20 flex items-center justify-center text-[#D97706]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <p class="text-[#8B8175] text-[9px] font-black uppercase tracking-widest mb-1">Operatividad</p>
            <h3 class="font-serif-elegant text-3xl font-bold text-[#2C241B] tracking-tight leading-none mb-3">{{ $metrics['sucursales_activas'] }}</h3>
            <p class="text-[9px] text-[#5C5246] font-bold uppercase tracking-wider border-t border-[#2C241B]/10 pt-3">Sedes en Servicio</p>
        </div>
    </div>

    <!-- Comparative Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Table: Sales Comparison -->
        <div class="card-light rounded-[32px] overflow-hidden">
            <div class="px-8 py-6 border-b border-[#2C241B]/10 bg-[#FDFBF7]">
                <h3 class="font-serif-elegant text-xl font-bold text-[#2C241B] tracking-tight">Leaderboard de Sedes</h3>
                <p class="text-[#5C5246] text-[10px] uppercase font-bold tracking-widest mt-1">Clasificación por volumen de ventas</p>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10">Rank</th>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10">Sede</th>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-right">Facturación</th>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-center">Impacto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sucursalesData as $index => $sucursal)
                            <tr class="bg-white hover:bg-[#FDFBF7] transition-colors rounded-xl">
                                <td class="px-6 py-4 rounded-l-xl">
                                    <div class="flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black shadow-sm
                                        @if($index === 0) bg-gradient-to-br from-[#E07A5F] to-[#D97706] text-white shadow-sm
                                        @elseif($index === 1) bg-gradient-to-br from-[#5C5246] to-[#8B8175] text-white
                                        @elseif($index === 2) bg-gradient-to-br from-[#D97706] to-[#92400E] text-white
                                        @else bg-[#FDFBF7] text-[#8B8175] border border-[#2C241B]/10
                                        @endif"
                                    >
                                        {{ $index + 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-[#2C241B] text-xs tracking-wider uppercase">{{ $sucursal['nombre'] }}</p>
                                    <div class="inline-flex items-center gap-1.5 mt-1">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $sucursal['activo'] ? 'bg-emerald-500 shadow-sm' : 'bg-rose-500' }}"></div>
                                        <span class="text-[7px] text-[#8B8175] font-black uppercase tracking-widest">{{ $sucursal['activo'] ? 'En Servicio' : 'Inactiva' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-black text-[#2C241B] text-sm tracking-tight">$ {{ number_format($sucursal['ventas'], 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 rounded-r-xl">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="font-black text-[10px] text-[#E07A5F]">{{ number_format($sucursal['porcentaje_ventas'], 1, ',', '.') }}%</span>
                                        <div class="w-20 h-1.5 bg-[#FDFBF7] rounded-full overflow-hidden shadow-sm">
                                            <div class="h-full bg-gradient-to-r from-[#D97706] to-[#E07A5F] rounded-full" style="width: {{ $sucursal['porcentaje_ventas'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-[#8B8175] text-xs font-bold uppercase tracking-widest">Sin datos disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table: Top Products -->
        <div class="card-light rounded-[32px] overflow-hidden">
            <div class="px-8 py-6 border-b border-[#2C241B]/10 bg-[#FDFBF7]">
                <h3 class="font-serif-elegant text-xl font-bold text-[#2C241B] tracking-tight">Menú Estrella</h3>
                <p class="text-[#5C5246] text-[10px] uppercase font-bold tracking-widest mt-1">Top 5 productos más vendidos</p>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="w-full text-left border-separate border-spacing-y-2">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10">Rank</th>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10">Ítem</th>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-center">Salidas</th>
                            <th class="px-6 py-3 text-[8px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-right">Recaudo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $index => $producto)
                            <tr class="bg-white hover:bg-[#FDFBF7] transition-colors rounded-xl">
                                <td class="px-6 py-4 rounded-l-xl">
                                    <div class="flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black shadow-sm
                                        @if($index === 0) bg-gradient-to-br from-[#E07A5F] to-[#D97706] text-white shadow-sm
                                        @elseif($index === 1) bg-gradient-to-br from-[#5C5246] to-[#8B8175] text-white
                                        @elseif($index === 2) bg-gradient-to-br from-[#D97706] to-[#92400E] text-white
                                        @else bg-[#FDFBF7] text-[#8B8175] border border-[#2C241B]/10
                                        @endif"
                                    >
                                        {{ $index + 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#FDFBF7] border border-[#2C241B]/10 flex items-center justify-center text-[#E07A5F]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <p class="font-bold text-[#2C241B] text-xs tracking-wider uppercase">{{ $producto->nombre_producto }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-black text-[#2C241B] text-xs bg-[#FDFBF7] border border-[#2C241B]/10 px-3 py-1 rounded-md">{{ $producto->cantidad_vendida }}</span>
                                </td>
                                <td class="px-6 py-4 text-right rounded-r-xl">
                                    <span class="font-black text-[#E07A5F] text-sm tracking-tight">$ {{ number_format($producto->total_ventas, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-[#8B8175] text-xs font-bold uppercase tracking-widest">Sin registros de ventas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Table: Branch Comparison Detail -->
    <div class="card-light rounded-[32px] overflow-hidden mb-8">
        <div class="px-8 py-6 border-b border-[#2C241B]/10 bg-[#FDFBF7]">
            <h3 class="font-serif-elegant text-xl font-bold text-[#2C241B] tracking-tight">Distribución Operativa</h3>
            <p class="text-[#5C5246] text-[10px] uppercase font-bold tracking-widest mt-1">Análisis detallado de carga por sucursal</p>
        </div>
        <div class="overflow-x-auto p-2">
            <table class="w-full text-left border-separate border-spacing-y-2">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[9px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10">Sede Operativa</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-right">Ingreso Bruto</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-center">Volumen Pedidos</th>
                        <th class="px-6 py-4 text-[9px] font-black text-[#8B8175] uppercase tracking-widest border-b border-[#2C241B]/10 text-center">Cuota de Red</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sucursalesData as $sucursal)
                        <tr class="bg-white hover:bg-[#FDFBF7] transition-colors rounded-xl group">
                            <td class="px-6 py-5 rounded-l-xl border-l-4 {{ $sucursal['activo'] ? 'border-transparent group-hover:border-[#E07A5F]' : 'border-[#E07A5F]/20' }} transition-colors">
                                <p class="font-bold text-[#2C241B] text-sm tracking-wider uppercase">{{ $sucursal['nombre'] }}</p>
                                <div class="inline-flex items-center gap-1.5 mt-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $sucursal['activo'] ? 'bg-emerald-500 shadow-sm' : 'bg-rose-500' }}"></div>
                                    <span class="text-[8px] text-[#8B8175] font-black uppercase tracking-widest">{{ $sucursal['activo'] ? 'En Servicio' : 'Fuera de Servicio' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span class="font-black text-[#2C241B] text-base tracking-tight">$ {{ number_format($sucursal['ventas'], 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="font-black text-[#2C241B] text-xs bg-[#FDFBF7] border border-[#2C241B]/10 px-4 py-1.5 rounded-lg">{{ number_format($sucursal['pedidos'], 0, ',', '.') }} cmd</span>
                            </td>
                            <td class="px-6 py-5 text-center rounded-r-xl">
                                <span class="font-black text-[#E07A5F] text-xs bg-[#E07A5F]/10 border border-[#E07A5F]/20 px-4 py-1.5 rounded-lg shadow-sm">
                                    {{ number_format($sucursal['porcentaje_dividido'], 2, ',', '.') }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-[#8B8175] text-xs font-bold uppercase tracking-widest">Sin datos operativos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
