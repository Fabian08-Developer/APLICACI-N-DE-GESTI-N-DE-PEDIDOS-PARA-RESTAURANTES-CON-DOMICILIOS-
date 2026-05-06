<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('titulo'); ?> — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/admin.css', 'resources/js/admin.js']); ?>
</head>
<body>

    
    <header class="topbar">
        <button class="btn-hamburger" onclick="toggleSidebar()" id="btnHamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="topbar-logo">
            Mi Restaurante
            <small>Panel administrador</small>
        </div>
        <div class="topbar-spacer"></div>
        <div class="topbar-usuario">
            <strong><?php echo e(auth()->user()?->nombre ?? 'Administrador'); ?></strong>
            Administrador
        </div>
    </header>

    
    <div class="overlay" onclick="toggleSidebar()"></div>

    
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-marca">
                Mi Restaurante
                <small>Panel administrador</small>
            </div>
            <button class="btn-cerrar" onclick="toggleSidebar()">✕</button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">General</div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item <?php echo e(request()->is('admin/dashboard') ? 'activo' : ''); ?>" onclick="toggleSidebar()">
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></span> Dashboard
            </a>

            <div class="nav-label">Gestión</div>
            <a href="<?php echo e(route('admin.categorias.index')); ?>" class="nav-item <?php echo e(request()->is('admin/categorias*') ? 'activo' : ''); ?>" onclick="toggleSidebar()">
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></span> Categorías
            </a>
            <a href="<?php echo e(route('admin.productos.index')); ?>" class="nav-item <?php echo e(request()->is('admin/productos*') ? 'activo' : ''); ?>" onclick="toggleSidebar()">
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg></span> Productos
            </a>
            <a href="<?php echo e(route('admin.mesas.index')); ?>" class="nav-item <?php echo e(request()->is('admin/mesas*') ? 'activo' : ''); ?>" onclick="toggleSidebar()">
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/></svg></span> Mesas
            </a>
            <a href="<?php echo e(route('admin.pedidos.index')); ?>" class="nav-item <?php echo e(request()->is('admin/pedidos*') ? 'activo' : ''); ?>" onclick="toggleSidebar()">
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span> Pedidos
            </a>
            <a href="<?php echo e(route('admin.usuarios.index')); ?>" class="nav-item <?php echo e(request()->is('admin/usuarios*') ? 'activo' : ''); ?>" onclick="toggleSidebar()">
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span> Usuarios
            </a>
        </nav>

        <div class="sidebar-footer">
            <strong><?php echo e(auth()->user()?->nombre ?? 'Administrador'); ?></strong>
            Administrador
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn-logout">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    
    <main class="contenido">
        <?php if(session('exito')): ?>
            <div class="alerta alerta-exito"><?php echo e(session('exito')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alerta alerta-error"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('contenido'); ?>
    </main>

    <?php echo $__env->make('partials.staff-token', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/admin/layout.blade.php ENDPATH**/ ?>