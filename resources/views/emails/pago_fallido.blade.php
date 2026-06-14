<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 30px; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        h2 { font-family: 'DM Serif Display', serif; color: #EF4444; margin-top: 0; }
        .details { margin: 20px 0; padding: 15px; background: #fafafa; border-radius: 8px; border: 1px solid #f0f0f0; }
        .details p { margin: 8px 0; font-size: 14px; }
        .footer { font-size: 12px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px; }
        .badge { display: inline-block; padding: 4px 8px; background: #FDE8E8; color: #9B1C1C; border-radius: 6px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pago Fallido / Rechazado</h2>
        <p>Hola,</p>
        <p>Te informamos que el intento de pago para tu pedido en <strong>SGPD</strong> ha sido rechazado o falló debido a un problema con la transacción (o el tiempo límite de espera expiró).</p>
        
        <div class="details">
            <p><strong>Referencia de Pago:</strong> {{ $pago->referencia }}</p>
            <p><strong>Método de Pago:</strong> {{ $pago->metodo }}</p>
            @if($pago->nequi_telefono)
                <p><strong>Celular Nequi:</strong> {{ $pago->nequi_telefono }}</p>
            @endif
            <p><strong>Monto Intentado:</strong> ${{ number_format($pago->monto, 0, ',', '.') }}</p>
            <p><strong>Estado:</strong> <span class="badge">Fallido</span></p>
            <p><strong>Número de Intentos:</strong> {{ $pago->intentos }}/3</p>
        </div>

        <p>Por favor, regresa a la página de pago del restaurante e intenta de nuevo. Recuerda que después de 3 intentos fallidos la opción de Nequi se bloqueará y deberás usar otro método de pago (como efectivo).</p>

        <div class="footer">
            SGPD &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
