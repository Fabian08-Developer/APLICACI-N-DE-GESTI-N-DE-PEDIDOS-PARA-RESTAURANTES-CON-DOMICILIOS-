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

    <div class="order-card-header">
        <div style="display:flex;align-items:center;gap:.5rem">
            <span class="order-id">#{{ $pedido->id }}</span>
            <span class="order-table">
                @if($pedido->tipo === 'DOMICILIO')
                    Domicilio
                @else
                    Mesa {{ $pedido->sesionMesa?->mesa?->numero ?? '—' }}
                @endif
            </span>
        </div>
        <span class="order-timer {{ $timerCls }}"
              data-creado="{{ $creadoEn->toIso8601String() }}">
            {{ $minutos }}min
        </span>
    </div>

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
                            • {{ is_array($v) ? ($v['nombre'] ?? '').': '.($v['opcion'] ?? '') : $k.': '.$v }}
                        </span>
                    @endforeach
                </div>
            @endif
            
            @if($detalle->adiciones_elegidas && count($detalle->adiciones_elegidas) > 0)
                <div class="order-item-additions">
                    @foreach($detalle->adiciones_elegidas as $ad)
                        <span class="order-addition">
                            + {{ is_array($ad) ? ($ad['nombre'] ?? '') : $ad }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if($detalle->notas)
                <div class="order-item-notes">
                    Nota: {{ $detalle->notas }}
                </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="order-footer">
        @if(!empty($estadoSig))
        <button class="btn-action {{ $btnClase }}"
                onclick="cambiarEstado(this, @js($pedido->id), '{{ $estadoSig }}')">
            {{ $btnLabel }}
        </button>
        @else
        <div style="text-align: center; color: var(--text-dim); font-size: 0.8rem; padding: 0.5rem 0; font-style: italic;">
            Esperando al mesero...
        </div>
        @endif
    </div>

</div>
