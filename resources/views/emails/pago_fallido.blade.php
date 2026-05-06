<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago no completado</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f3f4f6;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #111827;
            padding: 2rem 1rem;
        }

        .wrapper {
            max-width: 520px;
            margin: 0 auto;
        }

        .header {
            background: #dc2626;
            border-radius: 16px 16px 0 0;
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        .header .icono {
            font-size: 2.5rem;
            margin-bottom: 0.6rem;
        }

        .header h1 {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .header p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin-top: 0.3rem;
        }

        .cuerpo {
            background: #fff;
            padding: 1.8rem 2rem;
        }

        .monto-badge {
            background: #fef2f2;
            color: #dc2626;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        td {
            padding: 0.55rem 0;
            font-size: 0.9rem;
            border-bottom: 1px solid #f3f4f6;
        }

        td:first-child {
            color: #6b7280;
        }

        td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .causas {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 0.9rem 1rem;
            font-size: 0.875rem;
            color: #92400e;
            margin-bottom: 1.2rem;
        }

        .causas ul {
            padding-left: 1.2rem;
            margin-top: 0.4rem;
        }

        .causas li {
            margin-bottom: 0.3rem;
        }

        .aviso {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            border-radius: 8px;
            padding: 0.9rem 1rem;
            font-size: 0.875rem;
            color: #0c4a6e;
        }

        .footer {
            background: #f9fafb;
            border-radius: 0 0 16px 16px;
            padding: 1.2rem 2rem;
            text-align: center;
            font-size: 0.78rem;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="header">
            <div class="icono">❌</div>
            <h1>Pago no completado</h1>
            <p>No pudimos procesar tu pago con Nequi.</p>
        </div>

        <div class="cuerpo">

            <div class="monto-badge">${{ number_format($pago->monto, 2) }} COP</div>

            <table>
                <tr>
                    <td>Pedido</td>
                    <td>#{{ $pago->pedido_id }}</td>
                </tr>
                <tr>
                    <td>Método</td>
                    <td>Nequi</td>
                </tr>
                <tr>
                    <td>Número Nequi</td>
                    <td>{{ $pago->telefono }}</td>
                </tr>
                <tr>
                    <td>Referencia</td>
                    <td style="font-family:monospace;font-size:0.8rem">{{ $pago->referencia_transaccion }}</td>
                </tr>
                <tr>
                    <td>Fecha</td>
                    <td>{{ $pago->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>

            <div class="causas">
                <strong>¿Por qué puede ocurrir esto?</strong>
                <ul>
                    <li>No aceptaste la notificación en la app Nequi a tiempo</li>
                    <li>Saldo insuficiente en tu cuenta Nequi</li>
                    <li>La transacción fue rechazada por Nequi</li>
                </ul>
            </div>

            <div class="aviso">
                💡 Tu pedido sigue guardado. Puedes volver a intentar el pago o elegir otro método.
            </div>

        </div>

        <div class="footer">
            Este es un correo automático, no es necesario responder.
        </div>

    </div>
</body>

</html>