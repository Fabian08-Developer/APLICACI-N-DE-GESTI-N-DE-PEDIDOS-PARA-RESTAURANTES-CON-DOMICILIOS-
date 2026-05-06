<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido cancelado</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/canceelacion_exitosa.css'); ?>
</head>

<body>
    <div class="contenedor">

        <div class="icono-cancelado" aria-hidden="true">✕</div>
        <h1 style="text-align: center">Pedido cancelado</h1>
        <p class="subtexto" style="text-align: center">
            Tu pedido fue cancelado correctamente.
            <?php if($emailEnviado): ?>
            Te enviamos un comprobante al correo.
            <?php endif; ?>
        </p>

        
        <div class="cancelacion-card">
            <div class="cancelacion-header">Detalle de la cancelación</div>

            <div class="cancelacion-row">
                <span class="label">Pedido</span>
                <span class="valor">#<?php echo e($pedido->id); ?></span>
            </div>
            <div class="cancelacion-row">
                <span class="label">Total cancelado</span>
                <span class="valor">$<?php echo e(number_format($pedido->total, 2)); ?></span>
            </div>
            <div class="cancelacion-row">
                <span class="label">Fecha</span>
                <span class="valor"><?php echo e($pedido->fecha_cancelacion?->format('d/m/Y H:i')); ?></span>
            </div>

            <?php if($pedido->motivo_cancelacion): ?>
            <div class="motivo-badge">
                <strong>Motivo:</strong> <?php echo e($pedido->motivo_cancelacion); ?>

            </div>
            <?php endif; ?>
        </div>

        
        <?php if($teniaPagoAprobado): ?>
        <div class="aviso-reembolso">
            Tu pago fue procesado mediante Nequi. El reembolso se gestionará en los próximos días hábiles y recibirás una notificación cuando se complete.
        </div>
        <?php else: ?>
        <div class="aviso-ok">
            No se realizó ningún cobro a tu cuenta. No necesitas hacer nada más.
        </div>
        <?php endif; ?>

        
        <div class="tarjeta">
            <div class="tarjeta-header">
                <span>Productos cancelados</span>
                <span class="pedido-num">#<?php echo e($pedido->id); ?></span>
            </div>

            <?php $__currentLoopData = $pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="item-pedido">
                <span>
                    <span class="item-nombre"><?php echo e($detalle->producto?->nombre); ?></span>
                    <span class="item-cant"> &times;<?php echo e($detalle->cantidad); ?></span>
                </span>
                <span class="item-precio">$<?php echo e(number_format($detalle->subtotal, 2)); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="total-row">
                <span>Total</span>
                <span>$<?php echo e(number_format($pedido->total, 2)); ?></span>
            </div>
        </div>

        <a href="<?php echo e(route('cliente.menu', ['t' => $token])); ?>" class="btn-nuevo-pedido"> + Hacer un nuevo pedido</a>

    </div>
</body>

</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cliente/cancelacion_exitosa.blade.php ENDPATH**/ ?>