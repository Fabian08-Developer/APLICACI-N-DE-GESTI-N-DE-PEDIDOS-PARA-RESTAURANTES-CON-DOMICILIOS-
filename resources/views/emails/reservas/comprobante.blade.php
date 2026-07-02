<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 30px; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        h2 { font-family: 'DM Serif Display', serif; color: #C48B57; margin-top: 0; }
        .details { margin: 20px 0; padding: 15px; background: #fafafa; border-radius: 8px; border: 1px solid #f0f0f0; }
        .details p { margin: 8px 0; font-size: 14px; }
        .footer { font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; }
        .badge { display: inline-block; padding: 4px 8px; background: #DEF7EC; color: #03543F; border-radius: 6px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Comprobante de Pago de Reserva</h2>
        <p>Hola {{ $pago->reserva->nombre_cliente ?? 'Cliente' }},</p>
        <p>Hemos procesado exitosamente el pago del depósito de tu reserva en <strong>SGPD</strong>.</p>
        
        <div class="details">
            <p><strong>Código de Reserva:</strong> {{ $pago->reserva->codigo_reserva }}</p>
            <p><strong>Referencia de Pago:</strong> {{ $pago->referencia }}</p>
            <p><strong>Método de Pago:</strong> {{ $pago->metodo }}</p>
            <p><strong>Monto Pagado:</strong> ${{ number_format($pago->monto, 0, ',', '.') }}</p>
            <p><strong>Estado:</strong> <span class="badge">Aprobado</span></p>
            <p><strong>Fecha/Hora del Pago:</strong> {{ $pago->actualizado_en ? $pago->actualizado_en->format('d/m/Y g:i A') : now()->format('d/m/Y g:i A') }}</p>
        </div>

        <p>Este depósito garantiza tu reserva. Te esperamos el día <strong>{{ $pago->reserva->fecha_reserva->format('d/m/Y') }}</strong> a las <strong>{{ \Carbon\Carbon::parse($pago->reserva->hora_inicio)->format('g:i A') }}</strong>.</p>
        <p>¡Gracias por elegirnos!</p>

        <div class="footer">
            SGPD &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
