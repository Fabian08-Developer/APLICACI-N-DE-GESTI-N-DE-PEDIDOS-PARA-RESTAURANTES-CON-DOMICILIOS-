<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido cancelado</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#f3f4f6; font-family:'Helvetica Neue',Arial,sans-serif; color:#111827; padding:2rem 1rem; }
        .wrapper { max-width:520px; margin:0 auto; }
        .header  { background:#dc2626; border-radius:16px 16px 0 0; padding:2rem; text-align:center; color:#fff; }
        .header .icono { font-size:2.5rem; margin-bottom:0.6rem; }
        .header h1 { font-size:1.4rem; font-weight:700; }
        .header p  { font-size:0.9rem; opacity:0.85; margin-top:0.3rem; }
        .cuerpo { background:#fff; padding:1.8rem 2rem; }
        .monto-badge { background:#fef2f2; color:#dc2626; font-size:1.6rem; font-weight:700; text-align:center; border-radius:12px; padding:1rem; margin-bottom:1.5rem; }
        table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
        td { padding:0.55rem 0; font-size:0.9rem; border-bottom:1px solid #f3f4f6; }
        td:first-child { color:#6b7280; }
        td:last-child  { text-align:right; font-weight:600; }
        .items-titulo { font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#9ca3af; margin-bottom:0.6rem; }
        .item-row { display:flex; justify-content:space-between; font-size:0.88rem; padding:0.4rem 0; border-bottom:1px solid #f9fafb; }
        .motivo-box { background:#fffbeb; border-left:4px solid #f59e0b; border-radius:8px; padding:0.9rem 1rem; font-size:0.875rem; color:#92400e; margin-bottom:1.2rem; }
        .aviso { background:#f0f9ff; border-left:4px solid #0ea5e9; border-radius:8px; padding:0.9rem 1rem; font-size:0.875rem; color:#0c4a6e; }
        .footer { background:#f9fafb; border-radius:0 0 16px 16px; padding:1.2rem 2rem; text-align:center; font-size:0.78rem; color:#9ca3af; border-top:1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <div class="icono">❌</div>
        <h1>Pedido cancelado</h1>
        <p>Tu pedido ha sido cancelado correctamente.</p>
    </div>

    <div class="cuerpo">

        <div class="monto-badge">$<?php echo e(number_format($pedido->total, 2)); ?> COP</div>

        <table>
            <tr><td>Pedido</td>          <td>#<?php echo e($pedido->id); ?></td></tr>
            <tr><td>Fecha cancelación</td><td><?php echo e($pedido->fecha_cancelacion?->format('d/m/Y H:i')); ?></td></tr>
        </table>

        <?php if($pedido->motivo_cancelacion): ?>
        <div class="motivo-box">
            <strong>Motivo de cancelación:</strong><br>
            <?php echo e($pedido->motivo_cancelacion); ?>

        </div>
        <?php endif; ?>

        <?php if($pedido->detalles->isNotEmpty()): ?>
        <div class="items-titulo">Productos del pedido cancelado</div>
        <?php $__currentLoopData = $pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="item-row">
            <span><?php echo e($detalle->producto?->nombre); ?> <span style="color:#9ca3af">×<?php echo e($detalle->cantidad); ?></span></span>
            <span style="font-weight:500">$<?php echo e(number_format($detalle->subtotal, 2)); ?></span>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <div class="aviso" style="margin-top:1.2rem">
            💡 Si pagaste con Nequi y el pago fue aprobado, el reembolso se procesará en los próximos días hábiles.
            Si pagaste en efectivo o el pago no fue completado, no se realizará ningún cobro.
        </div>

    </div>

    <div class="footer">
        Este es un correo automático, no es necesario responder.
    </div>

</div>
</body>
</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/emails/pedido_cancelado.blade.php ENDPATH**/ ?>