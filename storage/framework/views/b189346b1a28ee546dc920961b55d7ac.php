

<?php $__env->startSection('titulo', 'Cocina'); ?>

<?php $__env->startSection('contenido'); ?>





<div class="page-header">
    <div>
        <h1>Panel de cocina</h1>
        <div class="page-header-meta" id="live-time"><?php echo e(now()->format('d/m/Y H:i')); ?></div>
    </div>
</div>



<div class="kanban">

    
    <div class="kanban-col" id="col-nuevos">
        <div class="kanban-col-header">
            Nuevos
            <span class="col-count" id="col-cnt-nuevos"><?php echo e($pedidosNuevos->count()); ?></span>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pedidosNuevos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-new',
                'btnLabel'  => 'Iniciar',
                'btnClase'  => 'btn-start',
                'estadoSig' => 'EN_COCINA',
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-empty">Sin pedidos nuevos</div>
        <?php endif; ?>
    </div>

    
    <div class="kanban-col" id="col-en-cocina">
        <div class="kanban-col-header">
            En cocina
            <span class="col-count" id="col-cnt-cocina"><?php echo e($pedidosEnCocina->count()); ?></span>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pedidosEnCocina; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-cooking',
                'btnLabel'  => 'Preparando',
                'btnClase'  => 'btn-prep',
                'estadoSig' => 'EN_PREPARACION',
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-empty">Vacío</div>
        <?php endif; ?>
    </div>

    
    <div class="kanban-col" id="col-prep">
        <div class="kanban-col-header">
            Preparando
            <span class="col-count" id="col-cnt-prep"><?php echo e($pedidosEnPreparacion->count()); ?></span>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pedidosEnPreparacion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-prep',
                'btnLabel'  => 'Listo',
                'btnClase'  => 'btn-ready',
                'estadoSig' => 'LISTO',
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-empty">Vacío</div>
        <?php endif; ?>
    </div>

    
    <div class="kanban-col" id="col-listos">
        <div class="kanban-col-header">
            Listos
            <span class="col-count" id="col-cnt-listos"><?php echo e($pedidosListos->count()); ?></span>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $pedidosListos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('cocina.partials.card-pedido', [
                'pedido'    => $pedido,
                'clase'     => 'state-ready',
                'btnLabel'  => 'Entregar',
                'btnClase'  => 'btn-deliver',
                'estadoSig' => '',
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-empty">Vacío</div>
        <?php endif; ?>
    </div>

</div>



<div id="toast"></div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        initCocina({
            csrf: '<?php echo e(csrf_token()); ?>',
            rutaNuevos: '<?php echo e(route('cocina.pedidos.nuevos')); ?>'
        });
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('cocina.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cocina/dashboard.blade.php ENDPATH**/ ?>