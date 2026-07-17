<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DM Sans', 'Outfit', sans-serif; color: #2C2621; background: #F9F7F3; margin: 0; padding: 20px; }
        .container { padding: 0; background: #FFFFFF; border: 1px solid #e8e4e0; border-radius: 16px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden; }
        .header { background: linear-gradient(135deg, #7c4d00 0%, #A85507 100%); padding: 36px 36px 28px; text-align: center; }
        .header-icon { font-size: 2.5rem; margin-bottom: 12px; display: block; }
        .header h1 { font-family: 'DM Serif Display', Georgia, serif; color: #F9F7F3; margin: 0; font-size: 1.5rem; font-weight: 400; }
        .header p { color: #fdd9b5; margin: 6px 0 0; font-size: 0.9rem; }
        .body { padding: 36px; }
        .greeting { font-size: 1rem; color: #2C2621; margin: 0 0 20px; }
        .code-box { background: #FFF8F0; border: 1px solid #F9C784; border-radius: 12px; padding: 20px; margin: 24px 0; text-align: center; }
        .code-box .label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: #A85507; font-weight: 700; margin-bottom: 8px; }
        .code-box .code { font-family: 'Courier New', monospace; font-size: 1.6rem; font-weight: 700; color: #7c4d00; letter-spacing: 0.12em; }
        .change-box { display: flex; gap: 16px; margin: 24px 0; }
        .change-col { flex: 1; border-radius: 12px; padding: 18px; }
        .change-col.before { background: #fef2f2; border: 1px solid #fca5a5; }
        .change-col.after  { background: #f0fdf4; border: 1px solid #86efac; }
        .change-col .col-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; margin-bottom: 10px; }
        .change-col.before .col-label { color: #dc2626; }
        .change-col.after  .col-label { color: #16a34a; }
        .change-col .col-date { font-size: 1rem; font-weight: 700; color: #2C2621; }
        .change-col .col-time { font-size: 0.85rem; color: #555; margin-top: 4px; }
        .details { background: #FAFAF8; border: 1px solid #EDE9E4; border-radius: 12px; padding: 24px; margin: 24px 0; }
        .details-title { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin: 0 0 16px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #EDE9E4; font-size: 0.9rem; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #888; }
        .detail-value { color: #2C2621; font-weight: 600; }
        .note { font-size: 0.88rem; color: #555; line-height: 1.6; margin-top: 20px; }
        .footer { padding: 24px 36px; border-top: 1px solid #EDE9E4; text-align: center; font-size: 0.78rem; color: #AAA; }
        @media (max-width: 480px) {
            .change-box { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="header-icon">📅</span>
            <h1>Tu reserva ha sido reprogramada</h1>
            <p>{{ $reserva->sucursal->nombre ?? 'Nuestro restaurante' }}</p>
        </div>
        <div class="body">
            <p class="greeting">Hola, <strong>{{ $reserva->nombre_cliente }}</strong>.</p>
            <p style="color:#555; font-size:0.95rem; line-height:1.6;">
                Queremos informarte que tu reserva ha sido <strong>reprogramada</strong> a una nueva fecha y hora. A continuación encontrarás los detalles del cambio:
            </p>

            <div class="code-box">
                <div class="label">Código de Reserva</div>
                <div class="code">{{ $reserva->codigo_reserva }}</div>
            </div>

            {{-- Bloque ANTES / DESPUÉS --}}
            <div class="change-box">
                <div class="change-col before">
                    <div class="col-label">❌ Fecha anterior</div>
                    <div class="col-date">{{ \Carbon\Carbon::parse($fechaAnterior)->format('d/m/Y') }}</div>
                    <div class="col-time">{{ substr($horaAnterior, 0, 5) }} hrs</div>
                </div>
                <div class="change-col after">
                    <div class="col-label">✅ Nueva fecha</div>
                    <div class="col-date">{{ $reserva->fecha_reserva->format('d/m/Y') }}</div>
                    <div class="col-time">{{ substr($reserva->hora_inicio, 0, 5) }} – {{ substr($reserva->hora_fin, 0, 5) }} hrs</div>
                </div>
            </div>

            <div class="details">
                <div class="details-title">Detalles de tu reserva</div>
                <div class="detail-row">
                    <span class="detail-label">👥 Personas</span>
                    <span class="detail-value">{{ $reserva->numero_personas }}</span>
                </div>
                @if($reserva->mesas->count() > 0)
                <div class="detail-row">
                    <span class="detail-label">🪑 Mesa(s)</span>
                    <span class="detail-value">{{ $reserva->mesas->pluck('numero')->join(', ') }}</span>
                </div>
                @endif
                @if($reserva->notas_cliente)
                <div class="detail-row">
                    <span class="detail-label">📝 Notas</span>
                    <span class="detail-value">{{ $reserva->notas_cliente }}</span>
                </div>
                @endif
            </div>

            <p class="note">
                Si tienes alguna duda o necesitas hacer algún ajuste adicional, por favor contáctanos directamente.<br><br>
                ¡Gracias por tu comprensión y esperamos verte pronto! 🍽️
            </p>
        </div>
        <div class="footer">
            {{ $reserva->sucursal->nombre ?? 'El equipo del restaurante' }} &copy; {{ date('Y') }}<br>
            Este correo fue enviado a {{ $reserva->correo_cliente }}
        </div>
    </div>
</body>
</html>
