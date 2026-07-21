<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Meta tags PWA y contexto de autenticación --}}
    <meta name="theme-color" content="#E07A5F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SGPD">
    <link rel="manifest" href="/manifest.json">
    @auth
    <meta name="auth-user-id"      content="{{ auth()->id() }}">
    <meta name="auth-sucursal-id" content="{{ auth()->user()->sucursal_id }}">
    @endauth

    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        body { color: var(--text-main); background-color: var(--bg-main); overflow-x: hidden; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #FFFFFF; }
        ::-webkit-scrollbar-thumb { background: #E2DDD5; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #D97706; }
        .sidebar-transition { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .livewire-progress-bar { background-color: #E07A5F !important; }
    </style>
</head>

<body class="antialiased font-sans" x-data="{ sidebarOpen: false }">
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

    @php
    $userObj = auth()->user();
    $isSuperAdmin = $userObj && $userObj->hasRole('super-admin');
    $isGerente = $userObj && $userObj->hasRole('gerente');
    $userName = $userObj ? $userObj->nombre : 'Usuario';

    $userRole = 'Usuario';
    if ($isSuperAdmin) $userRole = 'Super Admin';
    elseif ($isGerente) $userRole = 'Gerente';
    elseif ($userObj && $userObj->rol) $userRole = $userObj->rol->nombre;

    $platformName = $isSuperAdmin ? 'BUILDER PLATFORM' : 'Mi Restaurante';
    $platformSub = $isSuperAdmin ? 'Centro de Control Global' : 'Panel Administrador';
    @endphp
    {{-- TOPBAR --}}
    <header class="h-20 bg-[#FFFFFF]/98 backdrop-blur-xl border-b border-[#2C241B]/[0.05] sticky top-0 z-30 px-4 sm:px-6 lg:pl-[18rem] lg:pr-8 flex items-center justify-between shadow-[0_4px_25px_rgba(44,36,27,0.02)] transition-all duration-300">
        <div class="flex items-center gap-3 lg:hidden">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2.5 rounded-xl text-[#5C5246] hover:text-[#2C241B] hover:bg-[#E2DDD5]/60 transition-all flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-[#E07A5F]/50" title="Abrir menú">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex flex-col">
                <h1 class="text-[14px] font-black text-[#2C241B] uppercase tracking-widest leading-tight">{{ $platformName }}</h1>
                <span class="text-[10px] font-bold text-[#E07A5F] uppercase tracking-wider">{{ $platformSub }}</span>
            </div>
        </div>

        <div class="hidden lg:flex items-center text-[11px] font-extrabold text-[#8B8175] tracking-wider uppercase">
        </div>

        <div class="flex items-center gap-4 ml-auto">
            <div class="hidden md:flex flex-col items-end">
                <strong class="text-[12px] text-[#2C241B] uppercase tracking-tighter">{{ $userName }}</strong>
                <span class="text-[10px] text-[#5C5246] uppercase tracking-wider">{{ $userRole }}</span>
            </div>

            {{-- Campanilla de notificaciones (solo usuarios con sucursal asignada) --}}
            @auth
            @if(auth()->user()->sucursal_id)
            <livewire:shared.campanilla-notificaciones />
            @endif
            @endauth

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center justify-center w-10 h-10 rounded-xl border border-[#2C241B]/10 text-[#E07A5F] hover:bg-[#E07A5F]/10 transition-all" title="Cerrar sesión">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    {{-- OVERLAY --}}
    <div x-show="sidebarOpen"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 bg-[#2C241B]/40 backdrop-blur-sm z-40 lg:hidden"
        @click="sidebarOpen = false"
        x-cloak></div>

    {{-- DRAWER SIDEBAR --}}
    <aside class="fixed inset-y-0 left-0 bg-[#FFFFFF]/98 backdrop-blur-3xl text-[#2C241B] w-64 z-50 border-r border-[#2C241B]/[0.05] shadow-[12px_0_40px_rgba(44,36,27,0.035)] flex flex-col transform transition-transform duration-300 ease-out lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        {{-- Sidebar Header --}}
        <div class="h-20 flex items-center justify-between px-6 border-b border-[#2C241B]/[0.05] bg-[#FFFFFF] shrink-0">
            <div class="flex flex-col">
                <span class="text-[14px] font-black text-[#2C241B] uppercase tracking-widest leading-tight">{{ $platformName }}</span>
                <span class="text-[10px] font-bold text-[#E07A5F] uppercase tracking-wider">{{ $platformSub }}</span>
            </div>
            <button @click="sidebarOpen = false" class="p-2 rounded-lg text-[#5C5246] hover:text-[#2C241B] hover:bg-[#E2DDD5]/50 transition-colors lg:hidden" title="Cerrar menú">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Nav Links --}}
        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            <div class="px-3 mb-3 text-[10px] font-black uppercase tracking-widest text-[#8B8175]">
                {{ $isSuperAdmin ? 'Administración SaaS' : 'Menú Principal' }}
            </div>
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('dashboard') || request()->routeIs('super-admin.dashboard') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg></span>
                Dashboard
            </a>

            @if($isSuperAdmin)
                {{-- Opciones de Super Admin --}}
                <a href="{{ route('super-admin.requests') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('super-admin.requests') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                    <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg></span>
                    Solicitudes
                </a>
                <a href="{{ route('super-admin.tenants') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('super-admin.tenants') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                    <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg></span>
                    Gestionar Tenants (Empresas)
                </a>
                <a href="{{ route('super-admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('super-admin.users') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                    <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg></span>
                    Usuarios Globales
                </a>
                <a href="{{ route('super-admin.trash') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('super-admin.trash') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                    <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg></span>
                    Papelera
                </a>
            @else
                {{-- Opciones de Gerente / Admin --}}
                <a href="{{ route('sucursales') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('sucursales') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                    <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z" />
                        </svg></span>
                    Mis Sucursales
                </a>

                @if($isGerente)
                    <a href="{{ route('gerente.reportes-globales') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('gerente.reportes-globales') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                        <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6m2 5h2a2 2 0 002-2v-5a2 2 0 00-2-2h-2a2 2 0 00-2 2v5a2 2 0 002 2z" />
                            </svg></span>
                        Reportes Globales
                    </a>
                    <a href="{{ route('gerente.mapa-sedes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('gerente.mapa-sedes') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                        <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg></span>
                        Mapa de Sedes
                    </a>
                    <a href="{{ route('gerente.mi-pagina') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('gerente.mi-pagina') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                        <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" /><polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg></span>
                        Mi Página
                    </a>
                @endif
            @endif
            
            <a href="{{ route('configuracion') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-[12px] tracking-wide {{ request()->routeIs('configuracion') ? 'bg-[#E07A5F]/10 text-[#E07A5F] border-l-2 border-[#E07A5F]' : 'text-[#5C5246] hover:text-[#2C241B] hover:bg-[#F5F2ED] border-l-2 border-transparent' }}" @click="sidebarOpen = false" wire:navigate>
                <span class="text-current"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg></span>
                Configuración
            </a>
        </nav>

        {{-- Footer Sidebar --}}
        <div class="p-4 border-t border-[#2C241B]/10 bg-[#FFFFFF]">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-[#E07A5F] text-[#FFFFFF] flex items-center justify-center font-black shadow-sm shrink-0">
                    {{ substr($userName, 0, 1) }}
                </div>
                <div class="flex flex-col">
                    <strong class="text-[12px] text-[#2C241B] uppercase tracking-tighter truncate">{{ $userName }}</strong>
                    <span class="text-[10px] text-[#5C5246] uppercase">{{ $userRole }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#E07A5F]/10 text-[#E07A5F] hover:bg-[#E07A5F] hover:text-[#FFFFFF] font-black text-[11px] uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENIDO MAIN --}}
    <main class="w-full min-h-[calc(100vh-80px)]">
        <div class="p-4 sm:p-6 lg:py-8 lg:pr-8 lg:pl-[18rem] w-full transition-all duration-300">
            {{ $slot }}
        </div>
    </main>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('swal', event => {
            const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            Swal.fire({
                title: data.title,
                text: data.text,
                icon: data.icon,
                background: '#FFFFFF',
                color: '#2C241B',
                confirmButtonColor: '#E07A5F',
                customClass: {
                    popup: 'rounded-[16px] border border-[#2C241B]/10 shadow-lg',
                }
            });
        });
    </script>
    
    @auth
        @if(!auth()->user()->hasRole(['super-admin', 'gerente']) && auth()->user()->hasRole(['administrador', 'cocina', 'mesero', 'domiciliario']))
            @include('partials.staff-token')
        @endif
    @endauth

    {{-- Echo → Livewire bridge para app.blade.php (Gerente / Super-admin con sucursal) --}}
    @auth
    @if(auth()->user()->sucursal_id)
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const initEcho = () => {
            if (!window.Echo || !window.__SGPD_ECHO?.ready) {
                setTimeout(initEcho, 300);
                return;
            }
            const { sucursalId } = window.__SGPD_ECHO;
            window.Echo.private(`sucursal.${sucursalId}`)
                .listen('.pedido.creado',         (e) => Livewire.dispatch('nuevaNotificacion', e))
                .listen('.pedido.estado_cambiado',(e) => Livewire.dispatch('nuevaNotificacion', e))
                .listen('.pedido.asignado',       (e) => Livewire.dispatch('nuevaNotificacion', e))
                .listen('.pedido.cancelado',      (e) => Livewire.dispatch('nuevaNotificacion', e));
        };
        initEcho();
    });
    </script>
    @endif
    @endauth
</body>
</html>