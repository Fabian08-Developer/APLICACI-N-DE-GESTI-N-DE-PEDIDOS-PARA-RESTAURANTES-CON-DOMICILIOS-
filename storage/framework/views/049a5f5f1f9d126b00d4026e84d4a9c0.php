<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('titulo'); ?> — Cocina</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/cocina.css', 'resources/js/cocina.js']); ?>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-name">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" opacity=".3"/>
                    <path d="M15 8.5c0-1.38-1.12-2.5-2.5-2.5S10 7.12 10 8.5c0 2.5 2.5 4 2.5 6.5"/>
                    <line x1="12.5" y1="17" x2="12.5" y2="17.5" stroke-linecap="round" stroke-width="2.5"/>
                </svg>
                Cocina
            </div>
            <div class="sidebar-logo-role">Panel de preparación</div>
        </div>

        <nav class="sidebar-nav">
            <a href="<?php echo e(route('cocina.dashboard')); ?>"
               class="nav-item <?php echo e(request()->is('cocina/dashboard') ? 'activo' : ''); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                    <line x1="9" y1="16" x2="13" y2="16"/>
                </svg>
                Pedidos
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo e(auth()->user()?->nombre ?? 'Cocina'); ?></div>
                    <div class="sidebar-user-role">Cocina</div>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <main class="contenido">
        <?php if(session('exito')): ?>
            <div class="alerta alerta-exito">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?php echo e(session('exito')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alerta alerta-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('contenido'); ?>
    </main>

    <?php echo $__env->make('partials.staff-token', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cocina/layout.blade.php ENDPATH**/ ?>