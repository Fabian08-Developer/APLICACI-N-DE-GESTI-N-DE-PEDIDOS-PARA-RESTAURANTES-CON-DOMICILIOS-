

<?php $__env->startSection('titulo', 'Resumen General'); ?>

<?php $__env->startSection('contenido'); ?>


<div class="pagina-header">
    <h1>Dashboard</h1>
    <p>Resumen del día — <?php echo e(now()->format('d/m/Y')); ?></p>
</div>


<div class="grid-tarjetas">
    <div class="tarjeta-stat">
        <span class="stat-icono"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
        <div class="stat-numero dorado"><?php echo e($pedidosHoy); ?></div>
        <div class="stat-label">Pedidos hoy</div>
    </div>

    <div class="tarjeta-stat">
        <span class="stat-icono"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/></svg></span>
        <div class="stat-numero verde"><?php echo e($mesasDisponibles); ?></div>
        <div class="stat-label">Mesas disponibles</div>
    </div>

    <div class="tarjeta-stat">
        <span class="stat-icono"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span>
        <div class="stat-numero"><?php echo e($totalUsuarios); ?></div>
        <div class="stat-label">Usuarios activos</div>
    </div>
</div>

<div class="grid-dos">

    
    <div class="tarjeta">
        <div class="tarjeta-header">Últimos pedidos registrados</div>

        <?php if($ultimosPedidos->isEmpty()): ?>
            <div class="vacio">No se han registrado pedidos hoy</div>
        <?php else: ?>
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
                    <?php $__currentLoopData = $ultimosPedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="font-weight: 500;">#<?php echo e($pedido->id); ?></td>
                        <td><?php echo e($pedido->mesero?->nombre ?? '—'); ?></td>
                        <td>
                            <?php
                                $claseBadge = match($pedido->estado) {
                                    'CREADO'    => 'badge-creado',
                                    'EN_COCINA' => 'badge-cocina',
                                    'LISTO'     => 'badge-listo',
                                    'CANCELADO' => 'badge-cancelado',
                                    default     => 'badge-default',
                                };
                            ?>
                            <span class="badge <?php echo e($claseBadge); ?>">
                                <?php echo e(str_replace('_', ' ', $pedido->estado)); ?>

                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            <?php echo e($pedido->created_at->format('d/m H:i')); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    
    <div class="tarjeta">
        <div class="tarjeta-header">Control de ocupación</div>

        <div class="mesas-grid">
            <div class="mesa-stat disponible">
                <div class="mesa-num"><?php echo e($mesasDisponibles); ?></div>
                <div class="mesa-label">Disponibles</div>
            </div>
            <div class="mesa-stat ocupada">
                <div class="mesa-num"><?php echo e($mesasOcupadas); ?></div>
                <div class="mesa-label">Ocupadas</div>
            </div>
        </div>

        <div style="padding: 0 1.5rem 2rem;">
            <?php if($totalMesas > 0): ?>
                <?php $porcentaje = round(($mesasOcupadas / $totalMesas) * 100); ?>
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.6rem;">
                    <span>Ocupación de la sala</span>
                    <span style="font-weight: 600; color: var(--text-main);"><?php echo e($porcentaje); ?>%</span>
                </div>
                <div class="progreso-vacia">
                    <div class="progreso-llena" style="width: <?php echo e($porcentaje); ?>%;"></div>
                </div>
            <?php else: ?>
                <div style="text-align: center; font-size: 0.85rem; color: var(--text-muted);">
                    No hay mesas configuradas
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    // Auto-recarga cada 60 segundos para mantener estadísticas frescas
    setInterval(() => {
        window.location.reload();
    }, 60000);
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>