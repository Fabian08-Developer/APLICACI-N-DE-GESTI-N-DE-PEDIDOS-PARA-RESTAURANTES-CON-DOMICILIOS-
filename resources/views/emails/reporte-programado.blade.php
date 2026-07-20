<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 30px; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 12px; max-width: 620px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .header { border-bottom: 2px solid #f0ece8; padding-bottom: 20px; margin-bottom: 24px; }
        .header-icon { font-size: 36px; margin-bottom: 10px; }
        h2 { font-family: 'DM Serif Display', serif; color: #C48B57; margin: 0 0 4px 0; font-size: 22px; }
        .subtitle { font-size: 14px; color: #888; margin: 0; }
        .info-card { background: #fafaf9; border: 1px solid #ede9e4; border-radius: 10px; padding: 18px; margin: 20px 0; }
        .info-card p { margin: 7px 0; font-size: 14px; color: #4a4540; }
        .info-card p strong { color: #2C2621; min-width: 130px; display: inline-block; }
        .badge { display: inline-block; padding: 3px 10px; background: #EEF9F1; color: #1a7a3a; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .note { background: #FFF8F0; border-left: 3px solid #C48B57; border-radius: 0 8px 8px 0; padding: 12px 16px; margin: 20px 0; font-size: 13px; color: #7a5c3a; }
        .footer { font-size: 12px; color: #aaa; margin-top: 28px; border-top: 1px solid #eee; padding-top: 16px; text-align: center; }
        .divider { height: 1px; background: #f0ece8; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <div class="header-icon">📊</div>
            <h2>Reporte de Ventas Automático</h2>
            <p class="subtitle">{{ $sucursal->nombre }}</p>
        </div>

        <p>Hola,</p>
        <p>
            Te enviamos el reporte de ventas correspondiente al período <strong>{{ $periodo }}</strong>
            de la sede <strong>{{ $sucursal->nombre }}</strong>.
            Encuéntralo adjunto a este correo en formato <span class="badge">PDF</span>.
        </p>

        <div class="info-card">
            <p><strong>Sucursal:</strong> {{ $sucursal->nombre }}</p>
            <p><strong>Período:</strong> {{ $periodo }}</p>
            <p><strong>Generado el:</strong> {{ now()->format('d/m/Y \a \l\a\s g:i A') }}</p>
        </div>

        <div class="note">
            📎 El reporte incluye KPIs de ventas, desglose por categoría y tendencia del período.
            Si tienes alguna duda sobre los datos, contáctanos.
        </div>

        <div class="footer">
            Este correo fue enviado automáticamente por SGPD.<br>
            Para modificar o cancelar tus reportes programados, ingresa a la sección de Reportes en el panel de administración.<br><br>
            SGPD &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
