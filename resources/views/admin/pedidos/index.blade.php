@extends('admin.layout')
@section('titulo', 'Pedidos')
@section('contenido')
@vite(['resources/css/pedidos.css'])

<div class="pagina-header">
    <h1>Pedidos</h1>
    <p>Gestión y seguimiento de todos los pedidos</p>
</div>

{{-- FILTROS AVANZADOS (ESTILO OSCURO/ELEGANTE) --}}
<div class="elegant-filter-card">
    <div class="filter-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        FILTROS
    </div>
    
    <form method="GET" action="{{ route('admin.pedidos.index') }}" class="elegant-filter-grid">
        <div class="elegant-group">
            <label for="filtro_inicio">DESDE</label>
            <input type="date" name="fecha_inicio" id="filtro_inicio" value="{{ request('fecha_inicio') }}">
        </div>

        <div class="elegant-group">
            <label for="filtro_fin">HASTA</label>
            <input type="date" name="fecha_fin" id="filtro_fin" value="{{ request('fecha_fin') }}">
        </div>
        
        <div class="elegant-group">
            <label for="filtro_estado">ESTADO</label>
            <select name="estado" id="filtro_estado">
                <option value="">Todos los estados</option>
                <option value="CREADO" {{ request('estado') == 'CREADO' ? 'selected' : '' }}>Creado</option>
                <option value="EN_COCINA" {{ request('estado') == 'EN_COCINA' ? 'selected' : '' }}>En Cocina</option>
                <option value="LISTO" {{ request('estado') == 'LISTO' ? 'selected' : '' }}>Listo</option>
                <option value="ENTREGADO" {{ request('estado') == 'ENTREGADO' ? 'selected' : '' }}>Entregado</option>
                <option value="CANCELADO" {{ request('estado') == 'CANCELADO' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>

        <div class="elegant-group">
            <label for="filtro_mesa">MESA</label>
            <select name="mesa_id" id="filtro_mesa">
                <option value="">Todas las mesas</option>
                @foreach($mesas as $mesa)
                    <option value="{{ $mesa->id }}" {{ request('mesa_id') == $mesa->id ? 'selected' : '' }}>Mesa {{ $mesa->numero }}</option>
                @endforeach
            </select>
        </div>

        <div class="elegant-group">
            <label for="filtro_mesero">MESERO</label>
            <select name="mesero_id" id="filtro_mesero">
                <option value="">Todos los meseros</option>
                @foreach($meseros as $mesero)
                    <option value="{{ $mesero->id }}" {{ request('mesero_id') == $mesero->id ? 'selected' : '' }}>{{ $mesero->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="elegant-actions">
            <button type="submit" class="btn-filtrar">Filtrar</button>
            @if(request()->anyFilled(['fecha_inicio', 'fecha_fin', 'estado', 'mesa_id', 'mesero_id']))
                <a href="{{ route('admin.pedidos.index') }}" class="btn-limpiar" title="Limpiar filtros">✕</a>
            @endif
        </div>
    </form>
</div>
<div class="tarjeta">
    <div class="tarjeta-header">
        Resultados: {{ $pedidos->count() }} pedidos
    </div>

    @if($pedidos->isEmpty())
        <div class="vacio">No hay pedidos con esos criterios</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Mesa</th>
                    <th>Mesero</th>
                    <th>Productos</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                @php
                    $claseEstado = 'badge-' . strtolower(str_replace('_', '_', $pedido->estado));
                @endphp
                <tr>
                    <td class="texto-gris">#{{ $pedido->id }}</td>
                    <td>Mesa {{ $pedido->sesionMesa?->mesa?->numero ?? '—' }}</td>
                    <td>{{ $pedido->mesero?->nombre ?? '—' }}</td>
                    <td class="texto-gris">{{ $pedido->detalles->count() }} items</td>
                    <td class="precio">
                        {{ $pedido->total ? '$' . number_format($pedido->total, 2) : '—' }}
                    </td>
                    <td>
                        <span class="badge {{ $claseEstado }}">{{ $pedido->estado }}</span>
                    </td>
                    <td class="texto-gris">{{ $pedido->created_at->format('d/m H:i') }}</td>
                    <td>
                        <div class="acciones">
                            <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="btn-ver">
                                Ver detalle
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection