<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/menu.css', 'resources/js/menu.js'])
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

    {{-- ══════════ HEADER ══════════ --}}
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

    {{-- ══════════ NAV CATEGORÍAS ══════════ --}}
    <div class="cat-nav-wrapper">
        <div class="cat-nav">
            @foreach($categorias as $index => $cat)
            <button class="cat-btn" id="btn-cat-{{ $cat->id }}"
                data-cat="{{ $cat->id }}"
                onclick="filtrarCategoria({{ $cat->id }}, this)">
                {{ $cat->nombre }}
            </button>
            @endforeach
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
            <span class="carrito-badge {{ count($carrito) > 0 ? 'visible' : '' }}" id="badge">
                {{ count($carrito) }}
            </span>
        </button>
    </div>

    {{-- ══════════ ALERTAS ══════════ --}}
    @if(session('exito'))
    <div class="alerta alerta-exito">{{ session('exito') }}</div>
    @endif
    @if(session('error'))
    <div class="alerta alerta-error">{{ session('error') }}</div>
    @endif

    {{-- ══════════ MENÚ POR CATEGORÍAS ══════════ --}}
    @foreach($categorias as $categoria)
    <div class="seccion" id="cat-{{ $categoria->id }}">
        <div class="seccion-titulo">{{ $categoria->nombre }}</div>
        <div class="productos-grid">
            @foreach($categoria->productos as $producto)
            <div class="producto-card">
                <div class="producto-imagen" aria-hidden="true">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                    @else
                        <div class="sin-imagen">🍽</div>
                    @endif
                </div>
                <div class="producto-info">
                    <div class="producto-nombre">{{ $producto->nombre }}</div>
                    <div class="producto-precio">${{ number_format($producto->precio, 2) }}</div>
                    @if($producto->descripcion)
                    <div class="producto-desc">{{ Str::limit($producto->descripcion, 60) }}</div>
                    @endif

                    <form method="POST" action="{{ route('cliente.carrito.agregar') }}">
                        @csrf
                        <input type="hidden" name="_t" value="{{ $token }}">
                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">
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
            @endforeach
        </div>
    </div>
    @endforeach


    {{-- ══════════ OVERLAY ══════════ --}}
    <div class="sidebar-overlay" id="overlay" onclick="cerrarCarrito()"></div>

    {{-- ══════════ PANEL LATERAL — CARRITO ══════════ --}}
    <div class="carrito-sidebar" id="carritoSidebar" role="dialog" aria-label="Tu carrito">

        <div class="sidebar-header">
            <div>
                <span class="sidebar-titulo">Tu carrito</span>
                <span class="sidebar-count">{{ count($carrito) }} {{ count($carrito) === 1 ? 'producto' : 'productos' }}</span>
            </div>
            <button class="btn-cerrar-sidebar" onclick="cerrarCarrito()" aria-label="Cerrar carrito">✕</button>
        </div>

        <div class="sidebar-items">
            @if(empty($carrito))
            <div class="sidebar-vacio">
                <div class="sidebar-vacio-icon" aria-hidden="true">—</div>
                <span>Tu carrito está vacío.<br>Agrega algo del menú para empezar.</span>
            </div>
            @else
            @foreach($carrito as $productoId => $item)
            <div class="carrito-item" id="item-{{ $productoId }}">
                <div class="item-imagen" aria-hidden="true">
                    @if($item['imagen'])
                        <img src="{{ asset('storage/' . $item['imagen']) }}" alt="">
                    @else
                        <div class="sin-imagen-min">🍽</div>
                    @endif
                </div>
                <div class="item-info">
                    <div class="item-nombre">{{ $item['nombre'] }}</div>
                    <div class="item-precio" id="sub-{{ $productoId }}">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</div>
                </div>
                <div class="qty-sidebar">
                    <button type="button" onclick="actualizarCantidad({{ $productoId }}, -1)" aria-label="Reducir">−</button>
                    <span id="qty-{{ $productoId }}">{{ $item['cantidad'] }}</span>
                    <button type="button" onclick="actualizarCantidad({{ $productoId }}, 1)" aria-label="Aumentar">+</button>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <div class="sidebar-footer">
            <div class="sidebar-total">
                <span class="total-label">Total del pedido</span>
                <span class="total-valor">${{ number_format($totalCarrito, 2) }}</span>
            </div>
            <form method="POST" action="{{ route('cliente.pedido.confirmar') }}">
                @csrf
                <input type="hidden" name="_t" value="{{ $token }}">
                <button type="submit" class="btn-confirmar" {{ empty($carrito) ? 'disabled' : '' }}>
                    Confirmar pedido
                </button>
            </form>
        </div>
    </div>

    {{-- ══════════ MODAL — CERRAR SESIÓN ══════════ --}}
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

    {{-- ══════════ MODAL — INACTIVIDAD ══════════ --}}
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

    {{-- Form oculto para logout manual --}}
    <form id="formLogout" method="POST" action="{{ route('cliente.logout') }}" style="display:none">
        @csrf
        <input type="hidden" name="_t" value="{{ $token }}">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.initMenu) {
                window.initMenu({
                    csrf: '{{ csrf_token() }}',
                    token: '{{ $token }}',
                    routes: {
                        logoutInactividad: "{{ route('cliente.logout.inactividad') }}",
                        sesionCerrada: "{{ route('cliente.sin-sesion') }}"
                    }
                });
            }
        });
    </script>


</body>

</html>