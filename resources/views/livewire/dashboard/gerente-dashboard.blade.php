<div class="w-full pb-10" style="font-family: 'Geist', sans-serif;">
    <style>
        .font-serif-elegant { font-family: 'Playfair Display', serif; }
        .card-light {
            background: #FFFFFF;
            border: 1px solid rgba(44,36,27,0.08);
            box-shadow: 0 4px 6px -1px rgba(44,36,27,0.05), 0 2px 4px -1px rgba(44,36,27,0.03);
        }
        .text-terracotta { color: #E07A5F; }
        .bg-terracotta-glow { background: radial-gradient(circle, rgba(224, 122, 95, 0.08) 0%, transparent 70%); }
    </style>

    {{-- Hero Section Executive --}}
    <div class="relative h-80 rounded-[32px] overflow-hidden mb-10 shadow-sm border border-[#2C241B]/10">
        <img src="{{ asset('images/gerente_hero_espresso.png') }}" class="w-full h-full object-cover opacity-60 mix-blend-overlay" alt="Executive Coffee Hero" onerror="this.src='{{ asset('images/cafeteria_branding_hero_1778531783781.png') }}'">
        <div class="absolute inset-0 bg-gradient-to-r from-[#FDFBF7] via-[#FDFBF7]/80 to-transparent"></div>
        <div class="absolute inset-0 flex flex-col justify-center px-12 items-start">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 rounded-full bg-[#FFFFFF]/90 border border-[#E07A5F]/30 text-[#E07A5F] text-[10px] font-black uppercase tracking-widest mb-6 shadow-sm">
                <div class="w-1.5 h-1.5 rounded-full bg-[#E07A5F] animate-pulse shadow-[0_0_8px_#E07A5F]"></div>
                Sistema Operativo en Vivo
            </div>
            <h1 class="font-serif-elegant text-5xl font-bold text-[#2C241B] leading-tight mb-4 tracking-tight drop-shadow-sm">
                Resumen Ejecutivo,<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#E07A5F] to-[#D97706]">{{ auth()->user()->nombre }}</span>
            </h1>
            <p class="text-[#5C5246] text-sm max-w-lg font-medium tracking-wide leading-relaxed border-l-2 border-[#E07A5F] pl-4">
                El salón está activo. Aquí tienes el pulso en tiempo real de todas tus sedes y el rendimiento operativo del día.
            </p>
        </div>
    </div>

    {{-- Stats Grid Executive --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        @php
            $statsData = [
                [
                    'label' => 'Ventas del Día', 
                    'value' => '$' . number_format($stats['ventas_hoy'] ?? 0, 0, ',', '.'), 
                    'trend' => '+14.2%',
                    'trendUp' => true,
                    'sub' => 'Ingresos brutos hoy',
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>', 
                    'color' => 'text-[#E07A5F]',
                    'bgIcon' => 'bg-[#E07A5F]/10 border-[#E07A5F]/20'
                ],
                [
                    'label' => 'Tickets / Pedidos', 
                    'value' => sprintf('%02d', $stats['pedidos_hoy'] ?? 0), 
                    'trend' => '+5.8%',
                    'trendUp' => true,
                    'sub' => 'Comandas procesadas',
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>', 
                    'color' => 'text-[#81B29A]',
                    'bgIcon' => 'bg-[#81B29A]/10 border-[#81B29A]/20'
                ],
                [
                    'label' => 'Flujo de Clientes', 
                    'value' => sprintf('%02d', $stats['clientes_hoy'] ?? 0), 
                    'trend' => '-2.1%',
                    'trendUp' => false,
                    'sub' => 'Mesas atendidas hoy',
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>', 
                    'color' => 'text-[#3D5A80]',
                    'bgIcon' => 'bg-[#3D5A80]/10 border-[#3D5A80]/20'
                ],
                [
                    'label' => 'Estado de Sedes', 
                    'value' => sprintf('%02d', $stats['total_sedes'] ?? 0), 
                    'trend' => '100% Opt.',
                    'trendUp' => true,
                    'sub' => ($stats['sedes_activas'] ?? 0) . ' Activas / ' . ($stats['sedes_inactivas'] ?? 0) . ' Inactivas',
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>', 
                    'color' => 'text-[#D97706]',
                    'bgIcon' => 'bg-[#D97706]/10 border-[#D97706]/20'
                ],
            ];
        @endphp

        @foreach($statsData as $stat)
            <div class="card-light rounded-[24px] p-6 relative overflow-hidden group hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                {{-- Decorative background glow --}}
                <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full blur-3xl opacity-10 {{ str_replace('text-', 'bg-', $stat['color']) }} group-hover:opacity-20 transition-opacity"></div>
                
                <div>
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center border {{ $stat['bgIcon'] }} {{ $stat['color'] }} shadow-sm">
                            {!! $stat['icon'] !!}
                        </div>
                        <div class="inline-flex items-center gap-1 text-[10px] font-black px-2 py-1 rounded-md bg-[#FDFBF7] border border-[#2C241B]/10">
                            @if($stat['trendUp'])
                                <svg class="w-3 h-3 text-[#81B29A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                <span class="text-[#81B29A]">{{ $stat['trend'] }}</span>
                            @else
                                <svg class="w-3 h-3 text-[#E07A5F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                                <span class="text-[#E07A5F]">{{ $stat['trend'] }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="block text-[#8B8175] text-[10px] font-bold uppercase tracking-widest mb-1">{{ $stat['label'] }}</span>
                    <h3 class="font-serif-elegant text-4xl font-bold text-[#2C241B] tracking-tight leading-none">{{ $stat['value'] }}</h3>
                </div>
                <div class="mt-5 pt-4 border-t border-[#2C241B]/10">
                    <p class="text-[10px] text-[#5C5246] font-medium uppercase tracking-wider">{{ $stat['sub'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Performance Chart Executive --}}
    <div class="card-light rounded-[32px] p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-terracotta-glow opacity-30 pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 relative z-10 gap-4">
            <div>
                <div class="inline-flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full bg-[#E07A5F]"></div>
                    <h2 class="text-[10px] font-black text-[#E07A5F] uppercase tracking-widest">Rendimiento Operativo</h2>
                </div>
                <h3 class="font-serif-elegant text-2xl font-bold text-[#2C241B] tracking-tight">Ventas Últimos 7 Días</h3>
            </div>
            <a href="{{ route('gerente.reportes-globales') }}" wire:navigate class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-[#E07A5F] to-[#D97706] hover:from-[#D97706] hover:to-[#E07A5F] text-white font-black rounded-xl transition-all shadow-md hover:shadow-lg uppercase text-[10px] tracking-widest">
                Análisis Detallado
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
        
        <div class="h-56 flex items-end gap-2 sm:gap-4 relative z-10 px-2 border-b border-[#2C241B]/10 pb-4">
            <!-- Grid lines background -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                <div class="w-full h-px bg-[#2C241B]/10"></div>
                <div class="w-full h-px bg-[#2C241B]/10"></div>
                <div class="w-full h-px bg-[#2C241B]/10"></div>
                <div class="w-full h-px bg-[#2C241B]/10"></div>
                <div class="w-full h-px bg-transparent"></div>
            </div>

            @foreach($weeklySales as $day)
                <div class="flex-1 h-full relative group cursor-pointer flex flex-col justify-end items-center z-10" title="{{ $day['label'] }} ({{ $day['date_str'] }}): ${{ number_format($day['sales'], 0, ',', '.') }}">
                    {{-- Tooltip overlay on hover --}}
                    <div class="absolute -top-12 opacity-0 group-hover:opacity-100 transition-all duration-300 pointer-events-none transform group-hover:-translate-y-2">
                        <div class="bg-white border border-[#E07A5F]/40 text-[#E07A5F] font-bold text-[10px] px-3 py-1.5 rounded-lg shadow-md whitespace-nowrap">
                            ${{ number_format($day['sales'], 0, ',', '.') }}
                        </div>
                    </div>
                    
                    {{-- Bar fill premium --}}
                    <div class="w-full max-w-[60px] bg-gradient-to-t from-[#E07A5F]/40 to-[#E07A5F] hover:from-[#E07A5F]/60 hover:to-[#D97706] transition-all duration-500 rounded-t-md shadow-sm relative overflow-hidden" style="height: {{ $day['height'] }}%">
                        <!-- Shine effect -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="flex justify-between mt-4 px-2 text-[#8B8175] text-[9px] font-black uppercase tracking-widest relative z-10">
            @foreach($weeklySales as $day)
                <div class="flex-1 text-center flex flex-col items-center gap-1">
                    <span class="text-[#2C241B]">{{ $day['label'] }}</span>
                    <span class="text-[#8B8175] text-[8px]">{{ $day['date_str'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>