<div class="w-full pb-10">
    {{-- Hero Section Cafeteria - Full Width --}}
    <div class="relative h-72 rounded-[32px] overflow-hidden mb-8 shadow-2xl border border-[#292524]">
        <img src="{{ asset('cafeteria_branding_hero_1778531783781.png') }}" class="w-full h-full object-cover" alt="Cafeteria Hero">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0F172A] via-[#0F172A]/40 to-transparent flex flex-col justify-center px-10 items-start">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#A85507]/20 border border-[#A85507]/30 text-[#A85507] text-[9px] font-black uppercase tracking-widest mb-4">
                <div class="w-1 h-1 rounded-full bg-[#A85507] animate-pulse"></div>
                Sistema Operativo Activo
            </div>
            <h1 class="text-4xl font-black text-white leading-tight mb-3 tracking-tighter uppercase">Bienvenido,<br><span class="text-[#A85507]">{{ auth()->user()->nombre }}</span></h1>
            <p class="text-stone-400 text-base max-w-md font-medium">Gestión inteligente para tu red de cafeterías.</p>
        </div>
    </div>

    {{-- Stats Grid Fluid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @php
            $statsData = [
                [
                    'label' => 'Sucursales', 
                    'value' => sprintf('%02d', $stats['total_sedes'] ?? 0), 
                    'sub' => ($stats['sedes_activas'] ?? 0) . ' Activas / ' . ($stats['sedes_inactivas'] ?? 0) . ' Inactivas',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 
                    'color' => 'text-[#A85507] bg-[#A85507]/10'
                ],
                [
                    'label' => 'Ventas Hoy', 
                    'value' => '$' . number_format($stats['ventas_hoy'] ?? 0, 0, ',', '.'), 
                    'sub' => 'Ventas en efectivo y nequi',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 
                    'color' => 'text-emerald-400 bg-emerald-500/10'
                ],
                [
                    'label' => 'Pedidos Hoy', 
                    'value' => sprintf('%02d', $stats['pedidos_hoy'] ?? 0), 
                    'sub' => 'Pedidos registrados hoy',
                    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 
                    'color' => 'text-blue-400 bg-blue-500/10'
                ],
                [
                    'label' => 'Clientes Hoy', 
                    'value' => sprintf('%02d', $stats['clientes_hoy'] ?? 0), 
                    'sub' => 'Sesiones de clientes hoy',
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 
                    'color' => 'text-amber-400 bg-amber-500/10'
                ],
            ];
        @endphp

        @foreach($statsData as $stat)
            <div class="bg-[#1C1917] rounded-[24px] p-6 border border-[#292524] hover:border-[#A85507]/30 transition-all group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $stat['color'] }} group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                            </svg>
                        </div>
                        <span class="text-stone-600 text-[9px] font-black uppercase tracking-widest">{{ $stat['label'] }}</span>
                    </div>
                    <h3 class="text-3xl font-black text-white tracking-tighter leading-none">{{ $stat['value'] }}</h3>
                </div>
                <p class="text-[9px] text-stone-500 font-bold uppercase tracking-wider mt-4">{{ $stat['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Performance Chart --}}
    <div class="bg-[#1C1917] rounded-[32px] border border-[#292524] p-8 shadow-2xl relative overflow-hidden">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl font-black text-white uppercase tracking-tight">Rendimiento Semanal</h2>
                <p class="text-stone-600 font-bold text-xs">Ventas últimos 7 días</p>
            </div>
            <a href="{{ route('gerente.reportes-globales') }}" wire:navigate class="px-5 py-2.5 bg-[#A85507] hover:bg-[#78350F] text-white font-black rounded-xl transition-all shadow-lg uppercase text-[9px] tracking-widest">
                Detalles
            </a>
        </div>
        
        <div class="h-48 flex items-end gap-3">
            @foreach($weeklySales as $day)
                <div class="flex-1 bg-stone-900/40 border border-[#292524] rounded-t-xl h-full relative group cursor-pointer overflow-hidden flex flex-col justify-end" title="{{ $day['label'] }} ({{ $day['date_str'] }}): ${{ number_format($day['sales'], 0, ',', '.') }}">
                    {{-- Tooltip overlay on hover --}}
                    <div class="absolute inset-x-0 top-2 text-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10">
                        <span class="bg-[#1E293B] border border-stone-800 text-white font-bold text-[9px] px-2 py-1 rounded shadow-lg uppercase tracking-wider">
                            ${{ number_format($day['sales'], 0, ',', '.') }}
                        </span>
                    </div>
                    {{-- Bar fill --}}
                    <div class="w-full bg-gradient-to-t from-[#A85507] to-[#D97706] transition-all duration-700 rounded-t-lg" style="height: {{ $day['height'] }}%"></div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between mt-4 px-1 text-stone-600 text-[9px] font-black uppercase tracking-widest">
            @foreach($weeklySales as $day)
                <span class="flex-1 text-center font-bold text-stone-500">
                    {{ $day['label'] }}
                    <small class="text-[7px] text-stone-600 block">{{ $day['date_str'] }}</small>
                </span>
            @endforeach
        </div>
    </div>
</div>