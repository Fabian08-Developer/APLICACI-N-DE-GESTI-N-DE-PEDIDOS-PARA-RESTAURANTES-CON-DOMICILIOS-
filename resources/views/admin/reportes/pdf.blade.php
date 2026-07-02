<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 5px 0;
        }
        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }
        .meta-table {
            width: 100%;
            margin-top: 10px;
        }
        .meta-table td {
            padding: 3px 0;
            color: #475569;
        }
        .kpis-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .kpi-card {
            width: 22%;
            display: inline-block;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-right: 2%;
            vertical-align: top;
        }
        .kpi-card-last {
            margin-right: 0;
        }
        .kpi-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .kpi-change {
            font-size: 11px;
            font-weight: bold;
        }
        .kpi-positive {
            color: #10b981;
        }
        .kpi-negative {
            color: #ef4444;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 8px 12px;
            border-bottom: 1px solid #cbd5e1;
        }
        .data-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .progress-bar-container {
            width: 100px;
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        .progress-bar {
            height: 6px;
            background-color: #64748b;
            border-radius: 3px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Reporte de Ventas</div>
        <div class="subtitle">Sucursal: {{ $sucursal->nombre }}</div>
        <table class="meta-table">
            <tr>
                <td><strong>Período:</strong> {{ ucfirst($period) }}</td>
                <td class="text-right"><strong>Fechas:</strong> {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</td>
            </tr>
            @if(!empty($selectedCategorias) || !empty($selectedMetodosPago) || !empty($selectedProductosTop))
            <tr>
                <td colspan="2" style="font-size: 11px; color: #64748b; padding-top: 5px;">
                    <strong>Filtros Aplicados:</strong> 
                    @if(!empty($selectedCategorias)) Categorías ({{ count($selectedCategorias) }}) @endif
                    @if(!empty($selectedMetodosPago)) | Métodos de Pago ({{ implode(', ', $selectedMetodosPago) }}) @endif
                    @if(!empty($selectedProductosTop)) | Productos Top ({{ count($selectedProductosTop) }}) @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    @if(in_array('kpis', $sections))
    <div class="kpis-container">
        <!-- KPI 1 -->
        <div class="kpi-card">
            <div class="kpi-label">Ventas Totales</div>
            <div class="kpi-value">${{ number_format($currentMetrics['ventasTotales'], 0, ',', '.') }}</div>
            <div class="kpi-change {{ $changes['ventasTotales'] >= 0 ? 'kpi-positive' : 'kpi-negative' }}">
                {{ $changes['ventasTotales'] >= 0 ? '↗' : '↘' }} {{ $changes['ventasTotales'] }}%
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="kpi-card">
            <div class="kpi-label">Clientes</div>
            <div class="kpi-value">{{ number_format($currentMetrics['clientesAtendidos'], 0, ',', '.') }}</div>
            <div class="kpi-change {{ $changes['clientesAtendidos'] >= 0 ? 'kpi-positive' : 'kpi-negative' }}">
                {{ $changes['clientesAtendidos'] >= 0 ? '↗' : '↘' }} {{ $changes['clientesAtendidos'] }}%
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="kpi-card">
            <div class="kpi-label">Ticket Prom.</div>
            <div class="kpi-value">${{ number_format($currentMetrics['ticketPromedio'], 0, ',', '.') }}</div>
            <div class="kpi-change {{ $changes['ticketPromedio'] >= 0 ? 'kpi-positive' : 'kpi-negative' }}">
                {{ $changes['ticketPromedio'] >= 0 ? '↗' : '↘' }} {{ $changes['ticketPromedio'] }}%
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="kpi-card kpi-card-last">
            <div class="kpi-label">Pedidos</div>
            <div class="kpi-value">{{ $currentMetrics['totalPedidos'] }}</div>
            <div class="kpi-change {{ $changes['totalPedidos'] >= 0 ? 'kpi-positive' : 'kpi-negative' }}">
                {{ $changes['totalPedidos'] >= 0 ? '↗' : '↘' }} {{ $changes['totalPedidos'] }}%
            </div>
        </div>
    </div>
    @endif

    @if(in_array('categories', $sections) && count($categoriasChart) > 0)
    <div>
        <div class="section-title">Desglose por Categoría</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="text-right">Monto Ventas ($)</th>
                    <th class="text-right">Participación (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoriasChart as $cat)
                @php
                    $pct = $currentMetrics['ventasTotales'] > 0 ? ($cat->total / $currentMetrics['ventasTotales']) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $cat->nombre }}</td>
                    <td class="text-right">${{ number_format($cat->total, 0, ',', '.') }}</td>
                    <td class="text-right">
                        {{ round($pct, 1) }}%
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ $pct }}%;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(in_array('chart', $sections) && count($trendDates) > 0)
    <div>
        <div class="section-title">Tendencia de Ventas (Detalle de Movimiento)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th class="text-right">Ventas ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trendDates as $index => $date)
                <tr>
                    <td>{{ $date }}</td>
                    <td class="text-right">${{ number_format($trendTotals[$index], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Documento generado automáticamente por el sistema de Cafetería - {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>
