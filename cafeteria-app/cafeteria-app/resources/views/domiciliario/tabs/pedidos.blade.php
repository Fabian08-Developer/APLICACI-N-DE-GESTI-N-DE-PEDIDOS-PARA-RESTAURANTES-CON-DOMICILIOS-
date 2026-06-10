<div class="px-4 py-6 space-y-6">
    
    <!-- Estadísticas Rápidas -->
    <div class="grid grid-cols-3 gap-3">
        <!-- Pendientes -->
        <div class="dom-card p-3 flex flex-col items-center justify-center text-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-[#00A8B5]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <svg class="w-6 h-6 text-[#00A8B5] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span class="text-xl font-bold text-white">{{ $estadisticas['dia']['pendientes'] ?? 0 }}</span>
            <span class="text-[10px] text-dom-text-muted mt-1">Pendientes</span>
        </div>
        
        <!-- Distancia -->
        <div class="dom-card p-3 flex flex-col items-center justify-center text-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <svg class="w-6 h-6 text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <span class="text-xl font-bold text-white">{{ $estadisticas['dia']['km_recorrer'] ?? '--' }} <span class="text-sm font-medium text-dom-text-muted">km</span></span>
            <span class="text-[10px] text-dom-text-muted mt-1">Por recorrer</span>
        </div>
        
        <!-- Por Cobrar -->
        <div class="dom-card p-3 flex flex-col items-center justify-center text-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-green-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <svg class="w-6 h-6 text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-lg font-bold text-white">${{ number_format($estadisticas['dia']['por_cobrar'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-[10px] text-dom-text-muted mt-1">Por cobrar</span>
        </div>
    </div>

    <!-- Título de sección -->
    <div class="flex items-center justify-between mt-8 mb-2">
        <h2 class="text-sm font-bold tracking-wider text-dom-text-muted uppercase">Mis Pedidos ({{ count($pedidos ?? []) }})</h2>
    </div>

    <!-- Lista de Pedidos -->
    <div class="space-y-4">
        @forelse($pedidos ?? [] as $pedido)
            @include('domiciliario.components.pedido-card', ['pedido' => $pedido])
        @empty
            <div class="dom-card p-8 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-dom-surface-hover flex items-center justify-center mb-4 text-dom-text-muted">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-white font-medium mb-1">¡Todo al día!</h3>
                <p class="text-sm text-dom-text-muted">No tienes pedidos pendientes en este momento.</p>
            </div>
        @endforelse
    </div>
</div>
