<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Cancelado — SGPD</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/canceelacion_exitosa.css'])
    <style>
        body {
            background: #F9F7F3;
            color: #2C2621;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }
        .contenedor {
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <div class="icono-cancelado">✕</div>
        
        <h1 style="font-family: 'DM Serif Display', serif; font-size: 1.6rem; margin-bottom: 0.5rem;">Pedido Cancelado</h1>
        <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.5rem; line-height: 1.5;">Tu pedido ha sido cancelado con éxito.</p>

        <div class="cancelacion-card">
            <div class="cancelacion-header">Información de la Cancelación</div>
            
            <div class="aviso-reembolso">
                <strong>Reembolso en proceso:</strong> Si ya habías pagado tu orden a través de Nequi, el dinero será reembolsado a tu cuenta. Recibirás un correo electrónico de confirmación con los detalles del reembolso.
            </div>

            <div class="aviso-ok">
                Hemos enviado un correo electrónico notificando la cancelación de tu pedido a la dirección registrada.
            </div>
        </div>

        <a href="{{ route('cliente.menu', ['t' => $sesion->token]) }}" class="btn-nuevo-pedido">Crear Nuevo Pedido</a>
    </div>
</body>
</html>
