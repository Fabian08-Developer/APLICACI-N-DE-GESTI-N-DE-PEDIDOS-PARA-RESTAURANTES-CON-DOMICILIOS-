<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #E07A5F; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 40px 30px; text-align: center; }
        .content p { font-size: 16px; line-height: 1.6; color: #5C5246; margin-bottom: 20px; }
        .code-box { display: inline-block; background-color: #f1f3f5; border: 2px dashed #E07A5F; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #E07A5F; border-radius: 8px; margin: 20px 0; }
        .warning { background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; font-size: 14px; margin-top: 30px; border-left: 4px solid #ffeeba; text-align: left; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verificación de Seguridad</h1>
        </div>
        <div class="content">
            <p>Has solicitado eliminar la sucursal <strong>{{ $sucursal->nombre }}</strong>.</p>
            <p>Para confirmar esta acción crítica, por favor ingresa el siguiente código de verificación de 6 dígitos en el sistema:</p>
            
            <div class="code-box">{{ $codigo }}</div>
            
            <div class="warning">
                <strong>Advertencia de Seguridad:</strong> Si tú no solicitaste esta acción, alguien está intentando eliminar una sucursal de tu cuenta. Por favor, cambia tu contraseña inmediatamente. El personal perderá su acceso si procedes con la eliminación.
            </div>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no respondas.</p>
            <p>&copy; {{ date('Y') }} Mi Restaurante. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
