@php
    $estadoClase = match($pedido->estado ?? '') {
        'ASIGNADO' => 'dom-badge-asignado',
        'EN_CAMINO' => 'dom-badge-encamino',
        'ENTREGADO' => 'dom-badge-recogido', // Reusing this badge color for delivered/done
        default => 'dom-badge-asignado'
    };
    
    // Status button text
    $btnAccion = match($pedido->estado ?? '') {
        'PENDIENTE_PAGO', 'CREADO', 'EN_PREPARACION', 'ASIGNADO' => 'Esperando prep.',
        'LISTO' => 'Iniciar Entrega',
        'EN_CAMINO' => 'Confirmar Entrega',
        default => 'Acción'
    };

    // Is the button disabled?
    $isBtnDisabled = false;
    $disableReason = '';

    if (in_array($pedido->estado ?? '', ['PENDIENTE_PAGO', 'CREADO', 'EN_PREPARACION', 'ASIGNADO'])) {
        $isBtnDisabled = true;
    } elseif (($pedido->estado ?? '') === 'EN_CAMINO') {
        if (!isset($pedido->distancia_km) || $pedido->distancia_km > 0.05) {
            $isBtnDisabled = true;
            $disableReason = 'A ' . ($pedido->distancia_km ? ($pedido->distancia_km * 1000) . 'm' : 'lejos');
        }
    }
    
    $nombreCliente = $pedido->sesionCliente->nombre_cliente ?? 'Cliente';
    $whatsAppMensaje = "Hola " . $nombreCliente . ", soy tu domiciliario de Cafetería Huila. ¡Tu pedido " . $pedido->short_id . " está en camino!";
@endphp

<div class="dom-card flex flex-col transition-all duration-300">
    <!-- Header -->
    <div class="p-4 flex items-start justify-between border-b border-dom-border bg-black/20">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center shrink-0 text-[#00A8B5]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">{{ $pedido->short_id ?? 'N/A' }}</h3>
                <p class="text-[11px] text-dom-text-muted mt-0.5">Asignado: {{ $pedido->actualizado_en ? $pedido->actualizado_en->format('g:i A') : '--:--' }}</p>
            </div>
        </div>
        <span class="dom-badge {{ $estadoClase }} shadow-sm">
            {{ str_replace('_', ' ', $pedido->estado ?? 'ASIGNADO') }}
        </span>
    </div>

    <!-- Cliente Info -->
    <div class="p-4 pb-2">
        <h4 class="font-semibold text-white">{{ $nombreCliente }}</h4>
        <p class="text-sm text-dom-text-muted mt-1 leading-snug">{{ $pedido->direccion_entrega ?? 'Dirección no disponible' }}</p>
        @if(!empty($pedido->sesionCliente->notas))
            <p class="text-xs text-[#00A8B5] mt-1.5 font-medium flex items-start gap-1">
                <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $pedido->sesionCliente->notas }}
            </p>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="px-4 py-3 grid grid-cols-3 gap-2">
        <button @click="actionCall('{{ $pedido->sesionCliente->telefono_cliente ?? '' }}')" class="dom-btn-secondary text-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            Llamar
        </button>
        <button @click="actionWhatsApp('{{ $pedido->sesionCliente->telefono_cliente ?? '' }}', '{{ $whatsAppMensaje }}')" class="dom-btn-secondary text-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            WhatsApp
        </button>
        <button @click="actionNavigate('{{ str_replace(["\r", "\n"], ' ', $pedido->direccion_entrega ?? '') }}')" class="dom-btn-secondary text-xs text-green-400 border-green-500/20 hover:bg-green-500/10">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            Ir
        </button>
    </div>

    <!-- Products -->
    <div class="px-4 py-3 border-t border-dom-border bg-black/10">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-dom-text-muted">Productos</span>
            <span class="text-xs text-dom-text-muted">{{ count($pedido->detalles ?? []) }} ítems</span>
        </div>
        <ul class="space-y-1.5">
            @foreach($pedido->detalles ?? [] as $producto)
            <li class="flex items-start justify-between text-sm">
                <span class="text-white"><span class="font-bold text-dom-text-muted mr-1">{{ $producto->cantidad }}x</span> {{ $producto->nombre_producto }}</span>
                <span class="text-dom-text-muted">${{ number_format($producto->subtotal, 0, ',', '.') }}</span>
            </li>
            @endforeach
        </ul>

        @if(!empty($pedido->motivo_cancelacion))
        <div class="mt-3 p-2.5 bg-amber-500/10 rounded-lg border border-amber-500/20 flex gap-2">
            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-xs text-amber-400/90 font-medium leading-snug">{{ $pedido->motivo_cancelacion }}</p>
        </div>
        @endif
    </div>

    <!-- Metrics & Total -->
    <div class="px-4 py-3 flex items-end justify-between">
        <div class="flex gap-4">
            <div class="flex items-center gap-1.5 text-xs text-dom-text-muted">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                ~{{ $pedido->tiempo_min ?? '--' }} min
            </div>
            <div class="flex items-center gap-1.5 text-xs text-dom-text-muted">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                {{ $pedido->distancia_km ?? '--' }} km
            </div>
        </div>
        <div class="text-right">
            <div class="text-lg font-black text-white">${{ number_format($pedido->total ?? 0, 0, ',', '.') }}</div>
            <div class="text-[10px] text-dom-text-muted uppercase tracking-wider">{{ str_replace('_', ' ', $pedido->metodo_pago ?? '') }}</div>
        </div>
    </div>

    <!-- Actions Footer -->
    <div class="p-4 pt-2 border-t border-dom-border flex gap-3">
        <button @click="openProblemaModal('{{ $pedido->id ?? '' }}')" class="dom-btn-danger-outline shrink-0 !py-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="sr-only">Problema</span>
        </button>
        <button @click="if(!{{ $isBtnDisabled ? 'true' : 'false' }}) openConfirmModal('{{ $pedido->id ?? '' }}', '{{ $pedido->estado ?? '' }}')" 
                class="dom-btn-primary flex-grow group shadow-[0_0_15px_rgba(0,168,181,0.2)] {{ $isBtnDisabled ? 'opacity-50 cursor-not-allowed !bg-gray-700 !shadow-none !border-gray-600 !text-gray-400' : '' }}"
                {{ $isBtnDisabled ? 'disabled' : '' }}>
            @if(!$isBtnDisabled)
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @endif
            {{ $isBtnDisabled && $disableReason ? $disableReason : $btnAccion }}
        </button>
    </div>
</div>
