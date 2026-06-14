<table>
    <thead>
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 16px; text-align: center;">REPORTE DE VENTAS</th>
        </tr>
        <tr>
            <th colspan="3" style="font-size: 12px; text-align: center;">Sucursal: {{ $sucursal->nombre }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-size: 11px; text-align: center;">Período: {{ ucfirst($period) }} ({{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }})</th>
        </tr>
        @if(!empty($selectedCategorias) || !empty($selectedMetodosPago) || !empty($selectedProductosTop))
        <tr>
            <th colspan="3" style="font-size: 10px; color: #555555; text-align: center;">
                Filtros activos: 
                @if(!empty($selectedCategorias)) Categorías ({{ count($selectedCategorias) }}) @endif
                @if(!empty($selectedMetodosPago)) Métodos de Pago ({{ implode(', ', $selectedMetodosPago) }}) @endif
                @if(!empty($selectedProductosTop)) Productos Top ({{ count($selectedProductosTop) }}) @endif
            </th>
        </tr>
        @endif
        <tr><th></th><th></th><th></th></tr>
    </thead>
    <tbody>
        <!-- KPIs Section -->
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 13px; background-color: #E2E8F0;">Resumen General (KPIs)</th>
        </tr>
        <tr>
            <td style="font-weight: bold;">Métrica</td>
            <td style="font-weight: bold; text-align: right;">Valor</td>
            <td style="font-weight: bold; text-align: right;">Variación vs Anterior</td>
        </tr>
        <tr>
            <td>Ventas Totales</td>
            <td style="text-align: right;">${{ number_format($currentMetrics['ventasTotales'], 2, '.', '') }}</td>
            <td style="text-align: right;">{{ $changes['ventasTotales'] }}%</td>
        </tr>
        <tr>
            <td>Clientes Atendidos</td>
            <td style="text-align: right;">{{ $currentMetrics['clientesAtendidos'] }}</td>
            <td style="text-align: right;">{{ $changes['clientesAtendidos'] }}%</td>
        </tr>
        <tr>
            <td>Ticket Promedio</td>
            <td style="text-align: right;">${{ number_format($currentMetrics['ticketPromedio'], 2, '.', '') }}</td>
            <td style="text-align: right;">{{ $changes['ticketPromedio'] }}%</td>
        </tr>
        <tr>
            <td>Total Pedidos</td>
            <td style="text-align: right;">{{ $currentMetrics['totalPedidos'] }}</td>
            <td style="text-align: right;">{{ $changes['totalPedidos'] }}%</td>
        </tr>
        
        <tr><th></th><th></th><th></th></tr>

        <!-- Categories Section -->
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 13px; background-color: #E2E8F0;">Ventas por Categoría</th>
        </tr>
        <tr>
            <td style="font-weight: bold;">Categoría</td>
            <td style="font-weight: bold; text-align: right;">Ventas ($)</td>
            <td style="font-weight: bold; text-align: right;">Porcentaje</td>
        </tr>
        @foreach($categoriasChart as $cat)
        <tr>
            <td>{{ $cat->nombre }}</td>
            <td style="text-align: right;">${{ number_format($cat->total, 2, '.', '') }}</td>
            <td style="text-align: right;">
                {{ $currentMetrics['ventasTotales'] > 0 ? round(($cat->total / $currentMetrics['ventasTotales']) * 100, 1) : 0 }}%
            </td>
        </tr>
        @endforeach

        <tr><th></th><th></th><th></th></tr>

        <!-- Trend Section -->
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 13px; background-color: #E2E8F0;">Tendencia Temporal de Ventas</th>
        </tr>
        <tr>
            <td style="font-weight: bold;">Fecha / Hora</td>
            <td colspan="2" style="font-weight: bold; text-align: right;">Monto Ventas ($)</td>
        </tr>
        @foreach($trendDates as $index => $date)
        <tr>
            <td>{{ $date }}</td>
            <td colspan="2" style="text-align: right;">${{ number_format($trendTotals[$index], 2, '.', '') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
