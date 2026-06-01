<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso No Autorizado — SGPD</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/sesion_cerrada.css'])
</head>
<body>

    <div class="contenido">
        @if (session('success'))
            <span class="num">✓</span>
            <span class="icono">👋</span>
            <h1 class="titulo">¡Hasta Pronto!</h1>
            <p class="subtitulo">{{ session('success') }}</p>
        @else
            <span class="num">401</span>
            <span class="icono">🔒</span>
            <h1 class="titulo">Acceso Cerrado</h1>
            <p class="subtitulo">
                {{ session('error') ?? 'Tu sesión no es válida, ha expirado por inactividad o estás fuera del horario de atención.' }}
            </p>
        @endif

        <div class="linea"></div>
        <p class="marca">SGPD</p>
    </div>

</body>
</html>
