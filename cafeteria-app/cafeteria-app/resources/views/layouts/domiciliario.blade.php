<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Domiciliario - Cafetería Huila</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/domiciliario.css', 'resources/js/app.js'])
    
    <!-- Alpine JS (Removed: Livewire 3 already includes it) -->
    
    @livewireStyles
</head>
<body class="bg-[#0B1015] text-[#F8FAFC] antialiased overflow-x-hidden selection:bg-[#00A8B5]/30">

    <div class="flex flex-col min-h-screen">
        {{-- Contenido principal --}}
        <main class="flex-grow w-full max-w-md mx-auto relative pb-20 no-scrollbar overflow-y-auto">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
        
        {{-- Barra de navegación inferior (si se define) --}}
        @hasSection('bottom-nav')
            @yield('bottom-nav')
        @endif
    </div>

    @livewireScripts
    
    {{-- SweetAlert2 for notifications --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @include('partials.staff-token')
    @stack('scripts')
</body>
</html>
