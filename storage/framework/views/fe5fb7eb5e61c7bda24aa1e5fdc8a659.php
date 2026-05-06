

<?php $__env->startSection('titulo', 'QR Mesa ' . $mesa->numero); ?>

<?php $__env->startSection('contenido'); ?>

<style>
    .pagina-header { margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; }
    .pagina-header h1 { font-family: 'DM Serif Display', serif; font-size: 1.8rem; font-weight: 400; }

    .btn-volver {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .45rem 1rem;
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
        color: rgba(247,243,238,0.6); border-radius: .6rem; font-size: .85rem;
        text-decoration: none; font-family: 'DM Sans', sans-serif;
        transition: all .2s;
    }

    .btn-volver:hover { background: rgba(255,255,255,0.09); color: #f7f3ee; }

    .qr-contenedor {
        max-width: 420px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
    }

    .qr-imagen {
        background: white;
        border-radius: .8rem;
        padding: 1rem;
        display: inline-block;
        margin-bottom: 1.5rem;
    }

    .qr-imagen img { display: block; width: 200px; height: 200px; }

    .url-box {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: .6rem;
        padding: .75rem 1rem;
        font-size: .8rem;
        color: rgba(247,243,238,0.5);
        word-break: break-all;
        margin-bottom: 1.2rem;
        text-align: left;
    }

    .acciones { display: flex; gap: .6rem; justify-content: center; flex-wrap: wrap; }

    .btn-copiar {
        padding: .6rem 1.2rem;
        background: rgba(201,168,76,0.12);
        border: 1px solid rgba(201,168,76,0.3);
        color: #c9a84c;
        border-radius: .6rem;
        font-family: 'DM Sans', sans-serif;
        font-size: .875rem;
        cursor: pointer;
        transition: background .2s;
    }

    .btn-copiar:hover { background: rgba(201,168,76,0.22); }

    .btn-probar {
        padding: .6rem 1.2rem;
        background: rgba(79,142,247,0.1);
        border: 1px solid rgba(79,142,247,0.25);
        color: #90cdf4;
        border-radius: .6rem;
        font-family: 'DM Sans', sans-serif;
        font-size: .875rem;
        text-decoration: none;
        transition: background .2s;
    }

    .btn-probar:hover { background: rgba(79,142,247,0.2); }

    .info-mesa {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 1.5rem;
    }

    .info-chip {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: .5rem;
        padding: .4rem .9rem;
        font-size: .8rem;
        color: rgba(247,243,238,0.6);
    }
</style>

<div class="pagina-header">
    <a href="<?php echo e(route('admin.mesas.index')); ?>" class="btn-volver">← Volver a mesas</a>
    <h1>QR — Mesa <?php echo e($mesa->numero); ?></h1>
</div>

<div class="qr-contenedor">

    <div class="info-mesa">
        <span class="info-chip">Mesa <?php echo e($mesa->numero); ?></span>
        <?php if($mesa->capacidad): ?>
            <span class="info-chip"><?php echo e($mesa->capacidad); ?> personas</span>
        <?php endif; ?>
        <span class="info-chip"><?php echo e($mesa->estado); ?></span>
    </div>

    <p style="color:rgba(247,243,238,0.45); font-size:.875rem; margin-bottom:1.5rem">
        El cliente escanea este QR con su celular para acceder al menú
    </p>

    
    <div class="qr-imagen">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo e(urlencode($urlQR)); ?>"
             alt="QR Mesa <?php echo e($mesa->numero); ?>">
    </div>

    <div class="url-box"><?php echo e($urlQR); ?></div>

    <div class="acciones">
        <button class="btn-copiar" onclick="copiarURL()">Copiar enlace</button>
        <a href="<?php echo e($urlQR); ?>" target="_blank" class="btn-probar">Probar QR</a>
    </div>

</div>

<script>
    function copiarURL() {
        navigator.clipboard.writeText('<?php echo e($urlQR); ?>').then(() => {
            const btn = document.querySelector('.btn-copiar');
            btn.textContent = 'Copiado';
            setTimeout(() => btn.textContent = 'Copiar enlace', 2000);
        });
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/admin/mesas/qr.blade.php ENDPATH**/ ?>