<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #ef4444; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 40px 30px; text-align: center; }
        .content p { font-size: 16px; line-height: 1.6; color: #5C5246; margin-bottom: 20px; }
        .info-box { display: inline-block; background-color: #f1f3f5; border: 1px solid #ddd; padding: 15px 30px; font-size: 18px; font-weight: bold; color: #333; border-radius: 8px; margin: 20px 0; }
        .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sucursal Eliminada</h1>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Te confirmamos que la sucursal ha sido eliminada exitosamente del sistema tras superar la doble verificación de seguridad.</p>
            
            <div class="info-box">
                Sucursal: {{ $nombreSucursal }}
            </div>
            
            <p>El personal asociado a esta sucursal ya no podrá iniciar sesión hasta que sea reasignado a una nueva sede.</p>
            <p>Si esta acción fue un error, contacta inmediatamente al administrador del sistema.</p>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no respondas.</p>
            <p>&copy; {{ date('Y') }} Mi Restaurante. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
