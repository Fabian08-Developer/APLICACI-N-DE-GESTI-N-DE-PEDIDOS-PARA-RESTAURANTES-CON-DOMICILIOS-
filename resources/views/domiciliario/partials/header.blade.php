<header class="sticky top-0 z-40 bg-[#0B1015]/90 backdrop-blur-md border-b border-dom-border px-4 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <!-- Logo / Icon -->
        <div class="w-10 h-10 rounded-full bg-dom-surface flex items-center justify-center text-[#00A8B5] shadow-inner">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <div>
            <h1 class="text-white font-bold leading-tight tracking-tight">{{ $domiciliario['nombre'] ?? 'Cafetería Huila' }}</h1>
            <p class="text-xs text-dom-text-muted">Domiciliario</p>
        </div>
    </div>

    <!-- Toggle Status Button -->
    <button @click="toggleDisponibilidad()" 
            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors duration-300 border"
            :class="disponible ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'">
        <span class="relative flex h-2 w-2">
            <span :class="disponible ? 'bg-green-400' : 'bg-amber-400'" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
            <span :class="disponible ? 'bg-green-500' : 'bg-amber-500'" class="relative inline-flex rounded-full h-2 w-2"></span>
        </span>
        <span x-text="disponible ? 'Disponible' : 'Ocupado'"></span>
    </button>
</header>
