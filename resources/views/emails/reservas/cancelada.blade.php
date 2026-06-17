<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 0; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 16px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #4a1515 0%, #7a2020 100%); padding: 36px 36px 28px; text-align: center; }
        .header-icon { font-size: 2.5rem; margin-bottom: 12px; display: block; }
        .header h1 { font-family: 'DM Serif Display', Georgia, serif; color: #F9F7F3; margin: 0; font-size: 1.5rem; font-weight: 400; }
        .header p { color: #f4a0a0; margin: 6px 0 0; font-size: 0.9rem; }
        .body { padding: 36px; }
        .greeting { font-size: 1rem; color: #2C2621; margin: 0 0 20px; }
        .cancel-box { background: #FFF5F5; border: 1px solid #FCA5A5; border-radius: 12px; padding: 20px; margin: 24px 0; }
        .cancel-box .label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: #DC2626; font-weight: 700; margin-bottom: 8px; }
        .cancel-box .code { font-family: 'Courier New', monospace; font-size: 1.4rem; font-weight: 700; color: #991B1B; letter-spacing: 0.1em; }
        .details { background: #FAFAF8; border: 1px solid #EDE9E4; border-radius: 12px; padding: 24px; margin: 24px 0; }
        .details-title { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin: 0 0 16px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #EDE9E4; font-size: 0.9rem; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; }
        .detail-value { color: #2C2621; font-weight: 600; }
        .motivo { background: #FFF8F0; border-left: 3px solid #DC2626; border-radius: 0 8px 8px 0; padding: 14px 18px; font-size: 0.85rem; color: #7f1d1d; margin: 20px 0; }
        .note { font-size: 0.88rem; color: #555; line-height: 1.6; margin-top: 20px; }
        .footer { padding: 24px 36px; border-top: 1px solid #EDE9E4; text-align: center; font-size: 0.78rem; color: #AAA; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="header-icon">❌</span>
            <h1>Reserva cancelada</h1>
            <p>{{ $reserva->sucursal->nombre ?? 'Nuestro restaurante' }}</p>
        </div>
        <div class="body">
            <p class="greeting">Hola, <strong>{{ $reserva->nombre_cliente }}</strong>.</p>
            <p style="color:#555; font-size:0.95rem; line-height:1.6;">Te informamos que tu reserva ha sido cancelada. A continuación los detalles:</p>

            <div class="cancel-box">
                <div class="label">Código de Reserva Cancelada</div>
                <div class="code">{{ $reserva->codigo_reserva }}</div>
            </div>

            <div class="details">
                <div class="details-title">Detalles de la reserva</div>
                <div class="detail-row">
                    <span class="detail-label">📅 Fecha</span>
                    <span class="detail-value">{{ $reserva->fecha_reserva->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">🕐 Hora</span>
                    <span class="detail-value">{{ $reserva->hora_inicio }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">👥 Personas</span>
                    <span class="detail-value">{{ $reserva->numero_personas }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cancelada por</span>
                    <span class="detail-value">{{ $reserva->cancelado_por === 'cliente' ? 'Tú' : 'El restaurante' }}</span>
                </div>
            </div>

            @if($reserva->motivo_cancelacion)
            <div class="motivo">
                <strong>Motivo:</strong> {{ $reserva->motivo_cancelacion }}
            </div>
            @endif

            <p class="note">
                Si deseas hacer una nueva reserva, visita nuestro restaurante o accede al enlace de reservas.
                <br><br>
                Esperamos poder atenderte pronto. 🍽️
            </p>
        </div>
        <div class="footer">
            {{ $reserva->sucursal->nombre ?? 'El equipo del restaurante' }} &copy; {{ date('Y') }}<br>
            Este correo fue enviado a {{ $reserva->correo_cliente }}
        </div>
    </div>
</body>
</html>
