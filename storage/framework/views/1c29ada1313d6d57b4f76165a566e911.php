
<?php
    $minutos  = (int) $pedido->created_at->diffInMinutes(now());
    $timerCls = $minutos >= 15 ? 'timer-urgent'
              : ($minutos >= 10 ? 'timer-warn' : 'timer-ok');
?>

<div class="order-card <?php echo e($clase); ?>" id="card-<?php echo e($pedido->id); ?>">

    <div class="order-card-header">
        <div style="display:flex;align-items:center;gap:.5rem">
            <span class="order-id">#<?php echo e($pedido->id); ?></span>
            <span class="order-table">
                Mesa <?php echo e($pedido->subSesion?->sesionMesa?->mesa?->numero ?? '—'); ?>

            </span>
        </div>
        <span class="order-timer <?php echo e($timerCls); ?>"
              data-creado="<?php echo e($pedido->created_at->toIso8601String()); ?>">
            <?php echo e($minutos); ?>min
        </span>
    </div>

    <div class="order-items">
        <?php $__currentLoopData = $pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="order-item">
            <span><?php echo e($detalle->producto?->nombre ?? '—'); ?></span>
            <span class="order-item-qty">x<?php echo e($detalle->cantidad); ?></span>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="order-footer">
        <?php if(!empty($estadoSig)): ?>
        <button class="btn-action <?php echo e($btnClase); ?>"
                onclick="cambiarEstado(this, <?php echo e($pedido->id); ?>, '<?php echo e($estadoSig); ?>')">
            <?php echo e($btnLabel); ?>

        </button>
        <?php else: ?>
        <div style="text-align: center; color: var(--text-dim); font-size: 0.8rem; padding: 0.5rem 0; font-style: italic;">
            Esperando al mesero...
        </div>
        <?php endif; ?>
    </div>

</div><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cocina/partials/card-pedido.blade.php ENDPATH**/ ?>