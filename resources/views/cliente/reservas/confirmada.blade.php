<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --gold: #C48B57;
            --gold-light: #d8aa7e;
            --dark: #0D0B09;
            --surface: rgba(25, 20, 16, 0.85);
            --border: rgba(196, 139, 87, 0.2);
            --text: #F5EFE6;
            --text-dim: #A0907A;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-image:
                radial-gradient(ellipse at 50% 0%, rgba(196, 139, 87, 0.1) 0%, transparent 60%);
        }

        .ticket {
            width: 100%;
            max-width: 480px;
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
            overflow: hidden;
            position: relative;
        }

        /* ── Hero / Header ── */
        .ticket-hero {
            padding: 3rem 2rem 2.5rem;
            text-align: center;
            background: linear-gradient(180deg, rgba(16, 185, 129, 0.05) 0%, transparent 100%);
            position: relative;
        }

        .check-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--success), #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 12px 32px var(--success-glow), inset 0 -4px 10px rgba(0, 0, 0, 0.2);
        }

        .check-circle svg {
            width: 40px;
            height: 40px;
            color: #fff;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .hero-sub {
            color: #6ee7b7;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* ── Perforated Divider ── */
        .divider {
            position: relative;
            height: 30px;
            background: transparent;
            margin-top: -15px;
            z-index: 10;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 30px;
            height: 30px;
            background: var(--dark);
            border-radius: 50%;
            transform: translateY(-50%);
            border: 1px solid var(--border);
        }

        .divider::before {
            left: -16px;
            border-right: none;
        }

        .divider::after {
            right: -16px;
            border-left: none;
        }

        .divider-line {
            position: absolute;
            top: 50%;
            left: 20px;
            right: 20px;
            height: 1px;
            border-top: 2px dashed var(--border);
            opacity: 0.5;
        }

        /* ── Body / Details ── */
        .ticket-body {
            padding: 1.5rem 2.5rem 2.5rem;
        }

        .code-box {
            text-align: center;
            margin-bottom: 2rem;
        }

        .code-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--text-dim);
            margin-bottom: 0.5rem;
            display: block;
        }

        .code-value {
            font-family: 'Space Mono', monospace;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 0.15em;
            text-shadow: 0 0 20px rgba(196, 139, 87, 0.3);
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .dl {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-dim);
        }

        .dv {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text);
        }

        .dv-full {
            grid-column: 1 / -1;
        }

        .deposit-alert {
            background: rgba(196, 139, 87, 0.1);
            border: 1px solid rgba(196, 139, 87, 0.3);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: var(--text-dim);
            line-height: 1.6;
            display: flex;
            gap: 1rem;
        }

        .deposit-alert strong {
            color: var(--gold);
        }

        .deposit-alert i {
            font-size: 1.5rem;
            font-style: normal;
        }

        .btn-primary {
            display: block;
            width: 100%;
            background: var(--gold);
            color: var(--dark);
            border: none;
            border-radius: 12px;
            padding: 1.2rem;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(196, 139, 87, 0.3);
        }

        .btn-link {
            display: block;
            text-align: center;
            color: var(--text-dim);
            font-size: 0.85rem;
            text-decoration: none;
            padding: 0.5rem;
            transition: color 0.2s;
        }

        .btn-link:hover {
            color: var(--text);
        }

        .footer-note {
            font-size: 0.8rem;
            color: var(--text-dim);
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            line-height: 1.6;
        }
    </style>
</head>

<body>

    <div class="ticket">
        <div class="ticket-hero">
            <div class="check-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </div>
            <div class="hero-title">¡Reserva registrada!</div>
            <div class="hero-sub">
                @if($reserva->deposito_pagado || $reserva->monto_deposito == 0)
                Tu lugar está asegurado
                @else
                Pendiente de pago
                @endif
            </div>
        </div>

        <div class="divider">
            <div class="divider-line"></div>
        </div>

        <div class="ticket-body">

            <div class="code-box">
                <span class="code-label">Ticket No.</span>
                <div class="code-value">{{ $reserva->codigo_reserva }}</div>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <span class="dl">Fecha</span>
                    <span class="dv">{{ $reserva->fecha_reserva->translatedFormat('d M, Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="dl">Hora</span>
                    <span class="dv">{{ substr($reserva->hora_inicio,0,5) }}</span>
                </div>
                <div class="detail-item">
                    <span class="dl">Personas</span>
                    <span class="dv">{{ $reserva->numero_personas }}</span>
                </div>
                <div class="detail-item">
                    <span class="dl">Mesa(s)</span>
                    <span class="dv">{{ $reserva->mesas->count() > 0 ? $reserva->mesas->pluck('numero')->join(', ') : 'Auto' }}</span>
                </div>
                <div class="detail-item dv-full">
                    <span class="dl">A nombre de</span>
                    <span class="dv" style="font-family:'Playfair Display',serif;font-size:1.2rem;color:var(--gold);">{{ $reserva->nombre_cliente }}</span>
                </div>
            </div>

            @if($reserva->monto_deposito > 0 && !$reserva->deposito_pagado)
            <div class="deposit-alert">
                <i>💳</i>
                <div>
                    Para garantizar tu reserva debes completar el depósito de <strong>${{ number_format($reserva->monto_deposito, 0, ',', '.') }}</strong>.
                </div>
            </div>

            <a href="{{ route('cliente.reservas.deposito', ['slug' => $reserva->sucursal->slug, 'codigo' => $reserva->codigo_reserva]) }}" class="btn-primary">
                Pagar depósito ahora →
            </a>
            @endif

            <a href="{{ route('cliente.reservas.cancelar', $reserva->codigo_reserva) }}" class="btn-link">
                Necesito cancelar mi reserva
            </a>

            <div class="footer-note">
                Enviamos una copia a <strong>{{ $reserva->correo_cliente }}</strong>.<br>
                Presenta este código al llegar a <strong>{{ $reserva->sucursal->nombre }}</strong>.
            </div>
        </div>
    </div>

</body>

</html>