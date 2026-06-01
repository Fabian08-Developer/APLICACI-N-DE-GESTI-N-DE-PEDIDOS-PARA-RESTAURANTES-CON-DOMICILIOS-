<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Digital — {{ $sesion->sucursal->nombre }}</title>
    <meta name="description" content="Disfruta del menú digital de {{ $sesion->sucursal->nombre }} y realiza tu pedido desde tu celular.">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/menu.css'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    {{-- HEADER --}}
    <header class="header">
        <div class="header-logo">
            {{ $sesion->sucursal->nombre }}
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="header-mesa">
                @if($sesion->tipo === 'local')
                    Mesa {{ $sesion->mesa->numero ?? 'N/A' }}
                @else
                    Domicilio: {{ $sesion->nombre_cliente }}
                @endif
            </div>
            
            {{-- Botón Abrir Carrito (RF-C16) --}}
            <button type="button" class="btn-carrito-toggle" id="btn-carrito-trigger">
                <span>Carrito</span>
                <span class="carrito-badge {{ $itemsCarrito->sum('cantidad') > 0 ? 'visible' : '' }}" id="carrito-badge-count">{{ $itemsCarrito->sum('cantidad') }}</span>
            </button>

            {{-- Botón Cerrar Sesión (RF-C09) --}}
            <button type="button" class="btn-logout" id="btn-logout-trigger">
                <span>Salir</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </button>
        </div>
    </header>

    {{-- CATEGORÍAS NAV --}}
    @if($categorias->count() > 0)
        <div class="cat-nav-wrapper">
            <nav class="cat-nav">
                @foreach($categorias as $key => $categoria)
                    <a href="#cat-{{ $categoria->id }}" class="cat-btn {{ $key === 0 ? 'activo' : '' }}">
                        {{ $categoria->nombre }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    {{-- MENU PRODUCTS --}}
    <main style="padding-bottom: 5rem;">
        @forelse($categorias as $categoria)
            @php
                $prodCat = $productos->where('categoria_id', $categoria->id);
            @endphp
            @if($prodCat->count() > 0)
                <section class="seccion" id="cat-{{ $categoria->id }}">
                    <h2 class="seccion-titulo">{{ $categoria->nombre }}</h2>
                    <div class="productos-grid">
                        @foreach($prodCat as $producto)
                            @php
                                $hasDescuento = $producto->precio_oferta && $producto->precio_oferta > 0;
                                $precioActual = $hasDescuento ? $producto->precio_oferta : $producto->precio;
                            @endphp
                            <div class="producto-card">
                                <div class="producto-imagen">
                                    @if($producto->imagen)
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                                    @else
                                        <span class="sin-imagen">☕</span>
                                    @endif
                                    @if($hasDescuento)
                                        <span class="tag-descuento">Oferta</span>
                                    @endif
                                </div>
                                <div class="producto-info">
                                    <h3 class="producto-nombre">{{ $producto->nombre }}</h3>
                                    <p class="producto-desc">{{ $producto->descripcion }}</p>
                                    <div class="producto-precio">
                                        @if($hasDescuento)
                                            <span class="precio-oferta">${{ number_format($producto->precio_oferta, 0, ',', '.') }}</span>
                                            <span class="precio-original-tachado">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                        @else
                                            ${{ number_format($producto->precio, 0, ',', '.') }}
                                        @endif
                                    </div>
                                    <button class="btn-agregar"
                                            data-id="{{ $producto->id }}"
                                            data-nombre="{{ $producto->nombre }}"
                                            data-descripcion="{{ $producto->descripcion }}"
                                            data-precio="{{ $producto->precio }}"
                                            data-precio-oferta="{{ $producto->precio_oferta }}"
                                            data-permite-notas="{{ $producto->permite_notas ? '1' : '0' }}"
                                            data-min-adiciones="{{ $producto->limite_minimo_adiciones }}"
                                            data-max-adiciones="{{ $producto->limite_maximo_adiciones }}"
                                            data-variantes='@json($producto->variantes)'
                                            data-adiciones='@json($producto->adiciones_disponibles)'>
                                        Agregar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @empty
            <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                <p>No hay productos disponibles en este momento.</p>
            </div>
        @endforelse
    </main>

    {{-- SIDEPANEL CARRITO (RF-C16) --}}
    <div class="sidebar-overlay" id="carrito-overlay"></div>
    <div class="carrito-sidebar" id="carrito-sidebar">
        <div class="sidebar-header">
            <h3 class="sidebar-titulo">Mi Pedido <span class="sidebar-count" id="carrito-header-count">({{ $itemsCarrito->sum('cantidad') }} items)</span></h3>
            <button type="button" class="btn-cerrar-sidebar" id="btn-carrito-close">&times;</button>
        </div>
        <div class="sidebar-items" id="carrito-items-container">
            @if($itemsCarrito->isEmpty())
                <div class="sidebar-vacio">
                    <span class="sidebar-vacio-icon">🛒</span>
                    <p>Tu carrito está vacío</p>
                </div>
            @else
                @foreach($itemsCarrito as $item)
                    <div class="carrito-item" data-id="{{ $item->id }}">
                        <div class="item-imagen">
                            @if($item->producto && $item->producto->imagen)
                                <img src="{{ asset('storage/' . $item->producto->imagen) }}" alt="{{ $item->nombre_producto }}">
                            @else
                                <span class="sin-imagen-min">☕</span>
                            @endif
                        </div>
                        <div class="item-info">
                            <div class="item-nombre">{{ $item->nombre_producto }}</div>
                            @if(!empty($item->variantes_elegidas))
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem;">
                                    @foreach($item->variantes_elegidas as $var)
                                        <span>{{ $var['grupo'] }}: {{ $var['opcion'] }}</span>{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($item->adiciones_elegidas))
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem;">
                                    + @foreach($item->adiciones_elegidas as $ad)
                                        <span>{{ $ad['nombre'] }} (${{ number_format($ad['precio'], 0, ',', '.') }})</span>{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                </div>
                            @endif
                            @if($item->notas)
                                <div style="font-size: 0.72rem; color: var(--text-muted); font-style: italic; margin-top: 0.1rem;">
                                    Nota: "{{ $item->notas }}"
                                </div>
                            @endif
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.4rem;">
                                <div class="qty-sidebar">
                                    <button type="button" class="btn-qty-dec" data-id="{{ $item->id }}">-</button>
                                    <span class="qty-num">{{ $item->cantidad }}</span>
                                    <button type="button" class="btn-qty-inc" data-id="{{ $item->id }}">+</button>
                                </div>
                                <span class="item-precio">${{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <button type="button" class="btn-quitar" data-id="{{ $item->id }}" title="Eliminar">&times;</button>
                    </div>
                @endforeach
            @endif
        </div>
        
        @php
            $costoEnvio = 0;
            if ($sesion->tipo === 'domicilio' && $sesion->zona_id) {
                $zona = \App\Models\ZonaCobertura::find($sesion->zona_id);
                if ($zona) {
                    $costoEnvio = (float) $zona->costo_envio;
                }
            }
            $subtotal = $itemsCarrito->sum('subtotal');
            $total = $subtotal + $costoEnvio;
        @endphp

        <div class="sidebar-footer">
            <div class="sidebar-total">
                <span class="total-label">Subtotal</span>
                <span class="total-valor" id="cart-subtotal-val">${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if($sesion->tipo === 'domicilio')
                <div class="sidebar-total" style="margin-top: -0.5rem; margin-bottom: 0.75rem;">
                    <span class="total-label">Envío</span>
                    <span class="total-valor" style="font-size: 1.0rem;" id="cart-envio-val">
                        @if($costoEnvio > 0)
                            ${{ number_format($costoEnvio, 0, ',', '.') }}
                        @else
                            Gratis
                        @endif
                    </span>
                </div>
            @endif
            <div class="sidebar-total" style="border-top: 1px dashed var(--border); padding-top: 0.75rem; margin-bottom: 1.25rem;">
                <span class="total-label" style="font-weight: 700; color: var(--text-main);">Total</span>
                <span class="total-valor" style="font-size: 1.5rem; font-weight: 700;" id="cart-total-val">${{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div id="form-confirmar-pedido">
                <button type="button" class="btn-confirmar" id="btn-confirmar-pedido" {{ $itemsCarrito->isEmpty() ? 'disabled' : '' }}>
                    Confirmar Pedido
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL PERSONALIZACIÓN (RF-C19, RF-C20) --}}
    <div class="modal-inactividad" id="modal-personalizacion" style="z-index: 150;">
        <div class="modal-box" style="max-width: 480px; text-align: left; display: flex; flex-direction: column; max-height: 90vh;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                <div style="max-width: 90%;">
                    <h3 class="modal-titulo" id="custom-prod-nombre" style="font-size: 1.35rem; margin-bottom: 0.15rem;">Nombre Producto</h3>
                    <p id="custom-prod-desc" style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.3;"></p>
                </div>
                <button type="button" class="btn-cerrar-sidebar" id="btn-custom-close" style="width: 28px; height: 28px; font-size: 1rem;">&times;</button>
            </div>
            
            <div style="flex: 1; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1rem;" id="custom-options-container">
                {{-- Dinámico con JS --}}
            </div>

            <div id="custom-validation-error" style="display: none; color: #dc2626; font-size: 0.78rem; margin-bottom: 0.75rem; background: rgba(220, 38, 38, 0.08); padding: 0.5rem; border-radius: 0.4rem; border: 1px solid rgba(220, 38, 38, 0.15);"></div>

            <div style="border-top: 1px solid var(--border); padding-top: 1rem; display: flex; align-items: center; justify-content: space-between;">
                <div class="cantidad-control">
                    <button type="button" class="btn-cantidad" id="btn-custom-dec">-</button>
                    <span class="cantidad-num" id="custom-qty-val">1</span>
                    <button type="button" class="btn-cantidad" id="btn-custom-inc">+</button>
                </div>
                <button type="button" class="btn-seguir" id="btn-custom-submit" style="flex: none; min-width: 160px;">
                    Agregar $<span id="custom-price-total">0</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL INACTIVIDAD (RF-C08) --}}
    <div class="modal-inactividad" id="modal-inactividad">
        <div class="modal-box">
            <div class="modal-icono">⏳</div>
            <h3 class="modal-titulo">¿Sigues ahí?</h3>
            <p class="modal-texto">
                Tu sesión está a punto de expirar debido a inactividad. ¿Deseas mantener tu sesión abierta?
            </p>
            <div class="modal-countdown">
                <span id="inactividad-countdown">120</span>
                <span>segundos restantes</span>
            </div>
            <div class="modal-acciones">
                <button type="button" class="btn-salir-ahora" id="btn-salir-sesion-ahora">Cerrar Sesión</button>
                <button type="button" class="btn-seguir" id="btn-seguir-sesion">Seguir</button>
            </div>
        </div>
    </div>

    {{-- MODAL LOGOUT MANUAL (RF-C11) --}}
    <div class="modal-logout" id="modal-logout">
        <div class="modal-box">
            <div class="modal-icono">👋</div>
            <h3 class="modal-titulo">¿Cerrar Sesión?</h3>
            <p class="modal-texto">
                Si decides cerrar sesión, se cancelarán tus pedidos pendientes y se liberará tu acceso. ¿Confirmas que deseas salir?
            </p>
            <div class="modal-acciones">
                <button type="button" class="btn-salir-ahora" id="btn-logout-cancel" style="border: 1px solid var(--border); color: var(--text-main);">Cancelar</button>
                <form action="{{ route('cliente.logout', ['t' => $token]) }}" method="POST" style="flex: 1; display: flex;">
                    @csrf
                    <button type="submit" class="btn-seguir" style="background: #dc2626; width: 100%;">Salir</button>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT INACTIVIDAD Y MODALS --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Helper de formato de moneda
            function formatCurrency(val) {
                return parseFloat(val).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            // Modal de Logout Manual (RF-C11)
            const btnLogoutTrigger = document.getElementById('btn-logout-trigger');
            const modalLogout = document.getElementById('modal-logout');
            const btnLogoutCancel = document.getElementById('btn-logout-cancel');

            if (btnLogoutTrigger && modalLogout && btnLogoutCancel) {
                btnLogoutTrigger.addEventListener('click', () => {
                    modalLogout.classList.add('visible');
                });
                btnLogoutCancel.addEventListener('click', () => {
                    modalLogout.classList.remove('visible');
                });
            }

            // Temporizador de Inactividad de 10 min y modal de advertencia de 120s (RF-C07, RF-C08)
            const modalInactividad = document.getElementById('modal-inactividad');
            const countdownEl = document.getElementById('inactividad-countdown');
            const btnSeguir = document.getElementById('btn-seguir-sesion');
            const btnSalirAhora = document.getElementById('btn-salir-sesion-ahora');

            let warningTimer;
            let countdownTimer;
            let secondsLeft = 120;

            const WARNING_TIME = 8 * 60 * 1000; // 8 minutos en milisegundos (480s)
            const COUNTDOWN_TIME = 120; // 120 segundos (2 minutos)

            function resetTimer() {
                if (modalInactividad.classList.contains('visible')) return;

                clearTimeout(warningTimer);
                warningTimer = setTimeout(showWarning, WARNING_TIME);
            }

            function showWarning() {
                modalInactividad.classList.add('visible');
                secondsLeft = COUNTDOWN_TIME;
                countdownEl.textContent = secondsLeft;

                clearInterval(countdownTimer);
                countdownTimer = setInterval(() => {
                    secondsLeft--;
                    countdownEl.textContent = secondsLeft;

                    if (secondsLeft <= 0) {
                        clearInterval(countdownTimer);
                        logoutDueToInactivity();
                    }
                }, 1000);
            }

            function logoutDueToInactivity() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('cliente.logout.inactividad', ['t' => $token]) }}";

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }

            function keepSessionAlive() {
                fetch("{{ route('cliente.menu', ['t' => $token]) }}")
                    .then(response => {
                        modalInactividad.classList.remove('visible');
                        clearInterval(countdownTimer);
                        resetTimer();
                    })
                    .catch(err => {
                        console.error("No se pudo renovar la sesión", err);
                    });
            }

            const events = ['mousemove', 'keypress', 'mousedown', 'touchstart', 'scroll', 'click'];
            events.forEach(event => {
                document.addEventListener(event, resetTimer, { passive: true });
            });

            if (btnSeguir) {
                btnSeguir.addEventListener('click', keepSessionAlive);
            }

            if (btnSalirAhora) {
                btnSalirAhora.addEventListener('click', logoutDueToInactivity);
            }

            resetTimer();

            // Navegación Activa de Categorías (resaltar botón activo al hacer scroll)
            const catButtons = document.querySelectorAll('.cat-btn');
            const sections = document.querySelectorAll('.seccion');

            window.addEventListener('scroll', () => {
                let currentSecId = '';
                sections.forEach(section => {
                    const top = section.offsetTop - 150;
                    if (window.scrollY >= top) {
                        currentSecId = section.getAttribute('id');
                    }
                });

                if (currentSecId) {
                    catButtons.forEach(btn => {
                        btn.classList.remove('activo');
                        if (btn.getAttribute('href') === `#${currentSecId}`) {
                            btn.classList.add('activo');
                        }
                    });
                }
            });

            // Smooth Scroll para categoría nav
            catButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = btn.getAttribute('href');
                    const targetSection = document.querySelector(targetId);
                    if (targetSection) {
                        const offset = 120;
                        const bodyRect = document.body.getBoundingClientRect().top;
                        const elementRect = targetSection.getBoundingClientRect().top;
                        const elementPosition = elementRect - bodyRect;
                        const offsetPosition = elementPosition - offset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // ─────────────────────────────────────────────────────────────────
            // CARRITO SIDEBAR Y AJAX
            // ─────────────────────────────────────────────────────────────────
            const cartTrigger = document.getElementById('btn-carrito-trigger');
            const cartClose = document.getElementById('btn-carrito-close');
            const cartOverlay = document.getElementById('carrito-overlay');
            const cartSidebar = document.getElementById('carrito-sidebar');

            function abrirCarritoSidebar() {
                cartSidebar.classList.add('abierto');
                cartOverlay.classList.add('activo');
            }

            function cerrarCarritoSidebar() {
                cartSidebar.classList.remove('abierto');
                cartOverlay.classList.remove('activo');
            }

            if (cartTrigger) cartTrigger.addEventListener('click', abrirCarritoSidebar);
            if (cartClose) cartClose.addEventListener('click', cerrarCarritoSidebar);
            if (cartOverlay) cartOverlay.addEventListener('click', cerrarCarritoSidebar);

            // Refrescar contenido del carrito usando DOMParser
            function refreshCart() {
                return fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const currentContainer = document.getElementById('carrito-items-container');
                        const newContainer = doc.getElementById('carrito-items-container');
                        if (currentContainer && newContainer) {
                            currentContainer.innerHTML = newContainer.innerHTML;
                        }
                        
                        const currentBadge = document.getElementById('carrito-badge-count');
                        const newBadge = doc.getElementById('carrito-badge-count');
                        if (currentBadge && newBadge) {
                            currentBadge.textContent = newBadge.textContent;
                            if (parseInt(newBadge.textContent) > 0) {
                                currentBadge.classList.add('visible');
                            } else {
                                currentBadge.classList.remove('visible');
                            }
                        }
                        
                        const currentHeaderCount = document.getElementById('carrito-header-count');
                        const newHeaderCount = doc.getElementById('carrito-header-count');
                        if (currentHeaderCount && newHeaderCount) {
                            currentHeaderCount.textContent = newHeaderCount.textContent;
                        }
                        
                        const subtotalVal = document.getElementById('cart-subtotal-val');
                        const newSubtotalVal = doc.getElementById('cart-subtotal-val');
                        if (subtotalVal && newSubtotalVal) subtotalVal.textContent = newSubtotalVal.textContent;
                        
                        const envioVal = document.getElementById('cart-envio-val');
                        const newEnvioVal = doc.getElementById('cart-envio-val');
                        if (envioVal && newEnvioVal) envioVal.textContent = newEnvioVal.textContent;
                        
                        const totalVal = document.getElementById('cart-total-val');
                        const newTotalVal = doc.getElementById('cart-total-val');
                        if (totalVal && newTotalVal) totalVal.textContent = newTotalVal.textContent;
                        
                        const confirmBtn = document.getElementById('btn-confirmar-pedido');
                        if (confirmBtn) {
                            // Habilitamos/deshabilitamos según la cantidad del badge,
                            // porque DOMParser no refleja atributos booleanos de forma confiable
                            const badgeCount = parseInt(doc.getElementById('carrito-badge-count')?.textContent || '0');
                            confirmBtn.disabled = badgeCount === 0;
                        }
                    })
                    .catch(err => console.error("Error al refrescar el carrito:", err));
            }

            // AJAX: Actualizar cantidad de item
            function updateCartItemQuantity(id, qty) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                fetch(`/cliente/carrito/actualizar/${id}?t={{ $token }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cantidad: qty })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        refreshCart();
                    } else {
                        alert(data.error || 'Ocurrió un error al actualizar la cantidad.');
                    }
                })
                .catch(err => console.error("Error updating quantity:", err));
            }

            // AJAX: Eliminar item del carrito
            function removeCartItem(id) {
                if (!confirm('¿Deseas quitar este producto de tu carrito?')) return;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                fetch(`/cliente/carrito/eliminar/${id}?t={{ $token }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        refreshCart();
                    } else {
                        alert(data.error || 'Ocurrió un error al eliminar el item.');
                    }
                })
                .catch(err => console.error("Error removing item:", err));
            }

            // Event delegation en el listado del carrito
            const itemsContainer = document.getElementById('carrito-items-container');
            if (itemsContainer) {
                itemsContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('btn-qty-dec')) {
                        const id = e.target.dataset.id;
                        const qtyNumEl = e.target.nextElementSibling;
                        let qty = parseInt(qtyNumEl.textContent);
                        if (qty > 1) {
                            updateCartItemQuantity(id, qty - 1);
                        }
                    }
                    
                    if (e.target.classList.contains('btn-qty-inc')) {
                        const id = e.target.dataset.id;
                        const qtyNumEl = e.target.previousElementSibling;
                        let qty = parseInt(qtyNumEl.textContent);
                        updateCartItemQuantity(id, qty + 1);
                    }
                    
                    if (e.target.classList.contains('btn-quitar')) {
                        const id = e.target.dataset.id;
                        removeCartItem(id);
                    }
                });
            }

            // Confirmar pedido via AJAX (RF-C23)
            const btnConfirmar = document.getElementById('btn-confirmar-pedido');
            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', () => {
                    const badge = document.getElementById('carrito-badge-count');
                    const count = badge ? parseInt(badge.textContent || '0') : 0;
                    if (count === 0) {
                        alert('El carrito está vacío. Agrega productos antes de confirmar tu pedido.');
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    btnConfirmar.disabled = true;
                    btnConfirmar.textContent = 'Procesando...';

                    fetch(`/cliente/pedido/confirmar?t={{ $token }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.redirigir) {
                            window.location.href = data.redirigir;
                        } else {
                            alert(data.error || 'Ocurrió un error al confirmar el pedido.');
                            btnConfirmar.disabled = false;
                            btnConfirmar.textContent = 'Confirmar Pedido';
                        }
                    })
                    .catch(err => {
                        console.error('Error al confirmar pedido:', err);
                        alert('Ocurrió un error de red. Inténtalo de nuevo.');
                        btnConfirmar.disabled = false;
                        btnConfirmar.textContent = 'Confirmar Pedido';
                    });
                });
            }

            // ─────────────────────────────────────────────────────────────────
            // MODAL PERSONALIZACIÓN
            // ─────────────────────────────────────────────────────────────────
            const modalCustom = document.getElementById('modal-personalizacion');
            const customSubmitBtn = document.getElementById('btn-custom-submit');
            const customCloseBtn = document.getElementById('btn-custom-close');
            const customPriceTotal = document.getElementById('custom-price-total');
            const customQtyVal = document.getElementById('custom-qty-val');
            
            // Cerrar modal
            if (customCloseBtn) {
                customCloseBtn.addEventListener('click', () => {
                    modalCustom.classList.remove('visible');
                });
            }

            // Delegación de clic en botones "Agregar" de los productos
            document.body.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-agregar')) {
                    const button = e.target;
                    const id = button.dataset.id;
                    const nombre = button.dataset.nombre;
                    const descripcion = button.dataset.descripcion;
                    const precio = parseFloat(button.dataset.precio || 0);
                    const precioOferta = parseFloat(button.dataset.precioOferta || 0);
                    const permiteNotas = button.dataset.permiteNotas;
                    const minAdiciones = parseInt(button.dataset.minAdiciones || 0);
                    const maxAdiciones = button.dataset.maxAdiciones;
                    const variantes = JSON.parse(button.dataset.variantes || '[]');
                    const adiciones = JSON.parse(button.dataset.adiciones || '[]');
                    
                    const productObj = {
                        id, nombre, descripcion, precio, precioOferta, permiteNotas, minAdiciones, maxAdiciones, variantes, adiciones
                    };
                    
                    if (variantes.length === 0 && adiciones.length === 0 && permiteNotas === '0') {
                        // Es un producto simple, agregar directo
                        addToCartDirect(id, 1);
                    } else {
                        // Producto personalizable, abrir modal
                        window.activeProduct = productObj;
                        openCustomizationModal(productObj);
                    }
                }
            });

            // Agregar directamente al carrito
            function addToCartDirect(id, qty) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const payload = {
                    producto_id: id,
                    cantidad: qty,
                    variantes_elegidas: {},
                    adiciones_elegidas: [],
                    notas: null
                };
                
                fetch(`/cliente/carrito/agregar?t={{ $token }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        refreshCart();
                        abrirCarritoSidebar();
                    } else {
                        alert(data.error || 'Ocurrió un error al agregar el producto.');
                    }
                })
                .catch(err => console.error("Error direct add:", err));
            }

            // Abrir y renderizar modal
            function openCustomizationModal(product) {
                const nameEl = document.getElementById('custom-prod-nombre');
                const descEl = document.getElementById('custom-prod-desc');
                const optionsContainer = document.getElementById('custom-options-container');
                const errorEl = document.getElementById('custom-validation-error');
                
                errorEl.style.display = 'none';
                nameEl.textContent = product.nombre;
                descEl.textContent = product.descripcion || '';
                optionsContainer.innerHTML = '';
                
                modalCustom.dataset.productId = product.id;
                modalCustom.dataset.basePrecio = product.precio;
                modalCustom.dataset.precioOferta = product.precioOferta;
                modalCustom.dataset.permiteNotas = product.permiteNotas;
                modalCustom.dataset.minAdiciones = product.minAdiciones;
                modalCustom.dataset.maxAdiciones = product.maxAdiciones;
                
                customQtyVal.textContent = '1';
                
                // 1. Renderizar variantes
                if (product.variantes && product.variantes.length > 0) {
                    product.variantes.forEach(variante => {
                        const groupDiv = document.createElement('div');
                        groupDiv.className = 'custom-variant-group';
                        
                        const groupTitle = document.createElement('div');
                        groupTitle.className = 'custom-group-title';
                        groupTitle.textContent = `${variante.nombre}${variante.obligatorio ? ' * (Obligatorio)' : ''}`;
                        groupDiv.appendChild(groupTitle);
                        
                        const listDiv = document.createElement('div');
                        listDiv.className = 'custom-options-list';
                        
                        variante.opciones.forEach((opcion, idx) => {
                            const labelWrapper = document.createElement('label');
                            labelWrapper.className = 'custom-option-label-wrapper';
                            
                            const input = document.createElement('input');
                            input.type = 'radio';
                            input.name = `variante_${variante.nombre}`;
                            input.value = opcion.nombre;
                            input.className = 'custom-option-input';
                            input.dataset.precio = opcion.precio;
                            input.dataset.tipoImpacto = opcion.tipo_impacto || 'incremental';
                            input.dataset.grupo = variante.nombre;
                            
                            if (variante.obligatorio && idx === 0) {
                                input.checked = true;
                            }
                            
                            const label = document.createElement('div');
                            label.className = 'custom-option-label';
                            
                            const nameSpan = document.createElement('span');
                            nameSpan.textContent = opcion.nombre;
                            label.appendChild(nameSpan);
                            
                            if (parseFloat(opcion.precio) !== 0) {
                                const priceSpan = document.createElement('span');
                                priceSpan.className = 'option-price';
                                const sign = (opcion.tipo_impacto === 'fijo') ? '' : '+';
                                priceSpan.textContent = `${sign}$${formatCurrency(opcion.precio)}`;
                                label.appendChild(priceSpan);
                            }
                            
                            labelWrapper.appendChild(input);
                            labelWrapper.appendChild(label);
                            listDiv.appendChild(labelWrapper);
                            
                            input.addEventListener('change', updateModalPrice);
                        });
                        
                        groupDiv.appendChild(listDiv);
                        optionsContainer.appendChild(groupDiv);
                    });
                }
                
                // 2. Renderizar adiciones
                if (product.adiciones && product.adiciones.length > 0) {
                    const groupDiv = document.createElement('div');
                    groupDiv.className = 'custom-variant-group';
                    
                    const groupTitle = document.createElement('div');
                    groupTitle.className = 'custom-group-title';
                    
                    let addNotes = '';
                    if (product.minAdiciones > 0 || product.maxAdiciones !== null) {
                        const minT = product.minAdiciones > 0 ? `mín. ${product.minAdiciones}` : '';
                        const maxT = product.maxAdiciones !== null ? `máx. ${product.maxAdiciones}` : '';
                        addNotes = ` (${minT}${minT && maxT ? ', ' : ''}${maxT})`;
                    }
                    groupTitle.textContent = `Adiciones${addNotes}`;
                    groupDiv.appendChild(groupTitle);
                    
                    const listDiv = document.createElement('div');
                    listDiv.className = 'custom-options-list';
                    
                    product.adiciones.forEach(adicion => {
                        const labelWrapper = document.createElement('label');
                        labelWrapper.className = 'custom-option-label-wrapper';
                        
                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.name = 'adiciones[]';
                        input.value = adicion.id;
                        input.className = 'custom-option-input';
                        input.dataset.precio = adicion.precio;
                        input.dataset.nombre = adicion.nombre;
                        
                        const label = document.createElement('div');
                        label.className = 'custom-option-label';
                        
                        const nameSpan = document.createElement('span');
                        nameSpan.textContent = adicion.nombre;
                        label.appendChild(nameSpan);
                        
                        const priceSpan = document.createElement('span');
                        priceSpan.className = 'option-price';
                        priceSpan.textContent = `+$${formatCurrency(adicion.precio)}`;
                        label.appendChild(priceSpan);
                        
                        labelWrapper.appendChild(input);
                        labelWrapper.appendChild(label);
                        listDiv.appendChild(labelWrapper);
                        
                        input.addEventListener('change', updateModalPrice);
                    });
                    
                    groupDiv.appendChild(listDiv);
                    optionsContainer.appendChild(groupDiv);
                }
                
                // 3. Notas
                if (product.permiteNotas === '1') {
                    const groupDiv = document.createElement('div');
                    groupDiv.className = 'custom-variant-group';
                    
                    const groupTitle = document.createElement('div');
                    groupTitle.className = 'custom-group-title';
                    groupTitle.textContent = 'Notas / Instrucciones Especiales';
                    groupDiv.appendChild(groupTitle);
                    
                    const textarea = document.createElement('textarea');
                    textarea.id = 'custom-notes-input';
                    textarea.className = 'custom-textarea';
                    textarea.placeholder = 'Ej: Sin azúcar, hielo extra, etc.';
                    groupDiv.appendChild(textarea);
                    
                    optionsContainer.appendChild(groupDiv);
                }
                
                modalCustom.classList.add('visible');
                updateModalPrice();
            }

            // Calcular y actualizar precio del modal en tiempo real
            function updateModalPrice() {
                const basePrice = parseFloat(modalCustom.dataset.basePrecio || 0);
                const precioOferta = parseFloat(modalCustom.dataset.precioOferta || 0);
                const qty = parseInt(customQtyVal.textContent);
                
                let calculatedBase = (precioOferta > 0) ? precioOferta : basePrice;
                
                const radios = modalCustom.querySelectorAll('input[type="radio"]:checked');
                let sumFijo = 0;
                let hasFijo = false;
                let sumIncremental = 0;
                
                radios.forEach(radio => {
                    const price = parseFloat(radio.dataset.precio || 0);
                    const impact = radio.dataset.tipoImpacto;
                    if (impact === 'fijo') {
                        sumFijo += price;
                        hasFijo = true;
                    } else {
                        sumIncremental += price;
                    }
                });
                
                if (hasFijo) {
                    calculatedBase = sumFijo;
                }
                
                let totalUnitPrice = calculatedBase + sumIncremental;
                
                const checkboxes = modalCustom.querySelectorAll('input[type="checkbox"]:checked');
                checkboxes.forEach(checkbox => {
                    totalUnitPrice += parseFloat(checkbox.dataset.precio || 0);
                });
                
                const totalPrice = totalUnitPrice * qty;
                customPriceTotal.textContent = formatCurrency(totalPrice);
            }

            // Cantidades del modal
            const btnCustomDec = document.getElementById('btn-custom-dec');
            const btnCustomInc = document.getElementById('btn-custom-inc');
            
            if (btnCustomDec) {
                btnCustomDec.addEventListener('click', () => {
                    let qty = parseInt(customQtyVal.textContent);
                    if (qty > 1) {
                        customQtyVal.textContent = qty - 1;
                        updateModalPrice();
                    }
                });
            }
            
            if (btnCustomInc) {
                btnCustomInc.addEventListener('click', () => {
                    let qty = parseInt(customQtyVal.textContent);
                    customQtyVal.textContent = qty + 1;
                    updateModalPrice();
                });
            }

            // Enviar personalización al carrito
            if (customSubmitBtn) {
                customSubmitBtn.addEventListener('click', () => {
                    const productId = modalCustom.dataset.productId;
                    const errorEl = document.getElementById('custom-validation-error');
                    errorEl.style.display = 'none';
                    
                    const activeProduct = window.activeProduct;
                    const variantesElegidas = {};
                    
                    // Validar variantes obligatorias
                    if (activeProduct.variantes && activeProduct.variantes.length > 0) {
                        for (let v of activeProduct.variantes) {
                            const checked = modalCustom.querySelector(`input[name="variante_${v.nombre}"]:checked`);
                            if (v.obligatorio && !checked) {
                                errorEl.textContent = `La variante "${v.nombre}" es obligatoria.`;
                                errorEl.style.display = 'block';
                                return;
                            }
                            if (checked) {
                                variantesElegidas[v.nombre] = checked.value;
                            }
                        }
                    }
                    
                    // Validar límites de adiciones
                    const checkedAdditions = modalCustom.querySelectorAll('input[type="checkbox"]:checked');
                    const adicionesElegidas = [];
                    checkedAdditions.forEach(cb => {
                        adicionesElegidas.push(cb.value);
                    });
                    
                    const minAdd = parseInt(modalCustom.dataset.minAdiciones || 0);
                    const maxAdd = modalCustom.dataset.maxAdiciones === 'null' || modalCustom.dataset.maxAdiciones === '' ? null : parseInt(modalCustom.dataset.maxAdiciones);
                    
                    if (minAdd > 0 && adicionesElegidas.length < minAdd) {
                        errorEl.textContent = `Debes seleccionar al menos ${minAdd} adiciones.`;
                        errorEl.style.display = 'block';
                        return;
                    }
                    if (maxAdd !== null && adicionesElegidas.length > maxAdd) {
                        errorEl.textContent = `No puedes seleccionar más de ${maxAdd} adiciones.`;
                        errorEl.style.display = 'block';
                        return;
                    }
                    
                    // Notas
                    const notesInput = document.getElementById('custom-notes-input');
                    const notas = notesInput ? notesInput.value : null;
                    
                    // Cantidad
                    const cantidad = parseInt(customQtyVal.textContent);
                    
                    const payload = {
                        producto_id: productId,
                        cantidad: cantidad,
                        variantes_elegidas: variantesElegidas,
                        adiciones_elegidas: adicionesElegidas,
                        notas: notas
                    };
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    
                    fetch(`/cliente/carrito/agregar?t={{ $token }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            modalCustom.classList.remove('visible');
                            refreshCart();
                            abrirCarritoSidebar();
                        } else {
                            errorEl.textContent = data.error || 'Ocurrió un error al agregar el producto.';
                            errorEl.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        console.error("Error adding to cart:", err);
                        errorEl.textContent = 'Ocurrió un error al enviar el producto.';
                        errorEl.style.display = 'block';
                    });
                });
            }
        });
    </script>
</body>
</html>
