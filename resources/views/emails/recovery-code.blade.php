<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Outfit', sans-serif; color: #1a1612; }
        .container { padding: 20px; border: 1px solid #e8e4e0; border-radius: 10px; max-width: 600px; }
        .code { font-size: 24px; font-weight: bold; color: #c97b22; letter-spacing: 5px; margin: 20px 0; }
        .footer { font-size: 12px; color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hola,</h2>
        <p>Has solicitado recuperar tu contraseña en <strong>SGPD</strong>.</p>
        <p>Tu código de verificación es:</p>
        <div class="code">{{ $code }}</div>
        <p>Este código expirará en 10 minutos. Si no solicitaste este cambio, puedes ignorar este correo.</p>
        <div class="footer">
            SGPD &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
