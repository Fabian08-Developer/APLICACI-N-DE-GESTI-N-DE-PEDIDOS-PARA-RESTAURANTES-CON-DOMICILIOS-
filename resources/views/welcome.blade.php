<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGPD — Sistema de Gestión de Pedidos y Domicilios</title>
    <meta name="description" content="Sistema integral de gestión de pedidos para restaurantes. Multi-sucursal, QR para mesas, panel de cocina, control de meseros y más.">
    
    {{-- Google Fonts: Playfair Display & Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/home.css'])
</head>

<body>

    {{-- ══════════════════════════════
     NAVEGACIÓN SUPERIOR
     ══════════════════════════════ --}}
    <nav class="nav-top">
        <a href="/" class="nav-brand">
            <div class="nav-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
            </div>
            <span class="nav-brand-text">SGPD<span>.</span></span>
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

    {{-- ══════════════════════════════
     HERO
     ══════════════════════════════ --}}
    <section class="hero">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Plataforma Multi-Sucursal en Tiempo Real</span>
            </div>
            <h1 class="hero-title">
                Gestiona y eleva tu<br><em>restaurante</em> al siguiente nivel
            </h1>
            <p class="hero-desc">
                Plataforma integral para la administración inteligente de pedidos, mesas,
                cocina, meseros y domicilios. Todo sincronizado al segundo con soporte para múltiples sucursales.
            </p>
            <div class="hero-actions">
                <a href="{{ route('login') }}" class="btn btn--primary">
                    <span>Iniciar Sesión</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="{{ route('registro') }}" class="btn btn--outline">
                    <span>Registrar Negocio</span>
                </a>
            </div>
        </div>

        {{-- Visual Preview Container --}}
        <div class="hero-preview-wrapper">
            <div class="hero-preview-img-container">
                <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1400&q=80" alt="Ambiente de restaurante con tecnología de gestión" class="hero-preview-img" loading="lazy">
                
                {{-- Floating Status Pills --}}
                <div class="hero-floating-left">
                    <span class="dot-pulse"></span>
                    <span>Mesa 4: En Cocina (3 min)</span>
                </div>

                <div class="hero-floating-badge">
                    <div class="hero-floating-badge-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="hero-floating-badge-text">
                        <h4>Pedido #A84 Listo</h4>
                        <p>Notificación enviada al mesero</p>
                    </div>
                </div>
            </div>
        </div>

        <a href="#info" class="scroll-indicator">
            <span>Conoce el sistema</span>
            <div class="scroll-line"></div>
        </a>
    </section>

    {{-- ══════════════════════════════
     CARACTERÍSTICAS DEL SISTEMA
     ══════════════════════════════ --}}
    <section class="info-section" id="info">
        <div class="info-header">
            <p class="section-eyebrow">Potencia Gastronómica</p>
            <h2 class="section-title">Todo lo que necesitas para operar con excelencia</h2>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                </div>
                <h3 class="info-card-title">Menú QR para Mesas</h3>
                <p class="info-card-desc">Cada mesa cuenta con un código QR único. El cliente escanea, explora el menú digital interactivo y realiza su pedido directamente sin demoras.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" y1="17" x2="18" y2="17"/></svg>
                </div>
                <h3 class="info-card-title">Panel de Cocina en Vivo</h3>
                <p class="info-card-desc">La cocina recibe las comandas al instante. Los cocineros visualizan variantes y temporizadores de prioridad, cambiando estados con un solo toque.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <h3 class="info-card-title">Reportes y Métricas</h3>
                <p class="info-card-desc">Dashboard analítico con estadísticas completas de ventas diarias, semanales y mensuales. Exportación instantánea de reportes a formato PDF y Excel.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                </div>
                <h3 class="info-card-title">Control Multi-Sucursal</h3>
                <p class="info-card-desc">Administra múltiples sucursales desde un solo panel maestro. Cada sede opera con su propio catálogo de productos, inventario, mesas y personal.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 17a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm13 0a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path d="M15 6h5a1 1 0 0 1 1 1v7.5a2.5 2.5 0 0 1-2.5 2.5H18"/><path d="M2 12h3"/><path d="M2 8h4"/><path d="M8 14.5V6a1 1 0 0 1 1-1h6v9.5H8Z"/></svg>
                </div>
                <h3 class="info-card-title">Logística de Domicilios</h3>
                <p class="info-card-desc">Gestión integral de domiciliarios, tarifas de envío por zonas y seguimiento de entregas en tiempo real para optimizar los tiempos de despacho.</p>
            </div>
            <div class="info-card">
                <div class="info-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M12 15h6"/></svg>
                </div>
                <h3 class="info-card-title">Pagos en Línea Seguros</h3>
                <p class="info-card-desc">Integración nativa con pasarela de pagos Wompi para cobros digitales instantáneos, verificados y confirmados automáticamente mediante webhooks.</p>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════
     ROLES DEL SISTEMA
     ══════════════════════════════ --}}
    <section class="roles-section" id="roles">
        <div class="roles-header">
            <p class="section-eyebrow">Puntos de Acceso</p>
            <h2 class="section-title">Espacios de trabajo especializados</h2>
        </div>

        <div class="roles-grid">
            {{-- GERENTE --}}
            <div class="role-card role-card--gerente">
                <div class="role-card-banner">
                    <img src="https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=600&q=80" alt="Gestión de Gerencia" loading="lazy">
                </div>
                <div class="role-card-body">
                    <div>
                        <span class="role-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Acceso Gerencial
                        </span>
                        <h3 class="role-name">Gerente</h3>
                        <p class="role-desc">
                            Supervisa el rendimiento global del negocio, gestiona sucursales y toma decisiones estratégicas con datos consolidados.
                        </p>
                        <ul class="role-features">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Creación y control de sucursales
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Monitoreo de ventas en tiempo real
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Auditoría y reportes consolidados
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="role-btn">
                        <span>Ingresar como Gerente</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>

            {{-- ADMINISTRADOR --}}
            <div class="role-card role-card--admin">
                <div class="role-card-banner">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=600&q=80" alt="Gestión de Administración" loading="lazy">
                </div>
                <div class="role-card-body">
                    <div>
                        <span class="role-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                            Acceso Operativo
                        </span>
                        <h3 class="role-name">Administrador</h3>
                        <p class="role-desc">
                            Controla la operación diaria de la sucursal: menú, recetas, mesas, personal e historial detallado de caja.
                        </p>
                        <ul class="role-features">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Gestión completa de menú y mesas
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Control de usuarios y roles del staff
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Generación de códigos QR para mesas
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="role-btn">
                        <span>Ingresar como Admin</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>

            {{-- MESERO --}}
            <div class="role-card role-card--mesero">
                <div class="role-card-banner">
                    <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=600&q=80" alt="Servicio de Mesas y Meseros" loading="lazy">
                </div>
                <div class="role-card-body">
                    <div>
                        <span class="role-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                            Staff de Servicio
                        </span>
                        <h3 class="role-name">Mesero</h3>
                        <p class="role-desc">
                            Recibe pedidos asignados automáticamente, gestiona el estado de mesas y coordinada fluidamente con el área de cocina.
                        </p>
                        <ul class="role-features">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Recepción en vivo de pedidos listos
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Confirmación de entregas y adiciones
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Apertura y liberación de mesas
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="role-btn">
                        <span>Ingresar como Mesero</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>

            {{-- COCINA --}}
            <div class="role-card role-card--cocina">
                <div class="role-card-banner">
                    <img src="https://images.unsplash.com/photo-1556910110-a5a63dfd393c?auto=format&fit=crop&w=600&q=80" alt="Preparación en Cocina" loading="lazy">
                </div>
                <div class="role-card-body">
                    <div>
                        <span class="role-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" y1="17" x2="18" y2="17"/></svg>
                            Staff de Preparación
                        </span>
                        <h3 class="role-name">Cocina</h3>
                        <p class="role-desc">
                            Visualiza comandas al instante, temporizadores de prioridad y actualiza estados de preparación para agilizar el despacho.
                        </p>
                        <ul class="role-features">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Pantalla interactiva de pedidos en cola
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Control de disponibilidad e inventario
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Alertas de urgencia por tiempo de espera
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="role-btn">
                        <span>Ingresar a Cocina</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p><strong>SGPD</strong> · Sistema de Gestión de Pedidos para Restaurantes con Domicilios · © {{ date('Y') }}</p>
    </footer>

</body>

</html>