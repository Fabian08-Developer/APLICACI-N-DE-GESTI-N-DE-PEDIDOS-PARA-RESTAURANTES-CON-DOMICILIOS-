<div class="px-4 py-6 space-y-4">

    <!-- Tarjeta de Perfil -->
    <div class="dom-card p-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-[#00A8B5] text-white flex items-center justify-center text-xl font-bold shadow-lg">
            {{ substr($domiciliario['nombre'] ?? 'D', 0, 1) }}
        </div>
        <div class="flex-grow">
            <h2 class="font-bold text-white text-lg flex items-center gap-2">
                {{ $domiciliario['nombre'] ?? 'Nombre Apellido' }}
                <svg class="w-4 h-4 text-[#00A8B5]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </h2>
            <div class="flex items-center gap-3 mt-1">
                <span class="text-xs text-dom-text-muted">{{ $domiciliario['codigo'] ?? 'DOM000' }}</span>
                <div class="flex items-center text-xs font-medium text-amber-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    {{ $domiciliario['calificacion'] ?? '5.0' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Vehículo -->
    <div class="dom-card p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-black/30 flex items-center justify-center text-dom-text-muted shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-xs font-bold text-dom-text-muted uppercase tracking-wider mb-0.5">Vehículo</h3>
            <div class="text-white font-medium">{{ $domiciliario['vehiculo'] ?? 'Vehículo' }} <span class="text-dom-text-muted font-normal ml-1">Placa: {{ $domiciliario['placa'] ?? '---' }}</span></div>
        </div>
    </div>

    <!-- Zona -->
    <div class="dom-card p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-black/30 flex items-center justify-center text-[#00A8B5] shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-xs font-bold text-dom-text-muted uppercase tracking-wider mb-0.5">Zona Asignada</h3>
            <div class="text-white font-medium">{{ $domiciliario['zona'] ?? 'Sin asignar' }}</div>
        </div>
    </div>

    <!-- Efectivo del Día -->
    <div class="dom-card p-5">
        <h3 class="text-xs font-bold text-dom-text-muted uppercase tracking-wider mb-4 text-[#00A8B5]">Liquidación de Hoy</h3>
        <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div class="bg-black/20 p-3 rounded-lg border border-dom-border text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-green-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="text-xl font-bold text-green-400">${{ number_format($estadisticas['dia']['efectivo_recibido'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-dom-text-muted mt-1">Efectivo Recibido</div>
            </div>
            
            <div class="bg-black/20 p-3 rounded-lg border border-dom-border text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="text-xl font-bold text-amber-400">${{ number_format($estadisticas['dia']['a_liquidar'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-dom-text-muted mt-1">A Liquidar (Al Restaurante)</div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Mes -->
    <div class="dom-card p-5">
        <h3 class="text-xs font-bold text-dom-text-muted uppercase tracking-wider mb-4">Estadísticas del mes</h3>
        
        <div class="grid grid-cols-2 gap-y-6 gap-x-4">
            <div class="bg-black/20 p-3 rounded-lg border border-dom-border text-center">
                <div class="text-xl font-bold text-white">{{ $estadisticas['mes']['entregas'] ?? 0 }}</div>
                <div class="text-[10px] text-dom-text-muted mt-1">Entregas</div>
            </div>
            
            <div class="bg-black/20 p-3 rounded-lg border border-dom-border text-center">
                <div class="text-xl font-bold text-green-400">${{ number_format($estadisticas['mes']['ganancias'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-dom-text-muted mt-1">Ganancias</div>
            </div>
            
            <div class="bg-black/20 p-3 rounded-lg border border-dom-border text-center">
                <div class="text-lg font-bold text-white">{{ $estadisticas['mes']['tiempo_promedio'] ?? '0 min' }}</div>
                <div class="text-[10px] text-dom-text-muted mt-1">Tiempo Prom.</div>
            </div>
            
            <div class="bg-black/20 p-3 rounded-lg border border-dom-border text-center">
                <div class="text-lg font-bold text-amber-400">{{ $estadisticas['mes']['efectividad'] ?? '0%' }}</div>
                <div class="text-[10px] text-dom-text-muted mt-1">Efectividad</div>
            </div>
        </div>
    </div>

    <!-- Acciones de cuenta -->
    <div class="pt-2 space-y-3">
        <button class="w-full flex items-center justify-between p-4 dom-card hover:bg-dom-surface-hover transition-colors">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-dom-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-sm font-medium text-white">Configuración</span>
            </div>
            <svg class="w-4 h-4 text-dom-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>

        <form method="POST" action="{{ route('logout') ?? '#' }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-between p-4 rounded-xl border border-dom-danger/30 bg-dom-danger/5 hover:bg-dom-danger/10 transition-colors">
                <div class="flex items-center gap-3 text-dom-danger">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="text-sm font-medium">Cerrar Sesión</span>
                </div>
                <svg class="w-4 h-4 text-dom-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </form>
    </div>
</div>
