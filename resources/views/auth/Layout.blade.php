<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') — Mi Restaurante</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --negro:    #0f0f0f;
            --blanco:   #f7f3ee;
            --dorado:   #c9a84c;
            --gris:     #3a3a3a;
            --error:    #e05c5c;
            --exito:    #4caf7d;
        }

        body {
            background-color: var(--negro);
            color: var(--blanco);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Fondo con textura sutil */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(201,168,76,0.07) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,168,76,0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .tarjeta {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 440px;
            backdrop-filter: blur(10px);
            animation: aparecer 0.5s ease forwards;
        }

        @keyframes aparecer {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-family: 'DM Serif Display', serif;
            font-size: 1.6rem;
            color: var(--dorado);
            text-align: center;
            margin-bottom: 0.3rem;
            letter-spacing: 0.03em;
        }

        .subtitulo {
            text-align: center;
            color: rgba(247,243,238,0.45);
            font-size: 0.85rem;
            margin-bottom: 2.5rem;
            font-weight: 300;
        }

        h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 1.8rem;
            color: var(--blanco);
        }

        /* ---- Formulario ---- */
        .grupo {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(247,243,238,0.55);
            margin-bottom: 0.45rem;
        }

        input, select {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.7rem;
            padding: 0.8rem 1rem;
            color: var(--blanco);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s, background 0.2s;
            outline: none;
        }

        input:focus, select:focus {
            border-color: var(--dorado);
            background: rgba(201,168,76,0.07);
        }

        select option { background: #1a1a1a; }

        /* ---- Botón principal ---- */
        .btn-principal {
            width: 100%;
            padding: 0.9rem;
            background: var(--dorado);
            color: var(--negro);
            border: none;
            border-radius: 0.7rem;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.8rem;
            letter-spacing: 0.03em;
            transition: opacity 0.2s, transform 0.15s;
        }

        .btn-principal:hover  { opacity: 0.88; transform: translateY(-1px); }
        .btn-principal:active { transform: translateY(0); }

        /* ---- Mensajes ---- */
        .alerta {
            padding: 0.8rem 1rem;
            border-radius: 0.6rem;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }

        .alerta-error  { background: rgba(224,92,92,0.12); border: 1px solid rgba(224,92,92,0.3); color: #f0a0a0; }
        .alerta-exito  { background: rgba(76,175,125,0.12); border: 1px solid rgba(76,175,125,0.3); color: #90d4b0; }

        /* ---- Enlace inferior ---- */
        .enlace-inferior {
            text-align: center;
            margin-top: 1.8rem;
            font-size: 0.85rem;
            color: rgba(247,243,238,0.4);
        }

        .enlace-inferior a {
            color: var(--dorado);
            text-decoration: none;
            font-weight: 500;
        }

        .enlace-inferior a:hover { text-decoration: underline; }

        /* ---- Error individual de campo ---- */
        .error-campo {
            color: #f0a0a0;
            font-size: 0.78rem;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="tarjeta">
        <div class="logo">🍽 Mi Restaurante</div>
        <div class="subtitulo">Sistema de gestión</div>

        @yield('contenido')
    </div>
</body>
</html>