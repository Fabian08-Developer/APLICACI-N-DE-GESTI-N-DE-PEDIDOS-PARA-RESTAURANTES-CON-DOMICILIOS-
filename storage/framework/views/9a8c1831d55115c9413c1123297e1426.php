<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado — Mi Restaurante</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,500;0,600;1,300&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/sesion_cerrada.css'); ?>
</head>

<body>
    <div class="contenido" style="border-top-color: #ef4444;">
        <span class="icono" aria-hidden="true">⚠️</span>
        <h1 class="titulo" style="color: #1f2937;">Acceso denegado</h1>
        <p class="subtitulo" style="color: #4b5563; font-size: 1.1rem; line-height: 1.5; margin-bottom: 2rem;">
            <?php echo e(session('error', 'Tu sesión no es válida, expiró o la mesa fue liberada.')); ?><br><br>
            <strong>Por favor, vuelve a escanear el código QR que se encuentra en tu mesa para acceder.</strong>
        </p>
        <div class="linea" aria-hidden="true" style="background: rgba(239, 68, 68, 0.1);"></div>
        <div class="marca">Mi Restaurante</div>
    </div>
</body>

</html>
<?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/cliente/sin-sesion.blade.php ENDPATH**/ ?>