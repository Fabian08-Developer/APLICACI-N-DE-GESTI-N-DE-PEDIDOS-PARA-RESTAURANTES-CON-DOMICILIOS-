<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; color: #333; margin: 0; padding: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0F172A; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #0F172A; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; }
        .metrics { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .metrics td { width: 25%; text-align: center; border: 1px solid #ddd; padding: 15px; background: #f8fafc; }
        .metrics strong { display: block; font-size: 18px; color: #0F172A; margin-top: 5px; }
        .table-title { font-size: 16px; color: #0F172A; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.data th { background-color: #0F172A; color: #fff; font-size: 11px; text-transform: uppercase; }
        table.data tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { text-align: center; margin-top: 50px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .projection-box { border: 1px solid #ddd; border-radius: 4px; padding: 12px; margin-bottom: 30px; background: #f8fafc; }
        .projection-box p { margin: 4px 0; }
        .projection-label { font-weight: bold; color: #0F172A; }
    </style>
</head>
<body>

    
    <div class="header">
        <h1>Reporte de Ventas - Cafetería</h1>
        <p>Periodo: <?php echo e($start); ?> al <?php echo e($end); ?></p>
        <?php if(count($sections) < 6): ?>
        <p style="font-size:10px;color:#999;">
            Secciones incluidas: <?php echo e(implode(', ', $sections)); ?>

        </p>
        <?php endif; ?>
    </div>

    
    <?php if(in_array('kpis', $sections)): ?>
    <table class="metrics">
        <tr>
            <td>
                Ventas Totales
                <strong>$<?php echo e(number_format($currentMetrics['ventasTotales'], 0, ',', '.')); ?></strong>
            </td>
            <td>
                Clientes
                <strong><?php echo e($currentMetrics['clientesAtendidos']); ?></strong>
            </td>
            <td>
                Ticket Promedio
                <strong>$<?php echo e(number_format($currentMetrics['ticketPromedio'], 0, ',', '.')); ?></strong>
            </td>
            <td>
                Total Pedidos
                <strong><?php echo e($currentMetrics['totalPedidos']); ?></strong>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    
    <?php if(in_array('categories', $sections)): ?>
    <div class="table-title">Desglose de Ventas por Categoría</div>
    <table class="data">
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Total Vendido ($)</th>
                <th>Participación (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $categoriasChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($cat->nombre); ?></td>
                <td>$<?php echo e(number_format($cat->total, 0, ',', '.')); ?></td>
                <td><?php echo e($currentMetrics['ventasTotales'] > 0 ? round(($cat->total / $currentMetrics['ventasTotales']) * 100, 1) : 0); ?>%</td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if(in_array('products', $sections)): ?>
    <div class="table-title">Productos Destacados (Top 10)</div>
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Unidades Vendidas</th>
                <th>Total Generado ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $productosTop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($prod->nombre); ?></td>
                <td><?php echo e($prod->cantidad); ?></td>
                <td>$<?php echo e(number_format($prod->total, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if(in_array('chart', $sections)): ?>
    <div class="table-title">Listado de Pedidos Completados</div>
    <table class="data">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Mesero</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>#<?php echo e($pedido->id); ?></td>
                <td><?php echo e($pedido->created_at->format('Y-m-d H:i')); ?></td>
                <td><?php echo e($pedido->mesero ? $pedido->mesero->nombre : 'Automático'); ?></td>
                <td>$<?php echo e(number_format($pedido->total, 0, ',', '.')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if(in_array('comparison', $sections)): ?>
    <div class="table-title">Comparación de Período</div>
    <table class="data">
        <thead>
            <tr>
                <th>Métrica</th>
                <th>Período Actual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ventas Totales</td>
                <td>$<?php echo e(number_format($currentMetrics['ventasTotales'], 0, ',', '.')); ?></td>
            </tr>
            <tr>
                <td>Total Pedidos</td>
                <td><?php echo e($currentMetrics['totalPedidos']); ?></td>
            </tr>
            <tr>
                <td>Ticket Promedio</td>
                <td>$<?php echo e(number_format($currentMetrics['ticketPromedio'], 0, ',', '.')); ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if(in_array('predictions', $sections)): ?>
    <?php
        $diasPeriodo = max(1, \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1);
        $ventaDiaProm = $currentMetrics['ventasTotales'] / $diasPeriodo;
        $proyeccionMes = $ventaDiaProm * 30;
        $proyeccionTrim = $ventaDiaProm * 90;
    ?>
    <div class="table-title">Proyecciones Estimadas</div>
    <div class="projection-box">
        <p><span class="projection-label">Promedio diario:</span> $<?php echo e(number_format($ventaDiaProm, 0, ',', '.')); ?></p>
        <p><span class="projection-label">Proyección mensual (30 días):</span> $<?php echo e(number_format($proyeccionMes, 0, ',', '.')); ?></p>
        <p><span class="projection-label">Proyección trimestral (90 días):</span> $<?php echo e(number_format($proyeccionTrim, 0, ',', '.')); ?></p>
        <p style="font-size:10px;color:#999;margin-top:8px;">* Basado en el promedio diario del período seleccionado.</p>
    </div>
    <?php endif; ?>

    <div class="footer">
        Generado el <?php echo e(now()->format('Y-m-d H:i:s')); ?> — Sistema de Gestión Cafetería
    </div>

</body>
</html>
<?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/admin/reports/exports/sales_pdf.blade.php ENDPATH**/ ?>