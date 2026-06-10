@section('bottom-nav')
<nav class="fixed bottom-0 left-0 right-0 z-40 bg-[#0B1015]/95 backdrop-blur-xl border-t border-dom-border h-[68px] pb-safe shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
    <div class="max-w-md mx-auto h-full flex items-center justify-around px-2 relative">
        
        <!-- Tab indicator animation -->
        <div class="absolute top-0 h-[2px] bg-[#00A8B5] transition-all duration-300 ease-out"
             :style="`left: ${activeTab === 'pedidos' ? '16.66%' : activeTab === 'historial' ? '50%' : '83.33%'}; transform: translateX(-50%); width: 40px;`">
        </div>

        <!-- Pedidos Tab -->
        <button @click="activeTab = 'pedidos'" class="bottom-nav-item w-1/3" :class="{'active': activeTab === 'pedidos'}">
            <div class="relative mb-1 transition-transform duration-200" :class="{'scale-110': activeTab === 'pedidos'}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                
                @if(($domiciliario['estadisticas']['dia']['pendientes'] ?? 0) > 0)
                <span class="absolute -top-1.5 -right-2 bg-dom-danger text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-dom-bg shadow-sm">
                    {{ $domiciliario['estadisticas']['dia']['pendientes'] }}
                </span>
                @endif
            </div>
            <span class="text-[10px] font-medium tracking-wide">Pedidos</span>
        </button>

        <!-- Historial Tab -->
        <button @click="activeTab = 'historial'" class="bottom-nav-item w-1/3" :class="{'active': activeTab === 'historial'}">
            <div class="relative mb-1 transition-transform duration-200" :class="{'scale-110': activeTab === 'historial'}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <span class="text-[10px] font-medium tracking-wide">Historial</span>
        </button>

        <!-- Perfil Tab -->
        <button @click="activeTab = 'perfil'" class="bottom-nav-item w-1/3" :class="{'active': activeTab === 'perfil'}">
            <div class="relative mb-1 transition-transform duration-200" :class="{'scale-110': activeTab === 'perfil'}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <span class="text-[10px] font-medium tracking-wide">Perfil</span>
        </button>
    </div>
</nav>
@endsection
