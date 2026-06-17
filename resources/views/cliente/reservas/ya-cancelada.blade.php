<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Ya Cancelada — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--gold:#C48B57;--dark:#1A1208;--surface:#2C2218;--border:rgba(196,139,87,.2);--text:#F5EFE6;--text-dim:#A0907A}
        body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;max-width:500px;width:100%;padding:3rem 2.5rem;box-shadow:0 30px 70px rgba(0,0,0,.5);text-align:center}
        .icon{font-size:3.5rem;margin-bottom:1.5rem;display:block;opacity:.6}
        .card-title{font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:1rem;color:var(--text)}
        .card-desc{color:var(--text-dim);font-size:.95rem;line-height:1.6;margin-bottom:2rem}
        .btn-home{display:inline-block;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:12px;padding:.85rem 2rem;color:var(--text);text-decoration:none;font-weight:600;transition:all .2s}
        .btn-home:hover{background:rgba(255,255,255,.1)}
    </style>
</head>
<body>
<div class="card">
    <span class="icon">ℹ️</span>
    <h1 class="card-title">Reserva inactiva</h1>
    <p class="card-desc">La reserva <strong>{{ $reserva->codigo_reserva }}</strong> ya se encuentra en estado <strong>{{ $reserva->estado->etiqueta() }}</strong>. No es posible cancelarla nuevamente.</p>
    <a href="{{ route('cliente.reservas.formulario', $reserva->sucursal->slug) }}" class="btn-home">Hacer nueva reserva</a>
</div>
</body>
</html>
