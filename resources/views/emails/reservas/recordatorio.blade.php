<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 0; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 16px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #1a3a5c 0%, #2563EB 100%); padding: 36px 36px 28px; text-align: center; }
        .header-icon { font-size: 2.5rem; margin-bottom: 12px; display: block; }
        .header h1 { font-family: 'DM Serif Display', Georgia, serif; color: #F9F7F3; margin: 0; font-size: 1.5rem; font-weight: 400; }
        .header p { color: #93C5FD; margin: 6px 0 0; font-size: 0.9rem; }
        .body { padding: 36px; }
        .time-hero { background: linear-gradient(135deg, #EFF6FF, #DBEAFE); border: 1px solid #93C5FD; border-radius: 16px; padding: 28px; text-align: center; margin: 24px 0; }
        .time-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: #2563EB; font-weight: 700; margin-bottom: 12px; display: block; }
        .time-value { font-family: 'DM Serif Display', Georgia, serif; font-size: 2.5rem; color: #1D4ED8; font-weight: 700; }
        .time-sub { font-size: 0.9rem; color: #3B82F6; margin-top: 4px; }
        .details { background: #FAFAF8; border: 1px solid #EDE9E4; border-radius: 12px; padding: 24px; margin: 24px 0; }
        .details-title { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: #2563EB; margin: 0 0 16px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #EDE9E4; font-size: 0.9rem; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; }
        .detail-value { color: #2C2621; font-weight: 600; }
        .alert { background: #FFF8F0; border-left: 3px solid #F59E0B; border-radius: 0 8px 8px 0; padding: 14px 18px; font-size: 0.85rem; color: #92400E; margin: 20px 0; }
        .footer { padding: 24px 36px; border-top: 1px solid #EDE9E4; text-align: center; font-size: 0.78rem; color: #AAA; }
        .code { font-family: 'Courier New', monospace; background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="header-icon">⏰</span>
            <h1>¡Tu reserva es pronto!</h1>
            <p>{{ $reserva->sucursal->nombre ?? 'Nuestro restaurante' }}</p>
        </div>
        <div class="body">
            <p style="color:#555; font-size:0.95rem; line-height:1.6; margin:0 0 20px;">
                Hola, <strong>{{ $reserva->nombre_cliente }}</strong>. Este es un recordatorio de que tu reserva está a 2 horas.
            </p>

            <div class="time-hero">
                <span class="time-label">Tu hora de llegada</span>
                <div class="time-value">{{ $reserva->hora_inicio }}</div>
                <div class="time-sub">{{ $reserva->fecha_reserva->translatedFormat('l d \d\e F') }}</div>
            </div>

            <div class="details">
                <div class="details-title">Detalles de tu reserva</div>
                <div class="detail-row">
                    <span class="detail-label">Código</span>
                    <span class="detail-value"><span class="code">{{ $reserva->codigo_reserva }}</span></span>
                </div>
                @if($reserva->mesas->count() > 0)
                <div class="detail-row">
                    <span class="detail-label">🪑 Mesa(s)</span>
                    <span class="detail-value">Mesa(s) #{{ $reserva->mesas->pluck('numero')->join(', ') }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">👥 Personas</span>
                    <span class="detail-value">{{ $reserva->numero_personas }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📍 Dirección</span>
                    <span class="detail-value">{{ $reserva->sucursal->direccion ?? '—' }}</span>
                </div>
            </div>

            <div class="alert">
                ⚠️ <strong>Recuerda:</strong> Tenemos una tolerancia de 15 minutos. Si no puedes asistir, cancela con anticipación.
            </div>
        </div>
        <div class="footer">
            {{ $reserva->sucursal->nombre ?? 'El equipo del restaurante' }} &copy; {{ date('Y') }}<br>
            <a href="{{ url('/reserva/' . $reserva->codigo_reserva . '/cancelar') }}" style="color:#aaa;">Cancelar reserva</a>
        </div>
    </div>
</body>
</html>
