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
    $minutos  = (int) $pedido->created_at->diffInMinutes(now());
    $timerCls = $minutos >= 15 ? 'timer-urgent'
              : ($minutos >= 10 ? 'timer-warn' : 'timer-ok');
@endphp

<div class="order-card {{ $clase }}" id="card-{{ $pedido->id }}">

    <div class="order-card-header">
        <div style="display:flex;align-items:center;gap:.5rem">
            <span class="order-id">#{{ $pedido->id }}</span>
            <span class="order-table">
                Mesa {{ $pedido->subSesion?->sesionMesa?->mesa?->numero ?? '—' }}
            </span>
        </div>
        <span class="order-timer {{ $timerCls }}"
              data-creado="{{ $pedido->created_at->toIso8601String() }}">
            {{ $minutos }}min
        </span>
    </div>

    <div class="order-items">
        @foreach($pedido->detalles as $detalle)
        <div class="order-item">
            <span>{{ $detalle->producto?->nombre ?? '—' }}</span>
            <span class="order-item-qty">x{{ $detalle->cantidad }}</span>
        </div>
        @endforeach
    </div>

    <div class="order-footer">
        @if(!empty($estadoSig))
        <button class="btn-action {{ $btnClase }}"
                onclick="cambiarEstado(this, {{ $pedido->id }}, '{{ $estadoSig }}')">
            {{ $btnLabel }}
        </button>
        @else
        <div style="text-align: center; color: var(--text-dim); font-size: 0.8rem; padding: 0.5rem 0; font-style: italic;">
            Esperando al mesero...
        </div>
        @endif
    </div>

</div>