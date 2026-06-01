<div>
{{-- ENCABEZADO --}}
<div class="pagina-header">
    <h1>Dashboard</h1>
    <p>Resumen del día — {{ now()->format('d/m/Y') }}</p>
</div>

{{-- TARJETAS ESTADÍSTICAS --}}
<div class="grid-tarjetas">
    <div class="tarjeta-stat">
        <span class="stat-icono"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
        <div class="stat-numero dorado">{{ $pedidosHoy }}</div>
        <div class="stat-label">Pedidos hoy</div>
    </div>

    <div class="tarjeta-stat">
        <span class="stat-icono"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/></svg></span>
        <div class="stat-numero verde">{{ $mesasDisponibles }}</div>
        <div class="stat-label">Mesas disponibles</div>
    </div>

    <div class="tarjeta-stat">
        <span class="stat-icono"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span>
        <div class="stat-numero">{{ $usuariosActivos }}</div>
        <div class="stat-label">Usuarios activos</div>
    </div>
</div>

<div class="grid-dos">

    {{-- ÚLTIMOS PEDIDOS --}}
    <div class="tarjeta">
        <div class="tarjeta-header">Últimos pedidos registrados</div>

        @if($ultimosPedidos->isEmpty())
            <div class="vacio">No se han registrado pedidos hoy</div>
        @else
            <table class="admin-tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mesero</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosPedidos as $pedido)
                    <tr>
                        <td style="font-weight: 500;">#{{ $pedido->id }}</td>
                        <td>{{ $pedido->mesero?->nombre ?? '—' }}</td>
                        <td>
                            @php
                                $claseBadge = match($pedido->estado) {
                                    'CREADO', 'creado'    => 'badge-creado',
                                    'EN_PREPARACION', 'en_preparacion' => 'badge-cocina',
                                    'LISTO', 'listo'     => 'badge-listo',
                                    'CANCELADO', 'cancelado' => 'badge-cancelado',
                                    default     => 'badge-default',
                                };
                            @endphp
                            <span class="badge {{ $claseBadge }}">
                                {{ str_replace('_', ' ', strtoupper($pedido->estado)) }}
                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            {{ $pedido->creado_en?->format('d/m H:i') ?? '' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ESTADO DE MESAS --}}
    <div class="tarjeta">
        <div class="tarjeta-header">Control de ocupación</div>

        <div class="mesas-grid">
            <div class="mesa-stat disponible">
                <div class="mesa-num">{{ $mesasDisponibles }}</div>
                <div class="mesa-label">Disponibles</div>
            </div>
            <div class="mesa-stat ocupada">
                <div class="mesa-num">{{ $mesasOcupadas }}</div>
                <div class="mesa-label">Ocupadas</div>
            </div>
        </div>

        <div style="padding: 0 1.5rem 2rem;">
            @if($mesasTotales > 0)
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.6rem;">
                    <span>Ocupación de la sala</span>
                    <span style="font-weight: 600; color: var(--text-main);">{{ $porcentajeOcupacion }}%</span>
                </div>
                <div class="progreso-vacia">
                    <div class="progreso-llena" style="width: {{ $porcentajeOcupacion }}%;"></div>
                </div>
            @else
                <div style="text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                    No hay mesas configuradas
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    setInterval(() => {
        @this.call('$refresh');
    }, 60000);
</script>
</div>
