<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelación Bloqueada — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--gold:#C48B57;--dark:#1A1208;--surface:#2C2218;--border:rgba(196,139,87,.2);--text:#F5EFE6;--text-dim:#A0907A}
        body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;max-width:500px;width:100%;padding:3rem 2.5rem;box-shadow:0 30px 70px rgba(0,0,0,.5);text-align:center}
        .icon{font-size:3.5rem;margin-bottom:1.5rem;display:block;}
        .card-title{font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:1rem;color:var(--text)}
        .card-desc{color:var(--text-dim);font-size:.95rem;line-height:1.6;margin-bottom:2rem}
        .btn-home{display:inline-block;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:12px;padding:.85rem 2rem;color:var(--text);text-decoration:none;font-weight:600;transition:all .2s}
        .btn-home:hover{background:rgba(255,255,255,.1)}
    </style>
</head>
<body>
<div class="card">
    <span class="icon">⏳</span>
    <h1 class="card-title">Cancelación no disponible</h1>
    <p class="card-desc">Lo sentimos, las políticas del restaurante no permiten cancelar la reserva <strong>{{ $reserva->codigo_reserva }}</strong> porque faltan menos de <strong>{{ $limiteMinutos }} minutos</strong> para la hora de llegada programada ({{ $reserva->hora_inicio }}).</p>
    <p class="card-desc" style="font-size: 0.85rem; margin-top: -1rem;">Por favor comunícate directamente con el restaurante si necesitas asistencia urgente.</p>
    <a href="{{ route('cliente.reservas.confirmada', $reserva->codigo_reserva) }}" class="btn-home">Volver a mi reserva</a>
</div>
</body>
</html>
