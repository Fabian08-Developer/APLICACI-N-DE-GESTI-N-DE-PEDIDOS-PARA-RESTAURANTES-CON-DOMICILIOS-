<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pedido confirmado!</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/confirmacion.css', 'resources/js/confirmacion.js']); ?>
</head>
<body>
<div class="contenedor">

    <div class="icono-exito">✅</div>
    <h1>¡Pedido enviado!</h1>
    <p class="subtexto">Puedes seguir el estado de tu pedido aquí abajo.</p>

    <div class="tracker-card">
        <div class="tracker-barra-wrap">
            <div class="tracker-barra-bg">
                <div class="tracker-barra-fill" id="tracker-barra"></div>
            </div>
        </div>
        <div class="tracker-pasos">
            <div class="tracker-paso" id="paso-CREADO">
                <div class="paso-circulo"><span class="paso-check">✓</span><span class="paso-pulso"></span></div>
                <div class="paso-label">Recibido</div><div class="paso-sub">Esperando</div>
            </div>
            <div class="tracker-paso" id="paso-EN_COCINA">
                <div class="paso-circulo"><span class="paso-check">✓</span><span class="paso-pulso"></span></div>
                <div class="paso-label">Cocina</div><div class="paso-sub">Recibido</div>
            </div>
            <div class="tracker-paso" id="paso-EN_PREPARACION">
                <div class="paso-circulo"><span class="paso-check">✓</span><span class="paso-pulso"></span></div>
                <div class="paso-label">Preparando</div><div class="paso-sub">En marcha</div>
            </div>
            <div class="tracker-paso" id="paso-LISTO">
                <div class="paso-circulo"><span class="paso-check">✓</span><span class="paso-pulso"></span></div>
                <div class="paso-label">Listo</div><div class="paso-sub">¡Ya casi!</div>
            </div>
            <div class="tracker-paso" id="paso-ENTREGADO">
                <div class="paso-circulo"><span class="paso-check">✓</span><span class="paso-pulso"></span></div>
                <div class="paso-label">Entregado</div><div class="paso-sub">¡Disfruta!</div>
            </div>
        </div>
        <div class="tracker-mensaje" id="tracker-mensaje">
            <span class="tracker-dot" id="tracker-dot"></span>
            <span id="tracker-texto">Cargando estado...</span>
        </div>
    </div>

    <div class="tarjeta">
        <div class="tarjeta-header">
            <span>Tu pedido</span>
            <span class="pedido-num">#<?php echo e($pedido->id); ?></span>
        </div>
        <?php $__currentLoopData = $pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="item-pedido">
            <span>
                <span class="item-nombre"><?php echo e($detalle->producto?->nombre); ?></span>
                <span class="item-cant"> ×<?php echo e($detalle->cantidad); ?></span>
            </span>
            <span class="item-precio">$<?php echo e(number_format($detalle->subtotal, 2)); ?></span>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="total-row">
            <span>Total</span>
            <span>$<?php echo e(number_format($pedido->total, 2)); ?></span>
        </div>
    </div>

    
    <div id="wrap-cancelar" style="display:none">
        <button class="btn-cancelar" onclick="abrirModal()">
            ✕ Cancelar pedido
        </button>
    </div>

    <a href="<?php echo e(route('cliente.menu', ['t' => $token])); ?>" class="btn-volver-menu">+ Hacer otro pedido</a>

</div>


<div class="modal-overlay" id="modal-cancelar">
    <div class="modal-box">
        <div class="modal-titulo">¿Cancelar pedido?</div>
        <div class="modal-desc">
            Esta acción no puede deshacerse. El reembolso se procesará según el método original.
        </div>
        <label class="modal-label" for="motivo">Motivo (opcional)</label>
        <textarea id="motivo" class="modal-textarea" rows="2"
            placeholder="Ej: Cambio de opinión..."></textarea>
        <div class="modal-acciones">
            <button class="btn-modal-volver" onclick="cerrarModal()">No, volver</button>
            <button class="btn-modal-confirmar" id="btn-confirmar" onclick="confirmarCancelacion()">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.initConfirmacion) {
            window.initConfirmacion({
                pedidoId: <?php echo e($pedido->id); ?>,
                estadoInit: "<?php echo e($pedido->estado); ?>",
                rutaEstado: "<?php echo route('cliente.pedido.estado', [$pedido->id, 't' => $token]); ?>",
                rutaCancelar: "<?php echo route('cliente.pedido.cancelar', [$pedido->id, 't' => $token]); ?>",
                csrf: "<?php echo e(csrf_token()); ?>",
                intervaloMs: 6000
            });
        }
    });
</script>
</body>
</body>
</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cliente/confirmacion.blade.php ENDPATH**/ ?>