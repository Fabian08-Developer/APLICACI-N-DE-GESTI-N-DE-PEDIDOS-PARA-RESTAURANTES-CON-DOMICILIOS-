<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') — Mesero</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/mesero.css', 'resources/js/mesero.js'])
</head>
<body>
    {{-- OVERLAY CERRAR SIDEBAR --}}
    <div id="mesero-sidebar-overlay" class="mesero-sidebar-overlay"></div>

    {{-- HEADER MÓVIL --}}
    <header class="mesero-mobile-header">
        <div class="header-logo-mobile">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 11l19-9-9 19-2-8-8-2z"/>
            </svg>
            Mi Restaurante
        </div>
        <button id="btn-toggle-sidebar" class="btn-toggle-sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </header>

    <aside id="mesero-sidebar" class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-name">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 11l19-9-9 19-2-8-8-2z"/>
                </svg>
                Mi Restaurante
            </div>
            <div class="sidebar-logo-role">Panel del mesero</div>
        </div>

        <nav class="sidebar-nav">

            <a href="{{ route('mesero.dashboard') }}"
               class="nav-item {{ request()->routeIs('mesero.dashboard') ? 'activo' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Pedidos activos
            </a>

            {{-- ── Mesas asignadas (NUEVO) ── --}}
            <a href="{{ route('mesero.mesas') }}"
               class="nav-item {{ request()->routeIs('mesero.mesas') ? 'activo' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="8" width="18" height="4" rx="1"/>
                    <line x1="7"  y1="12" x2="7"  y2="19"/>
                    <line x1="17" y1="12" x2="17" y2="19"/>
                    <line x1="5"  y1="19" x2="9"  y2="19"/>
                    <line x1="15" y1="19" x2="19" y2="19"/>
                    <line x1="3"  y1="8"  x2="21" y2="8"/>
                    <line x1="5"  y1="5"  x2="19" y2="5"/>
                </svg>
                Mis mesas
            </a>

            {{-- ── Tomar Pedido (NUEVO POS) ── --}}
            <a href="{{ route('mesero.tomar-pedido.mesas') }}"
               class="nav-item {{ request()->routeIs('mesero.tomar-pedido.*') ? 'activo' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 5v14m-7-7h14"/>
                </svg>
                Tomar Pedido
            </a>

            <a href="{{ route('mesero.historial') }}"
               class="nav-item {{ request()->routeIs('mesero.historial') ? 'activo' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Historial
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">
                        {{ auth()->user()?->nombre ?? 'Mesero' }}
                    </div>
                    <div class="sidebar-user-role">Mesero</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
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
        @if(session('exito'))
            <div class="alerta alerta-exito">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('exito') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alerta alerta-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @yield('contenido')
    </main>

    @include('partials.staff-token')
</body>
</html>