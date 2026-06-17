<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Reserva — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--gold:#C48B57;--dark:#1A1208;--surface:#2C2218;--border:rgba(196,139,87,.2);--text:#F5EFE6;--text-dim:#A0907A;--red:#ef4444}
        body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;max-width:500px;width:100%;padding:2.5rem;box-shadow:0 30px 70px rgba(0,0,0,.5)}
        .card-title{font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:1rem;color:var(--text);text-align:center}
        .card-desc{color:var(--text-dim);font-size:.9rem;line-height:1.6;margin-bottom:2rem;text-align:center}
        .info-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:1.25rem;margin-bottom:2rem}
        .info-row{display:flex;justify-content:space-between;padding:.4rem 0;font-size:.88rem}
        .il{color:var(--text-dim)}
        .iv{font-weight:600}
        .form-group{margin-bottom:1.5rem}
        .form-label{display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);font-weight:600;margin-bottom:.5rem}
        .form-control{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:.85rem 1rem;color:var(--text);font-family:'Inter',sans-serif;font-size:.95rem;width:100%}
        .btn-red{width:100%;background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:1rem;font-family:inherit;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;margin-bottom:1rem}
        .btn-red:hover{background:var(--red);color:#fff}
        .btn-link{display:block;text-align:center;color:var(--text-dim);text-decoration:none;font-size:.9rem}
        .btn-link:hover{color:var(--text)}
    </style>
</head>
<body>
<div class="card">
    <h1 class="card-title">Cancelar Reserva</h1>
    <p class="card-desc">Lamentamos que no puedas acompañarnos. Por favor confirma la cancelación de tu reserva.</p>
    
    <div class="info-box">
        <div class="info-row"><span class="il">Código</span><span class="iv">{{ $reserva->codigo_reserva }}</span></div>
        <div class="info-row"><span class="il">Fecha</span><span class="iv">{{ $reserva->fecha_reserva->format('d/m/Y') }}</span></div>
        <div class="info-row"><span class="il">Hora</span><span class="iv">{{ $reserva->hora_inicio }}</span></div>
        <div class="info-row"><span class="il">Personas</span><span class="iv">{{ $reserva->numero_personas }}</span></div>
    </div>

    <form method="POST" action="{{ route('cliente.reservas.cancelar.procesar', $reserva->codigo_reserva) }}">
        @csrf
        <div class="form-group">
            <label class="form-label">Motivo (Opcional)</label>
            <textarea name="motivo" class="form-control" rows="3" placeholder="Ej: Cambio de planes, enfermedad..."></textarea>
        </div>
        
        <button type="submit" class="btn-red">Confirmar Cancelación</button>
        <a href="{{ route('cliente.reservas.confirmada', $reserva->codigo_reserva) }}" class="btn-link">Volver a mi reserva</a>
    </form>
</div>
</body>
</html>
