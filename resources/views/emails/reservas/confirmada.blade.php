<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DM Sans', 'Outfit', sans-serif;
            color: #2C2621;
            background: #F9F7F3;
            margin: 0;
            padding: 20px;
        }

        .container {
            padding: 0;
            background: #FFFFFF;
            border: 1px solid #e8e4e0;
            border-radius: 16px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2C2621 0%, #4a3728 100%);
            padding: 36px 36px 28px;
            text-align: center;
        }

        .header-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }

        .header h1 {
            font-family: 'DM Serif Display', Georgia, serif;
            color: #F9F7F3;
            margin: 0;
            font-size: 1.6rem;
            font-weight: 400;
        }

        .header p {
            color: #C48B57;
            margin: 6px 0 0;
            font-size: 0.9rem;
        }

        .body {
            padding: 36px;
        }

        .greeting {
            font-size: 1rem;
            color: #2C2621;
            margin: 0 0 20px;
        }

        .code-box {
            background: linear-gradient(135deg, #FFF8F0, #FFF3E5);
            border: 2px solid #C48B57;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }

        .code-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #888;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .code-value {
            font-family: 'Courier New', monospace;
            font-size: 1.8rem;
            font-weight: 700;
            color: #2C2621;
            letter-spacing: 0.12em;
        }

        .details {
            background: #FAFAF8;
            border: 1px solid #EDE9E4;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }

        .details-title {
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #C48B57;
            margin: 0 0 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #EDE9E4;
            font-size: 0.9rem;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #888;
            font-weight: 500;
        }

        .detail-value {
            color: #2C2621;
            font-weight: 600;
            text-align: right;
        }

        .badge-confirm {
            display: inline-block;
            background: #DEF7EC;
            color: #03543F;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .cancel-btn {
            display: block;
            text-align: center;
            margin: 28px 0 8px;
        }

        .cancel-link {
            color: #888;
            font-size: 0.82rem;
            text-decoration: none;
            border-bottom: 1px solid #ccc;
            padding-bottom: 1px;
        }

        .note {
            background: #FFF8F0;
            border-left: 3px solid #C48B57;
            border-radius: 0 8px 8px 0;
            padding: 14px 18px;
            font-size: 0.85rem;
            color: #6B5344;
            margin: 20px 0;
        }

        .footer {
            padding: 24px 36px;
            border-top: 1px solid #EDE9E4;
            text-align: center;
            font-size: 0.78rem;
            color: #AAA;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <span class="header-icon">🍽️</span>
            <h1>¡Tu reserva está confirmada!</h1>
            <p>{{ $reserva->sucursal->nombre ?? 'Nuestro restaurante' }}</p>
        </div>
        <div class="body">
            <p class="greeting">Hola, <strong>{{ $reserva->nombre_cliente }}</strong>.</p>
            <p style="color:#555; font-size:0.95rem; line-height:1.6;">Tu reserva ha sido confirmada exitosamente. Te esperamos a la hora indicada. Por favor guarda este código para cualquier cambio.</p>

            <div class="code-box">
                <span class="code-label">Código de Reserva</span>
                <div class="code-value">{{ $reserva->codigo_reserva }}</div>
            </div>

            <div class="details">
                <div class="details-title">Detalles de tu reserva</div>
                <div class="detail-row">
                    <span class="detail-label">Fecha</span>
                    <span class="detail-value">{{ $reserva->fecha_reserva->translatedFormat('l, d \d\e F \d\e Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Hora</span>
                    <span class="detail-value">{{ $reserva->hora_inicio }} — {{ $reserva->hora_fin }}</span>
                </div>
                @if($reserva->mesas->count() > 0)
                <div class="detail-row">
                    <span class="detail-label">Mesa(s)</span>
                    <span class="detail-value">Mesa(s) #{{ $reserva->mesas->pluck('numero')->join(', ') }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Personas</span>
                    <span class="detail-value">{{ $reserva->numero_personas }}</span>
                </div>
                @if($reserva->notas_cliente)
                <div class="detail-row">
                    <span class="detail-label">Nota</span>
                    <span class="detail-value" style="max-width:60%;">{{ $reserva->notas_cliente }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Estado</span>
                    <span class="detail-value"><span class="badge-confirm">Confirmada ✓</span></span>
                </div>
            </div>

            <div class="note">
                <strong>Tiempo de llegada:</strong> Por favor llega a tiempo. Tenemos una tolerancia de <strong>15 minutos</strong> antes de liberar tu mesa.
            </div>

            <div style="text-align:center; margin: 24px 0 16px;">
                <a href="{{ url('/reserva/' . $reserva->codigo_reserva) }}" style="display:inline-block; background:#C48B57; color:#FFFFFF; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:600; font-size:0.95rem;">
                    Ver y gestionar mi reserva →
                </a>
            </div>

            <div class="cancel-btn">
                <a href="{{ url('/reserva/' . $reserva->codigo_reserva . '/cancelar') }}" class="cancel-link">
                    ¿Necesitas cancelar? Hazlo aquí
                </a>
            </div>
        </div>
        <div class="footer">
            {{ $reserva->sucursal->nombre ?? 'El equipo del restaurante' }} &copy; {{ date('Y') }}<br>
            Este correo fue enviado a {{ $reserva->correo_cliente }}
        </div>
    </div>
</body>

</html>