<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/menu.css', 'resources/js/menu.js']); ?>
    <style>
        /* Ocultar secciones temporalmente durante la carga (evita parpadeo) */
        .seccion { display: none; }
    </style>
    <script>
        // Mostrar la categoría elegida (o la primera) antes de que el body se pinte
        const activeCatId = sessionStorage.getItem('activeCatId');
        if (activeCatId) {
            document.write('<style>#cat-' + activeCatId + ' { display: block; }</style>');
        }
    </script>
</head>

<body>

    
    <div class="header">
        <div>
            <div class="header-logo">Cafeteria</div>
        </div>

        <button type="button" class="btn-logout" onclick="abrirModalLogout()">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            Salir
        </button>
    </div>

    
    <div class="cat-nav-wrapper">
        <div class="cat-nav">
            <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button class="cat-btn" id="btn-cat-<?php echo e($cat->id); ?>"
                data-cat="<?php echo e($cat->id); ?>"
                onclick="filtrarCategoria(<?php echo e($cat->id); ?>, this)">
                <?php echo e($cat->nombre); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        
        <script>
            // Activar visualmente el botón correcto y aplicar display al DOM cuando cargue
            document.addEventListener('DOMContentLoaded', function() {
                const savedCat = sessionStorage.getItem('activeCatId');
                let btn;
                if (savedCat) {
                    btn = document.getElementById('btn-cat-' + savedCat);
                }
                if (!btn) {
                    btn = document.querySelector('.cat-btn'); // Primer botón por defecto
                }
                if (btn) {
                    btn.classList.add('activo');
                    const catId = btn.getAttribute('data-cat');
                    const section = document.getElementById('cat-' + catId);
                    if (section) section.style.display = 'block';
                }
            });
        </script>

        <button class="btn-carrito-toggle" onclick="abrirCarrito()">
            Carrito
            <span class="carrito-badge <?php echo e(count($carrito) > 0 ? 'visible' : ''); ?>" id="badge">
                <?php echo e(count($carrito)); ?>

            </span>
        </button>
    </div>

    
    <?php if(session('exito')): ?>
    <div class="alerta alerta-exito"><?php echo e(session('exito')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="alerta alerta-error"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    
    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="seccion" id="cat-<?php echo e($categoria->id); ?>">
        <div class="seccion-titulo"><?php echo e($categoria->nombre); ?></div>
        <div class="productos-grid">
            <?php $__currentLoopData = $categoria->productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="producto-card">
                <div class="producto-imagen" aria-hidden="true">
                    <?php if($producto->imagen): ?>
                        <img src="<?php echo e(asset('storage/' . $producto->imagen)); ?>" alt="<?php echo e($producto->nombre); ?>">
                    <?php else: ?>
                        <div class="sin-imagen">🍽</div>
                    <?php endif; ?>
                </div>
                <div class="producto-info">
                    <div class="producto-nombre"><?php echo e($producto->nombre); ?></div>
                    <div class="producto-precio">$<?php echo e(number_format($producto->precio, 2)); ?></div>
                    <?php if($producto->descripcion): ?>
                    <div class="producto-desc"><?php echo e(Str::limit($producto->descripcion, 60)); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('cliente.carrito.agregar')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="_t" value="<?php echo e($token); ?>">
                        <input type="hidden" name="producto_id" value="<?php echo e($producto->id); ?>">
                        <div class="cantidad-control" style="margin-bottom: .55rem">
                            <button type="button" class="btn-cantidad"
                                onclick="cambiarCantidad(this, -1)" aria-label="Reducir cantidad">−</button>
                            <span class="cantidad-num">1</span>
                            <button type="button" class="btn-cantidad"
                                onclick="cambiarCantidad(this, 1)" aria-label="Aumentar cantidad">+</button>
                            <input type="hidden" name="cantidad" value="1" class="input-cantidad">
                        </div>
                        <button type="submit" class="btn-agregar">Agregar al carrito</button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


    
    <div class="sidebar-overlay" id="overlay" onclick="cerrarCarrito()"></div>

    
    <div class="carrito-sidebar" id="carritoSidebar" role="dialog" aria-label="Tu carrito">

        <div class="sidebar-header">
            <div>
                <span class="sidebar-titulo">Tu carrito</span>
                <span class="sidebar-count"><?php echo e(count($carrito)); ?> <?php echo e(count($carrito) === 1 ? 'producto' : 'productos'); ?></span>
            </div>
            <button class="btn-cerrar-sidebar" onclick="cerrarCarrito()" aria-label="Cerrar carrito">✕</button>
        </div>

        <div class="sidebar-items">
            <?php if(empty($carrito)): ?>
            <div class="sidebar-vacio">
                <div class="sidebar-vacio-icon" aria-hidden="true">—</div>
                <span>Tu carrito está vacío.<br>Agrega algo del menú para empezar.</span>
            </div>
            <?php else: ?>
            <?php $__currentLoopData = $carrito; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productoId => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="carrito-item" id="item-<?php echo e($productoId); ?>">
                <div class="item-imagen" aria-hidden="true">
                    <?php if($item['imagen']): ?>
                        <img src="<?php echo e(asset('storage/' . $item['imagen'])); ?>" alt="">
                    <?php else: ?>
                        <div class="sin-imagen-min">🍽</div>
                    <?php endif; ?>
                </div>
                <div class="item-info">
                    <div class="item-nombre"><?php echo e($item['nombre']); ?></div>
                    <div class="item-precio" id="sub-<?php echo e($productoId); ?>">$<?php echo e(number_format($item['precio'] * $item['cantidad'], 2)); ?></div>
                </div>
                <div class="qty-sidebar">
                    <button type="button" onclick="actualizarCantidad(<?php echo e($productoId); ?>, -1)" aria-label="Reducir">−</button>
                    <span id="qty-<?php echo e($productoId); ?>"><?php echo e($item['cantidad']); ?></span>
                    <button type="button" onclick="actualizarCantidad(<?php echo e($productoId); ?>, 1)" aria-label="Aumentar">+</button>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-total">
                <span class="total-label">Total del pedido</span>
                <span class="total-valor">$<?php echo e(number_format($totalCarrito, 2)); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('cliente.pedido.confirmar')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_t" value="<?php echo e($token); ?>">
                <button type="submit" class="btn-confirmar" <?php echo e(empty($carrito) ? 'disabled' : ''); ?>>
                    Confirmar pedido
                </button>
            </form>
        </div>
    </div>

    
    <div class="modal-logout" id="modalLogout" role="dialog" aria-modal="true" aria-labelledby="tituloLogout">
        <div class="modal-box">
            <div class="modal-icono" aria-hidden="true">👋</div>
            <div class="modal-titulo" id="tituloLogout">¿Deseas cerrar tu sesión?</div>
            <p class="modal-texto">
                Tu carrito actual se eliminará. Para volver a pedir, necesitarás escanear el código QR de la mesa.
            </p>
            <div class="modal-acciones">
                <button class="btn-seguir" onclick="cerrarModalLogout()">Cancelar</button>
                <button class="btn-salir-ahora" onclick="cerrarSesion()">Confirmar salida</button>
            </div>
        </div>
    </div>

    
    <div class="modal-inactividad" id="modalInactividad" role="dialog" aria-modal="true" aria-labelledby="tituloInactividad">
        <div class="modal-box">
            <div class="modal-icono" aria-hidden="true">⏱</div>
            <div class="modal-titulo" id="tituloInactividad">¿Sigues ahí?</div>
            <p class="modal-texto">Por inactividad, cerraremos tu sesión automáticamente en:</p>
            <div class="modal-countdown">
                <span id="cuentaRegresiva">60</span>
                <span>segundos</span>
            </div>
            <div class="modal-acciones">
                <button class="btn-seguir" onclick="reiniciarInactividad()">Seguir pidiendo</button>
                <button class="btn-salir-ahora" onclick="cerrarSesion()">Cerrar sesión</button>
            </div>
        </div>
    </div>

    
    <form id="formLogout" method="POST" action="<?php echo e(route('cliente.logout')); ?>" style="display:none">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="_t" value="<?php echo e($token); ?>">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.initMenu) {
                window.initMenu({
                    csrf: '<?php echo e(csrf_token()); ?>',
                    token: '<?php echo e($token); ?>',
                    routes: {
                        logoutInactividad: "<?php echo e(route('cliente.logout.inactividad')); ?>",
                        sesionCerrada: "<?php echo e(route('cliente.sin-sesion')); ?>"
                    }
                });
            }
        });
    </script>


</body>

</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cliente/menu.blade.php ENDPATH**/ ?>