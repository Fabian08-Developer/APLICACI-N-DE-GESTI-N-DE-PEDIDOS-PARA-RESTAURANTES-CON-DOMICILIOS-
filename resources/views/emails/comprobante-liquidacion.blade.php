<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Liquidación</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; padding: 40px 20px; }
        .wrapper { max-width: 580px; margin: 0 auto; }
        .card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0f172a, #1e293b); padding: 36px 32px; text-align: center; }
        .logo { font-size: 2rem; margin-bottom: 8px; }
        .header h1 { color: #fff; font-size: 1.4rem; font-weight: 700; }
        .header p { color: #94a3b8; font-size: 0.85rem; margin-top: 4px; }
        .badge { display: inline-block; background: #22c55e20; color: #22c55e; border: 1px solid #22c55e40; padding: 4px 14px; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-top: 12px; letter-spacing: 0.05em; }
        .body { padding: 32px; }
        .greeting { font-size: 1rem; color: #334155; margin-bottom: 20px; }
        .amount-box { background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 28px; }
        .amount-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; }
        .amount-value { font-size: 2.5rem; font-weight: 800; color: #0f172a; margin: 8px 0; }
        .amount-sub { font-size: 0.8rem; color: #94a3b8; }
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        .detail-table td { padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; }
        .detail-table td:first-child { color: #64748b; font-weight: 500; }
        .detail-table td:last-child { color: #0f172a; font-weight: 600; text-align: right; }
        .detail-table tr:last-child td { border-bottom: none; }
        .notes-box { background: #fefce8; border: 1px solid #fde047; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px; font-size: 0.85rem; color: #713f12; }
        .notes-label { font-weight: 700; margin-bottom: 4px; }
        .footer-msg { background: #f0fdf4; border-radius: 10px; padding: 16px 20px; text-align: center; font-size: 0.875rem; color: #16a34a; font-weight: 500; margin-bottom: 24px; }
        .footer { text-align: center; padding: 20px 32px 32px; }
        .footer p { font-size: 0.75rem; color: #94a3b8; line-height: 1.6; }
        .divider { height: 4px; background: linear-gradient(90deg, #c9a84c, #f59e0b, #c9a84c); }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">☕</div>
                <h1>Comprobante de Liquidación</h1>
                <p>Entrega de efectivo registrada</p>
                <span class="badge">✓ Liquidación Completada</span>
            </div>
            <div class="divider"></div>

            <div class="body">
                <p class="greeting">
                    Hola <strong>{{ $liquidacion->perfil->nombre }}</strong>,<br>
                    Este es el comprobante oficial de tu liquidación de caja del turno.
                </p>

                <div class="amount-box">
                    <div class="amount-label">Monto Liquidado</div>
                    <div class="amount-value">${{ number_format($liquidacion->monto, 0, ',', '.') }}</div>
                    <div class="amount-sub">COP · {{ \Carbon\Carbon::parse($liquidacion->liquidado_en)->format('d/m/Y H:i') }}</div>
                </div>

                <table class="detail-table">
                    <tr>
                        <td>Domiciliario</td>
                        <td>{{ $liquidacion->perfil->nombre }}</td>
                    </tr>
                    <tr>
                        <td>Aprobado por</td>
                        <td>{{ $liquidacion->aprobador->nombre ?? 'Administrador' }}</td>
                    </tr>
                    <tr>
                        <td>Fecha y hora</td>
                        <td>{{ \Carbon\Carbon::parse($liquidacion->liquidado_en)->format('d \d\e F \d\e Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Saldo anterior</td>
                        <td>${{ number_format($liquidacion->monto, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Saldo actual</td>
                        <td style="color: #22c55e;">$0</td>
                    </tr>
                </table>

                @if($liquidacion->notas)
                <div class="notes-box">
                    <div class="notes-label">Notas del administrador:</div>
                    {{ $liquidacion->notas }}
                </div>
                @endif

                <div class="footer-msg">
                    Tu efectivo fue recibido correctamente en la sede. ¡Gracias por tu trabajo!
                </div>
            </div>

            <div class="footer">
                <p>Este comprobante fue generado automáticamente por el sistema de gestión.<br>
                   Consérvalo como registro de la liquidación.</p>
            </div>
        </div>
    </div>
</body>
</html>
