
<?php $__env->startSection('titulo', 'Pedidos'); ?>
<?php $__env->startSection('contenido'); ?>
<?php echo app('Illuminate\Foundation\Vite')(['resources/css/pedidos.css']); ?>

<div class="pagina-header">
    <h1>Pedidos</h1>
    <p>Gestión y seguimiento de todos los pedidos</p>
</div>


<div class="elegant-filter-card">
    <div class="filter-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        FILTROS
    </div>
    
    <form method="GET" action="<?php echo e(route('admin.pedidos.index')); ?>" class="elegant-filter-grid">
        <div class="elegant-group">
            <label for="filtro_inicio">DESDE</label>
            <input type="date" name="fecha_inicio" id="filtro_inicio" value="<?php echo e(request('fecha_inicio')); ?>">
        </div>

        <div class="elegant-group">
            <label for="filtro_fin">HASTA</label>
            <input type="date" name="fecha_fin" id="filtro_fin" value="<?php echo e(request('fecha_fin')); ?>">
        </div>
        
        <div class="elegant-group">
            <label for="filtro_estado">ESTADO</label>
            <select name="estado" id="filtro_estado">
                <option value="">Todos los estados</option>
                <option value="CREADO" <?php echo e(request('estado') == 'CREADO' ? 'selected' : ''); ?>>Creado</option>
                <option value="EN_COCINA" <?php echo e(request('estado') == 'EN_COCINA' ? 'selected' : ''); ?>>En Cocina</option>
                <option value="LISTO" <?php echo e(request('estado') == 'LISTO' ? 'selected' : ''); ?>>Listo</option>
                <option value="ENTREGADO" <?php echo e(request('estado') == 'ENTREGADO' ? 'selected' : ''); ?>>Entregado</option>
                <option value="CANCELADO" <?php echo e(request('estado') == 'CANCELADO' ? 'selected' : ''); ?>>Cancelado</option>
            </select>
        </div>

        <div class="elegant-group">
            <label for="filtro_mesa">MESA</label>
            <select name="mesa_id" id="filtro_mesa">
                <option value="">Todas las mesas</option>
                <?php $__currentLoopData = $mesas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mesa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mesa->id); ?>" <?php echo e(request('mesa_id') == $mesa->id ? 'selected' : ''); ?>>Mesa <?php echo e($mesa->numero); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="elegant-group">
            <label for="filtro_mesero">MESERO</label>
            <select name="mesero_id" id="filtro_mesero">
                <option value="">Todos los meseros</option>
                <?php $__currentLoopData = $meseros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mesero): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($mesero->id); ?>" <?php echo e(request('mesero_id') == $mesero->id ? 'selected' : ''); ?>><?php echo e($mesero->nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="elegant-actions">
            <button type="submit" class="btn-filtrar">Filtrar</button>
            <?php if(request()->anyFilled(['fecha_inicio', 'fecha_fin', 'estado', 'mesa_id', 'mesero_id'])): ?>
                <a href="<?php echo e(route('admin.pedidos.index')); ?>" class="btn-limpiar" title="Limpiar filtros">✕</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<div class="tarjeta">
    <div class="tarjeta-header">
        Resultados: <?php echo e($pedidos->count()); ?> pedidos
    </div>

    <?php if($pedidos->isEmpty()): ?>
        <div class="vacio">No hay pedidos con esos criterios</div>
    <?php else: ?>
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
                <?php $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $claseEstado = 'badge-' . strtolower(str_replace('_', '_', $pedido->estado));
                ?>
                <tr>
                    <td class="texto-gris">#<?php echo e($pedido->id); ?></td>
                    <td>Mesa <?php echo e($pedido->sesionMesa?->mesa?->numero ?? '—'); ?></td>
                    <td><?php echo e($pedido->mesero?->nombre ?? '—'); ?></td>
                    <td class="texto-gris"><?php echo e($pedido->detalles->count()); ?> items</td>
                    <td class="precio">
                        <?php echo e($pedido->total ? '$' . number_format($pedido->total, 2) : '—'); ?>

                    </td>
                    <td>
                        <span class="badge <?php echo e($claseEstado); ?>"><?php echo e($pedido->estado); ?></span>
                    </td>
                    <td class="texto-gris"><?php echo e($pedido->created_at->format('d/m H:i')); ?></td>
                    <td>
                        <div class="acciones">
                            <a href="<?php echo e(route('admin.pedidos.detalle', $pedido->id)); ?>" class="btn-ver">
                                Ver detalle
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/admin/pedidos/index.blade.php ENDPATH**/ ?>