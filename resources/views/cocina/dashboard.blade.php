@extends('cocina.layout')

@section('titulo', 'Cocina')

@section('contenido')




{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <h1>Panel de cocina</h1>
        <div class="page-header-meta" id="live-time">{{ now()->format('d/m/Y H:i') }}</div>
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
                'btnLabel'  => 'Iniciar',
                'btnClase'  => 'btn-start',
                'estadoSig' => 'EN_COCINA',
            ])
        @empty
            <div class="col-empty">Sin pedidos nuevos</div>
        @endforelse
    </div>

    {{-- COL 2: EN COCINA --}}
    <div class="kanban-col" id="col-en-cocina">
        <div class="kanban-col-header">
            En cocina
            <span class="col-count" id="col-cnt-cocina">{{ $pedidosEnCocina->count() }}</span>
        </div>
        @forelse($pedidosEnCocina as $pedido)
            @include('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-cooking',
                'btnLabel'  => 'Preparando',
                'btnClase'  => 'btn-prep',
                'estadoSig' => 'EN_PREPARACION',
            ])
        @empty
            <div class="col-empty">Vacío</div>
        @endforelse
    </div>

    {{-- COL 3: EN PREPARACIÓN --}}
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

    {{-- COL 4: LISTOS --}}
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
            rutaNuevos: '{{ route('cocina.pedidos.nuevos') }}'
        });
    });
</script>

@endsection