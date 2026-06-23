{{--
    Partial: cocina/partials/card-pedido.blade.php
    Carpeta:  resources/views/cocina/partials/

    Parámetros esperados:
        'pedido'    => $pedido
        'clase'     => 'state-new' | 'state-cooking' | 'state-prep' | 'state-ready'
        'btnLabel'  => 'Iniciar' | 'Preparando' | 'Listo' | 'Entregar'
        'btnClase'  => 'btn-start' | 'btn-prep' | 'btn-ready' | 'btn-deliver'
        'estadoSig' => 'EN_COCINA' | 'EN_PREPARACION' | 'LISTO' | 'ENTREGADO'
--}}
@php
    $creadoEn = $pedido->creado_en ?? $pedido->created_at ?? $pedido->actualizado_en ?? now();
    $minutos  = (int) $creadoEn->diffInMinutes(now());
    $timerCls = $minutos >= 15 ? 'timer-urgent'
              : ($minutos >= 10 ? 'timer-warn' : 'timer-ok');
@endphp

<div class="order-card {{ $clase }}" id="card-{{ $pedido->id }}">

    <!-- Header Premium -->
    <div class="order-card-header">
        <div style="display:flex; align-items:center; gap: 0.6rem;">
            <span style="background: rgba(255,255,255,0.1); color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 800; font-size: 0.85rem; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.05);">#{{ $pedido->short_id }}</span>
            <span style="color: #34D399; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display:flex; align-items:center; gap:0.25rem;">
                @if(strtolower($pedido->tipo) === 'domicilio')
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Domicilio
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="14" width="18" height="8" rx="2"/><path d="M3 14V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8"/></svg>
                    Mesa {{ $pedido->sesionMesa?->mesa?->numero ?? '—' }}
                @endif
            </span>
        </div>
        
        <div class="order-timer {{ $timerCls }}" data-creado="{{ $creadoEn->toIso8601String() }}">
            <span style="font-size: 0.6rem; opacity: 0.8; font-weight: 600; margin-right: 0.2rem;">HACE</span><span class="timer-value">{{ $minutos }}</span> min
        </div>
    </div>

    <!-- Contenido del pedido -->
    <div class="order-items">
        @foreach($pedido->detalles as $detalle)
        <div class="order-item-group">
            <div class="order-item-main">
                <span class="order-item-name">{{ $detalle->nombre_producto ?? $detalle->producto?->nombre ?? '—' }}</span>
                <span class="order-item-qty">x{{ $detalle->cantidad }}</span>
            </div>
            
            @if($detalle->variantes_elegidas && count($detalle->variantes_elegidas) > 0)
                <div class="order-item-variants">
                    @foreach($detalle->variantes_elegidas as $k => $v)
                        <span class="order-variant">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            {{ is_array($v) ? ($v['nombre'] ?? '').': '.($v['opcion'] ?? '') : $k.': '.$v }}
                        </span>
                    @endforeach
                </div>
            @endif
            
            @if($detalle->adiciones_elegidas && count($detalle->adiciones_elegidas) > 0)
                <div class="order-item-additions">
                    @foreach($detalle->adiciones_elegidas as $ad)
                        <span class="order-addition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            {{ is_array($ad) ? ($ad['nombre'] ?? '') : $ad }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if($detalle->notas)
                <div class="order-item-notes">
                    <div style="font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.1rem; color: #F87171;">Nota del cliente:</div>
                    {{ $detalle->notas }}
                </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Footer -->
    <div class="order-footer">
        @if(!empty($estadoSig))
        <button class="btn-action {{ $btnClase }}" onclick="cambiarEstado(this, @js($pedido->id), '{{ $estadoSig }}')">
            {{ $btnLabel }}
        </button>
        @else
        <div style="text-align: center; color: var(--text-dim); font-size: 0.85rem; padding: 0.5rem 0; font-style: italic;">
            {{ strtolower($pedido->tipo) === 'domicilio' ? 'Esperando al domiciliario...' : 'Esperando al mesero...' }}
        </div>
        @endif
    </div>

</div>
