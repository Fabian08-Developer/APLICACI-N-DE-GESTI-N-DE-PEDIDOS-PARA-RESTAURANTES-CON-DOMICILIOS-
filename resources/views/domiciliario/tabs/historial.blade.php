<div class="px-4 py-6 space-y-6">
    
    <!-- Resumen del día -->
    <div class="dom-card p-5">
        <h2 class="text-xs font-bold tracking-wider text-dom-text-muted uppercase mb-4">Resumen del día</h2>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-2xl font-black text-white">{{ count($historial ?? []) }}</div>
                <div class="text-[10px] text-dom-text-muted mt-0.5">Entregas completadas</div>
            </div>
            
            <div class="text-right">
                <div class="text-2xl font-black text-green-400">
                    ${{ number_format($estadisticas['dia']['ganancias'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-dom-text-muted mt-0.5">Ganancias del día (envíos)</div>
            </div>
        </div>
    </div>

    <!-- Lista de entregas -->
    <div>
        <h2 class="text-xs font-bold tracking-wider text-dom-text-muted uppercase mb-3">Entregas de hoy</h2>
        
        <div class="space-y-3">
            @forelse($historial ?? [] as $entrega)
            <div class="dom-card p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-500/10 text-green-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-sm">{{ $entrega->sesionCliente->nombre_cliente ?? 'Cliente' }}</h4>
                        <div class="text-[10px] text-dom-text-muted mt-0.5">{{ $entrega->short_id }} • {{ $entrega->entregado_en ? $entrega->entregado_en->format('g:i A') : '--' }}</div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="font-bold text-white text-sm">${{ number_format($entrega->total, 0, ',', '.') }}</div>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-dom-text-muted text-sm">No has completado entregas hoy.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
