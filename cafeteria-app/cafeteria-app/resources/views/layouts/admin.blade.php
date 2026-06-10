<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @livewireStyles
    <style>
        .livewire-progress-bar {
            background-color: var(--primary, #A85507) !important;
        }
    </style>
</head>
<body x-data="{ sidebarOpen: false }">
    @php
        $globalSettings = @json_decode(@file_get_contents(storage_path('app/global_settings.json')), true) ?: [];
        $hasActiveNotice = ($globalSettings['aviso_activo'] ?? false) && ($globalSettings['aviso_mostrar_banner'] ?? false) && !empty($globalSettings['aviso_titulo']);
    @endphp

    @if($hasActiveNotice)
        <div id="system-notice-banner" style="background: linear-gradient(135deg, #7f1d1d, #78350f); border-bottom: 1px solid rgba(244, 63, 94, 0.3); color: #FFFFFF; padding: 12px 24px; font-family: 'DM Sans', sans-serif; font-size: 13px; display: flex; align-items: center; justify-content: space-between; gap: 16px; position: sticky; top: 0; z-index: 9999; box-shadow: 0 4px 20px rgba(0,0,0,0.3); transition: all 0.3s ease;">
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 auto; text-align: center;">
                <span style="display: inline-flex; position: relative; width: 10px; height: 10px; flex-shrink: 0;">
                    <span style="position: absolute; width: 100%; height: 100%; border-radius: 50%; background-color: #fb7185; opacity: 0.75; animation: notice-ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;"></span>
                    <span style="position: relative; width: 10px; height: 10px; border-radius: 50%; background-color: #f43f5e;"></span>
                </span>
                <div style="font-weight: 700; line-height: 1.4;">
                    <span style="text-transform: uppercase; letter-spacing: 0.05em; font-size: 10px; background: rgba(244, 63, 94, 0.2); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(244, 63, 94, 0.3); margin-right: 8px; color: #fecdd3; font-weight: 800;">AVISO DEL SISTEMA</span>
                    <strong style="color: #F8FAFC; text-transform: uppercase; font-weight: 800;">{{ $globalSettings['aviso_titulo'] }}</strong>
                    <span style="color: #E2E8F0; margin-left: 8px; font-weight: 500;">{{ $globalSettings['aviso_mensaje'] }}</span>
                </div>
            </div>
            <button onclick="document.getElementById('system-notice-banner').style.display='none'" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.color='#FFFFFF'; this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.color='#94A3B8'; this.style.background='none'">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <style>
            @keyframes notice-ping {
                75%, 100% { transform: scale(2.5); opacity: 0; }
            }
        </style>
    @endif

    {{-- TOPBAR --}}
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
            <strong>{{ auth()->user()->nombre }}</strong>
            {{ auth()->user()->rol->nombre }} @if(auth()->user()->sucursal_id) (Sucursal: {{ auth()->user()->sucursal->nombre }}) @endif
            <br>
            @if(auth()->user()->hasRole('gerente'))
            <a href="{{ route('sucursales') }}" style="color:var(--primary); font-size:0.7rem; text-decoration:underline;" wire:navigate>Cambiar sucursal</a>
            @endif
        </div>
        <form method="POST" action="{{ route('logout') }}" class="topbar-logout-form" style="margin-left: 0.5rem; display: flex; align-items: center;">
            @csrf
            <button type="submit" class="btn-topbar-logout" title="Cerrar sesión">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </header>

    {{-- OVERLAY --}}
    <div class="overlay" onclick="toggleSidebar()"></div>

    {{-- DRAWER SIDEBAR --}}
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
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></span> Dashboard
            </a>

            <div class="nav-label">Gestión</div>
            <a href="{{ route('admin.categorias') }}" class="nav-item {{ request()->routeIs('admin.categorias') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg></span> Categorías
            </a>
            <a href="{{ route('admin.productos') }}" class="nav-item {{ request()->routeIs('admin.productos') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg></span> Productos
            </a>
            <a href="{{ route('admin.mesas') }}" class="nav-item {{ request()->routeIs('admin.mesas') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/></svg></span> Mesas
            </a>
            <a href="{{ route('admin.pedidos') }}" class="nav-item {{ request()->routeIs('admin.pedidos') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span> Pedidos
            </a>
            <a href="{{ route('admin.domiciliarios.index') }}" class="nav-item {{ request()->routeIs('admin.domiciliarios.*') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M13 16h3.25L19 13.5V16h1a1 1 0 001-1v-4.5l-2.5-3H13"/></svg></span> Domiciliarios
            </a>
            <a href="{{ route('admin.zonas.index') }}" class="nav-item {{ request()->routeIs('admin.zonas.*') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></span> Zonas
            </a>
            <a href="{{ route('admin.reportes') }}" class="nav-item {{ request()->routeIs('admin.reportes') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></span> Reportes
            </a>
            <a href="{{ route('admin.usuarios.index') }}" class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'activo' : '' }}" onclick="toggleSidebar()" wire:navigate>
                <span class="icono"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span> Usuarios
            </a>
        </nav>

        <div class="sidebar-footer">
            <strong>{{ auth()->user()->nombre }}</strong>
            {{ auth()->user()->rol->nombre }}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENIDO --}}
    <main class="contenido">
        @if(session('exito'))
            <div class="alerta alerta-exito">{{ session('exito') }}</div>
        @endif
        @if(session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>

    @livewireScripts
    
    @auth
        @if(auth()->user()->hasRole('administrador') || auth()->user()->hasRole('cocina') || auth()->user()->hasRole('mesero') || auth()->user()->hasRole('domiciliario'))
            @include('partials.staff-token')
        @endif
    @endauth
</body>
</html>
