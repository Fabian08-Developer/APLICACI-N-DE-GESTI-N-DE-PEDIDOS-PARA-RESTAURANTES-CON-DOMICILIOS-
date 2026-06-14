<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGPD — Sistema de Gestión de Pedidos y Domicilios</title>
    <meta name="description" content="Sistema integral de gestión de pedidos para restaurantes. Multi-sucursal, QR para mesas, panel de cocina, control de meseros y más.">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/home.css'])
</head>

<body>

    {{-- ══════════════════════════════
     HERO
     ══════════════════════════════ --}}
    <section class="hero">
        <nav class="nav-top">
            <a href="/" class="nav-brand">
                <span class="nav-brand-icon">🍴</span>
                <span class="nav-brand-text">SGPD</span>
            </a>
            <div class="nav-links">
                <a href="{{ route('registro') }}" class="nav-link nav-link--ghost">
                    Registrar Negocio
                </a>
                <a href="{{ route('login') }}" class="nav-link nav-link--primary">
                    Iniciar Sesión
                </a>
            </div>
        </nav>

        <p class="hero-eyebrow">Sistema de Gestión Multi-Sucursal</p>
        <h1 class="hero-title">
            Gestiona tu<br><em>restaurante</em><br>desde cualquier lugar
        </h1>
        <p class="hero-desc">
            Plataforma integral para la administración de pedidos, mesas,
            cocina, meseros y domicilios. Todo conectado en tiempo real
            con soporte para múltiples sucursales.
        </p>
        <div class="hero-actions">
            <a href="{{ route('login') }}" class="btn btn--primary">
                Iniciar Sesión
            </a>
            <a href="{{ route('registro') }}" class="btn btn--outline">
                Registrar Negocio
            </a>
        </div>

        <div class="scroll-indicator">
            <span>Más información</span>
            <div class="scroll-line"></div>
        </div>
    </section>

    {{-- ══════════════════════════════
     CARACTERÍSTICAS DEL SISTEMA
     ══════════════════════════════ --}}
    <section class="info-section" id="info">
        <div class="info-header">
            <p class="info-eyebrow">Características del sistema</p>
            <h2 class="info-title">Todo lo que necesitas para operar</h2>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3 class="info-card-title">QR para Mesas</h3>
                <p class="info-card-desc">Cada mesa tiene su código QR único. El cliente escanea, ve el menú y hace su pedido sin esperar.</p>
            </div>
            <div class="info-card">
                <h3 class="info-card-title">Panel de Cocina</h3>
                <p class="info-card-desc">La cocina recibe los pedidos en tiempo real. Cambian estados de preparación con un clic.</p>
            </div>
            <div class="info-card">
                <h3 class="info-card-title">Reportes de Ventas</h3>
                <p class="info-card-desc">Dashboard con estadísticas de ventas diarias, semanales y mensuales. Exportación a PDF y Excel.</p>
            </div>
            <div class="info-card">
                <h3 class="info-card-title">Multi-Sucursal</h3>
                <p class="info-card-desc">Administra múltiples sucursales desde un solo panel. Cada una con sus propios productos, mesas y personal.</p>
            </div>
            <div class="info-card">
                <h3 class="info-card-title">Domicilios</h3>
                <p class="info-card-desc">Gestión de domiciliarios, zonas de cobertura y asignación automática de entregas por barrios.</p>
            </div>
            <div class="info-card">
                <h3 class="info-card-title">Pagos Integrados</h3>
                <p class="info-card-desc">Integración con pasarela de pagos Wompi para cobros en línea seguros y confirmados por webhook.</p>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════
     ROLES DEL SISTEMA
     ══════════════════════════════ --}}
    <section class="roles-section" id="roles">
        <div class="roles-header">
            <p class="info-eyebrow">¿Cómo accedo?</p>
            <h2 class="info-title">Roles y puntos de acceso</h2>
        </div>

        <div class="roles-grid">
            {{-- GERENTE --}}
            <div class="role-card role-card--gerente">
                <h3 class="role-name">Gerente</h3>
                <p class="role-desc">
                    Dueño o administrador general del negocio. Gestiona las sucursales
                    y tiene visión global de todas las operaciones.
                </p>
                <ul class="role-features">
                    <li>Crear y administrar sucursales</li>
                    <li>Acceso independiente del staff</li>
                    <li>Guard de autenticación propio</li>
                </ul>
                <a href="{{ route('login') }}" class="role-btn role-btn--gerente">
                    Ingresar →
                </a>
            </div>

            {{-- ADMINISTRADOR --}}
            <div class="role-card role-card--admin">
                <h3 class="role-name">Administrador</h3>
                <p class="role-desc">
                    Administra el contenido operativo de la sucursal: productos,
                    categorías, mesas, usuarios, reportes e inventarios.
                </p>
                <ul class="role-features">
                    <li>CRUD de productos, mesas y categorías</li>
                    <li>Gestión de usuarios y roles</li>
                    <li>Dashboard de reportes de ventas</li>
                    <li>Generación de códigos QR</li>
                </ul>
                <a href="{{ route('login') }}" class="role-btn role-btn--admin">
                    Ingresar →
                </a>
            </div>

            {{-- MESERO --}}
            <div class="role-card role-card--mesero">
                <h3 class="role-name">Mesero</h3>
                <p class="role-desc">
                    Recibe pedidos asignados automáticamente por un sistema
                    de balanceo de carga. Puede entregar y gestionar mesas.
                </p>
                <ul class="role-features">
                    <li>Ver pedidos asignados en tiempo real</li>
                    <li>Marcar pedidos como entregados</li>
                    <li>Liberar mesas y cerrar sesiones</li>
                </ul>
                <a href="{{ route('login') }}" class="role-btn role-btn--mesero">
                    Ingresar →
                </a>
            </div>

            {{-- COCINA --}}
            <div class="role-card role-card--cocina">
                <h3 class="role-name">Cocina</h3>
                <p class="role-desc">
                    Recibe los pedidos entrantes y actualiza el estado de
                    preparación para que el mesero sepa cuándo servir.
                </p>
                <ul class="role-features">
                    <li>Cola de pedidos en tiempo real</li>
                    <li>Cambiar estado: preparando → listo</li>
                    <li>Notificaciones de nuevos pedidos</li>
                </ul>
                <a href="{{ route('login') }}" class="role-btn role-btn--cocina">
                    Ingresar →
                </a>
            </div>
        </div>
    </section>

    <footer class="footer">
        SGPD · Sistema de Gestión · {{ date('Y') }}
    </footer>

</body>

</html>