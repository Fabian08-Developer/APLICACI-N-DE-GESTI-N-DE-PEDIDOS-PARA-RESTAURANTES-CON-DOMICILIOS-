<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--gold:#C48B57;--dark:#1A1208;--surface:#2C2218;--border:rgba(196,139,87,0.2);--text:#F5EFE6;--text-dim:#A0907A}
        body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;
            background-image:radial-gradient(ellipse at 50% 0%,rgba(196,139,87,0.1) 0%,transparent 60%)}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;max-width:560px;width:100%;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,0.5)}
        .card-hero{background:linear-gradient(135deg,#1a3320,#0d2015);padding:3rem 2rem;text-align:center;border-bottom:1px solid var(--border)}
        .check-circle{width:72px;height:72px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;box-shadow:0 12px 32px rgba(16,185,129,0.3)}
        .check-circle svg{width:36px;height:36px;color:white}
        .hero-title{font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--text);margin-bottom:0.4rem}
        .hero-sub{color:#6ee7b7;font-size:0.9rem}
        .card-body{padding:2rem}
        .code-box{background:rgba(196,139,87,0.08);border:1px solid var(--border);border-radius:12px;padding:1.25rem;text-align:center;margin-bottom:1.5rem}
        .code-label{font-size:0.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text-dim);display:block;margin-bottom:6px}
        .code-value{font-family:'Courier New',monospace;font-size:1.6rem;font-weight:700;color:var(--gold);letter-spacing:.12em}
        .details-grid{display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.5rem}
        .detail-row{display:flex;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:.9rem}
        .detail-row:last-child{border-bottom:none}
        .dl{color:var(--text-dim)}
        .dv{color:var(--text);font-weight:600;text-align:right;max-width:65%}
        .badge{display:inline-block;padding:.3rem .8rem;border-radius:100px;font-size:.75rem;font-weight:700;text-transform:uppercase}
        .badge-ok{background:rgba(16,185,129,.15);color:#34d399}
        .badge-pending{background:rgba(196,139,87,.15);color:var(--gold)}
        .deposit-alert{background:rgba(196,139,87,.08);border:1px solid var(--border);border-left:3px solid var(--gold);border-radius:0 10px 10px 0;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:.875rem;color:var(--text-dim);line-height:1.5}
        .deposit-alert strong{color:var(--gold)}
        .btn-primary{display:block;width:100%;background:linear-gradient(135deg,var(--gold),#a67040);color:#fff;border:none;border-radius:12px;padding:1rem;font-family:'Inter',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;text-align:center;text-decoration:none;margin-bottom:.75rem;transition:all .2s;box-shadow:0 8px 20px rgba(196,139,87,.25)}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(196,139,87,.35)}
        .btn-link{display:block;text-align:center;color:var(--text-dim);font-size:.85rem;text-decoration:none;border-bottom:1px solid transparent;padding-bottom:1px;transition:.2s}
        .btn-link:hover{color:var(--text-dim);border-bottom-color:var(--text-dim)}
        .note{font-size:.8rem;color:var(--text-dim);text-align:center;margin-top:1.25rem;line-height:1.6}
    </style>
</head>
<body>
<div class="card">
    <div class="card-hero">
        <div class="check-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="hero-title">¡Reserva registrada!</div>
        <div class="hero-sub">
            @if($reserva->deposito_pagado || $reserva->monto_deposito == 0)
                Tu mesa está confirmada ✓
            @else
                Pendiente de confirmación de pago
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="code-box">
            <span class="code-label">Código de Reserva</span>
            <div class="code-value">{{ $reserva->codigo_reserva }}</div>
        </div>

        @if($reserva->monto_deposito > 0 && !$reserva->deposito_pagado)
        <div class="deposit-alert">
            💳 <strong>Depósito pendiente:</strong> Para confirmar tu reserva, debes pagar el depósito de <strong>${{ number_format($reserva->monto_deposito, 0, ',', '.') }}</strong>.
        </div>
        @endif

        <div class="details-grid">
            <div class="detail-row">
                <span class="dl">📅 Fecha</span>
                <span class="dv">{{ $reserva->fecha_reserva->translatedFormat('l d \d\e F') }}</span>
            </div>
            <div class="detail-row">
                <span class="dl">🕐 Hora</span>
                <span class="dv">{{ $reserva->hora_inicio }} — {{ $reserva->hora_fin }}</span>
            </div>
            @if($reserva->mesa)
            <div class="detail-row">
                <span class="dl">🪑 Mesa</span>
                <span class="dv">Mesa #{{ $reserva->mesa->numero }} ({{ $reserva->mesa->capacidad }} personas)</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="dl">👥 Personas</span>
                <span class="dv">{{ $reserva->numero_personas }}</span>
            </div>
            <div class="detail-row">
                <span class="dl">📍 Restaurante</span>
                <span class="dv">{{ $reserva->sucursal->nombre }}</span>
            </div>
            <div class="detail-row">
                <span class="dl">Estado</span>
                <span class="dv">
                    @if($reserva->estado->value === 'confirmada')
                        <span class="badge badge-ok">Confirmada ✓</span>
                    @else
                        <span class="badge badge-pending">{{ $reserva->estado->etiqueta() }}</span>
                    @endif
                </span>
            </div>
        </div>

        @if($reserva->monto_deposito > 0 && !$reserva->deposito_pagado)
        <a href="{{ route('cliente.reservas.deposito', ['slug' => $reserva->sucursal->slug, 'codigo' => $reserva->codigo_reserva]) }}"
           class="btn-primary">
            Pagar depósito de garantía →
        </a>
        @endif

        <a href="{{ route('cliente.reservas.cancelar', $reserva->codigo_reserva) }}" class="btn-link">
            Cancelar esta reserva
        </a>

        <p class="note">
            Recibirás un correo de confirmación en <strong>{{ $reserva->correo_cliente }}</strong>.<br>
            Guarda tu código <strong>{{ $reserva->codigo_reserva }}</strong> para cualquier cambio.
        </p>
    </div>
</div>
</body>
</html>
