@extends('cocina.layout')

@section('titulo', 'Cocina')

@section('contenido')

{{-- PAGE HEADER --}}
<header class="page-header">
    <div>
        <h1>Panel de <span>Preparación</span></h1>
        <div class="live-indicator">
            <span class="dot"></span>
            Actualización en vivo
        </div>
    </div>
</header>

{{-- METRICS BAR --}}
<div class="metrics-bar">
    <div class="metric-card">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(224, 122, 95, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div>
            <div class="metric-label">Órdenes Activas</div>
            <div class="metric-value" id="metric-activas">{{ $pedidosNuevos->count() + $pedidosEnPreparacion->count() }}</div>
            <div class="metric-desc">En cola y preparación</div>
        </div>
    </div>
    <div class="metric-card">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(129, 178, 154, 0.1); color: var(--status-ready); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div>
            <div class="metric-label">Platos Listos Hoy</div>
            <div class="metric-value" id="metric-listos">{{ $pedidosListos->count() }}</div>
            <div class="metric-desc">Esperando entrega</div>
        </div>
    </div>
    <div class="metric-card">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(61, 90, 128, 0.1); color: var(--status-info); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div>
            <div class="metric-label">Tiempo Promedio</div>
            <div class="metric-value">14<span style="font-size:1rem; font-weight:500;">m</span></div>
            <div class="metric-desc">Estimado por orden</div>
        </div>
    </div>
</div>

{{-- KANBAN BOARD --}}
<div class="kanban">

    {{-- COL 1: NUEVOS --}}
    <div class="kanban-col" id="col-nuevos">
        <div class="kanban-col-header">
            Nuevos
            <span class="col-count" id="col-cnt-nuevos">{{ $pedidosNuevos->count() }}</span>
        </div>
        @forelse($pedidosNuevos as $pedido)
            @include('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-new',
                'btnLabel'  => 'Preparar',
                'btnClase'  => 'btn-prep',
                'estadoSig' => 'EN_PREPARACION',
            ])
        @empty
            <div class="col-empty">Sin pedidos nuevos</div>
        @endforelse
    </div>

    {{-- COL 2: EN PREPARACIÓN --}}
    <div class="kanban-col" id="col-prep">
        <div class="kanban-col-header">
            Preparando
            <span class="col-count" id="col-cnt-prep">{{ $pedidosEnPreparacion->count() }}</span>
        </div>
        @forelse($pedidosEnPreparacion as $pedido)
            @include('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-prep',
                'btnLabel'  => 'Listo',
                'btnClase'  => 'btn-ready',
                'estadoSig' => 'LISTO',
            ])
        @empty
            <div class="col-empty">Vacío</div>
        @endforelse
    </div>

    {{-- COL 3: LISTOS --}}
    <div class="kanban-col" id="col-listos">
        <div class="kanban-col-header">
            Listos
            <span class="col-count" id="col-cnt-listos">{{ $pedidosListos->count() }}</span>
        </div>
        @forelse($pedidosListos as $pedido)
            @include('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-ready',
                'btnLabel'  => 'Entregar',
                'btnClase'  => 'btn-deliver',
                'estadoSig' => '',
            ])
        @empty
            <div class="col-empty">Vacío</div>
        @endforelse
    </div>

</div>

{{-- TOAST --}}
<div id="toast"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initCocina({
            csrf: '{{ csrf_token() }}',
            rutaNuevos: '{{ route('cocina.pedidos.nuevos') }}',
            rutaVerificar: '{{ route('cocina.pedidos.verificar-estados') }}'
        });
    });
</script>

@endsection