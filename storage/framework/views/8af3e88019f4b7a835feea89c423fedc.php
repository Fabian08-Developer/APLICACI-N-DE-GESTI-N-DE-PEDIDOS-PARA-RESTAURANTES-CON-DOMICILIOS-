<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido — Mesa <?php echo e($mesa->numero); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garant:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/acceso.css'); ?>
</head>

<body>

    
    <div class="fondo-grano"></div>
    <div class="fondo-glow"></div>

    <div class="contenedor">

        
        <div class="logo" style="animation-delay: 0s">
            <div class="logo-icono">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 8h1a4 4 0 0 1 0 8h-1" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 1v3M10 1v3M14 1v3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="logo-nombre">Café Aroma</span>
        </div>

        
        <div class="linea-deco" style="animation-delay: 0.1s">
            <span></span><span class="punto"></span><span></span>
        </div>

        
        <div class="bienvenida" style="animation-delay: 0.2s">
            <p class="saludo">Bienvenido a tu mesa</p>
            <h1 class="mesa-num"><?php echo e($mesa->numero); ?></h1>
            <?php if($mesa->capacidad): ?>
            <?php
                $ocupados = \App\Models\SesionMesa::where('mesa_id', $mesa->id)
                    ->where('estado', 'ACTIVA')->count();
                $disponibles = $mesa->capacidad - $ocupados;
            ?>
            <p class="capacidad"><?php echo e($disponibles); ?> de <?php echo e($mesa->capacidad); ?> puestos disponibles</p>
            <?php endif; ?>
        </div>

        
        <p class="descripcion" style="animation-delay: 0.3s">
            Cafés de origen, bebidas artesanales<br>y delicias horneadas cada mañana.
        </p>

        <?php if(session('error')): ?>
        <div class="alerta-error" style="animation-delay: 0.35s">
            <?php echo e(session('error')); ?>

        </div>
        <?php endif; ?>

        
        <form method="POST" action="<?php echo e(route('cliente.sesion.individual')); ?>" style="animation-delay: 0.4s; width:100%">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="mesa_id" value="<?php echo e($mesa->id); ?>">
            <button type="submit" class="btn-menu">
                <span class="btn-menu-icono">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 7h8M8 11h6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="btn-menu-texto">
                    <strong>Ver el menú</strong>
                    <small>Pide desde tu mesa</small>
                </span>
                <span class="btn-menu-flecha">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>
        </form>

        
        <div class="pie" style="animation-delay: 0.5s">
            <p>Hecho con amor y granos selectos</p>
        </div>

    </div>
</body>
</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cliente/acceso.blade.php ENDPATH**/ ?>