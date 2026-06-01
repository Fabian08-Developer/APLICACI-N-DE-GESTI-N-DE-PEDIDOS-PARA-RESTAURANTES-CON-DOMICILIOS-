<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 30px; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        h2 { font-family: 'DM Serif Display', serif; color: #4B6E4E; margin-top: 0; }
        .details { margin: 20px 0; padding: 15px; background: #fafafa; border-radius: 8px; border: 1px solid #f0f0f0; }
        .details p { margin: 8px 0; font-size: 14px; }
        .footer { font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; }
        .badge { display: inline-block; padding: 4px 8px; background: #E1F5FE; color: #0288D1; border-radius: 6px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reembolso Procesado</h2>
        <p>Hola,</p>
        <p>Tu reembolso correspondiente a la cancelación del pedido #{{ $pedido->id }} en <strong>SGPD</strong> ha sido procesado exitosamente.</p>
        
        <div class="details">
            <p><strong>Referencia del Pedido:</strong> #{{ $pedido->id }}</p>
            <p><strong>Referencia del Pago original:</strong> {{ $pago->referencia }}</p>
            <p><strong>Método de Reembolso:</strong> Nequi ({{ $pago->nequi_telefono }})</p>
            <p><strong>Monto Reembolsado:</strong> ${{ number_format($pago->monto, 0, ',', '.') }}</p>
            <p><strong>Estado:</strong> <span class="badge">Reembolsado</span></p>
            <p><strong>Fecha/Hora:</strong> {{ now()->format('d/m/Y g:i A') }}</p>
        </div>

        <p>El dinero debería verse reflejado en tu cuenta Nequi en los próximos minutos o según los tiempos de procesamiento de la plataforma.</p>

        <div class="footer">
            SGPD &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
