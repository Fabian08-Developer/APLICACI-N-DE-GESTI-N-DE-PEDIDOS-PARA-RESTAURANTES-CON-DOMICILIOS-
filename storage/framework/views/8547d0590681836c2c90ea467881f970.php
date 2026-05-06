<?php $__env->startSection('titulo', 'Detalle de Pedido #' . $pedido->id); ?>
<?php $__env->startSection('contenido'); ?>
<?php echo app('Illuminate\Foundation\Vite')(['resources/css/pedidos.css']); ?>

<div class="pagina-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1>Pedido #<?php echo e($pedido->id); ?></h1>
        <p>Realizado el <?php echo e($pedido->created_at->format('d/m/Y H:i')); ?></p>
    </div>
    <div>
        <a href="<?php echo e(route('admin.pedidos.index')); ?>" class="btn-cancelar" style="text-decoration: none; display: inline-block; background: var(--surface); border: 1px solid var(--border); color: var(--text-main); padding: 0.5rem 1rem; border-radius: 6px;">← Volver</a>
    </div>
</div>

<div class="grid-dos" style="margin-bottom: 2rem;">
    
    <div class="tarjeta" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--primary);">Información General</h3>
        <div style="display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.9rem;">
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-sec)">Mesa</span>
                <strong><?php echo e($pedido->sesionMesa?->mesa?->numero ?? '—'); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-sec)">Mesero Asignado</span>
                <strong><?php echo e($pedido->mesero?->nombre ?? 'N/A'); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-sec)">Estado</span>
                <?php $claseEstado = 'badge-' . strtolower(str_replace('_', '', $pedido->estado)); ?>
                <span class="badge <?php echo e($claseEstado); ?>"><?php echo e(str_replace('_', ' ', $pedido->estado)); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--border);">
                <span style="color: var(--text-sec)">Total Pago</span>
                <strong style="color: var(--status-success); font-size: 1.1rem;">$<?php echo e(number_format($pedido->total, 0, ',', '.')); ?></strong>
            </div>
        </div>
    </div>

    
    <div class="tarjeta" style="padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--primary);">Historial de Movimientos</h3>
        <?php if($pedido->historial->isEmpty()): ?>
            <p style="color: var(--text-sec); font-size: 0.9rem;">No hay movimientos registrados.</p>
        <?php else: ?>
            <div style="max-height: 200px; overflow-y: auto;">
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                    <?php $__currentLoopData = $pedido->historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movimiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li style="position: relative; padding-left: 1.5rem;">
                        <span style="position: absolute; left: 0; top: 0.35rem; width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></span>
                        <div style="font-size: 0.85rem;">
                            <strong style="color: var(--text-main);"><?php echo e(str_replace('_', ' ', $movimiento->estado)); ?></strong> 
                            <span style="color: var(--text-sec); font-size: 0.75rem; margin-left: 0.5rem;"><?php echo e($movimiento->fecha->format('d/m/Y H:i')); ?></span>
                        </div>
                        <?php if($movimiento->usuario): ?>
                            <div style="color: var(--text-sec); font-size: 0.75rem; margin-top: 0.2rem;">Por: <?php echo e($movimiento->usuario->nombre); ?></div>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="tarjeta">
    <div class="tarjeta-header">Detalle de Productos</div>
    <div style="overflow-x: auto;">
        <table class="admin-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($detalle->producto?->nombre ?? 'Producto Eliminado'); ?></td>
                    <td><?php echo e($detalle->cantidad); ?></td>
                    <td style="color: var(--text-main); font-weight: 500;">$<?php echo e(number_format($detalle->subtotal, 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/admin/pedidos/detalle.blade.php ENDPATH**/ ?>