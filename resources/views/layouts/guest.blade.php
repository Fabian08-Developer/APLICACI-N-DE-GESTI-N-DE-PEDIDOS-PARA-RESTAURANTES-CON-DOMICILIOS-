<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="auth-body antialiased">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
