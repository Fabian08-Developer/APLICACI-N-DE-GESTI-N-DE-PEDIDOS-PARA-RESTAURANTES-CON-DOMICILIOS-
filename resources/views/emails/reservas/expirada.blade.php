<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 30px; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        h2 { font-family: 'DM Serif Display', serif; color: #E07A5F; margin-top: 0; }
        .details { margin: 20px 0; padding: 15px; background: #fafafa; border-radius: 8px; border: 1px solid #f0f0f0; }
        .details p { margin: 8px 0; font-size: 14px; }
        .footer { font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; }
        .badge { display: inline-block; padding: 4px 8px; background: #fee2e2; color: #991b1b; border-radius: 6px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reserva Expirada (No-Show)</h2>
        <p>Hola {{ $reserva->nombre_cliente ?? 'Cliente' }},</p>
        <p>Te escribimos para informarte que tu reserva en <strong>SGPD</strong> ha sido cancelada debido a que ha pasado el tiempo de tolerancia estipulado desde la hora de tu reserva y no registramos tu llegada al restaurante.</p>
        
        <div class="details">
            <p><strong>Código de Reserva:</strong> {{ $reserva->codigo_reserva }}</p>
            <p><strong>Fecha que estaba agendada:</strong> {{ $reserva->fecha_reserva->format('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ \Carbon\Carbon::parse($reserva->hora_inicio)->format('g:i A') }}</p>
            <p><strong>Estado Actual:</strong> <span class="badge">Cancelada (No-Show)</span></p>
        </div>

        <p>Esperamos que puedas visitarnos en una próxima ocasión. Si crees que esto es un error, por favor contáctanos lo más pronto posible.</p>

        <div class="footer">
            SGPD &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
