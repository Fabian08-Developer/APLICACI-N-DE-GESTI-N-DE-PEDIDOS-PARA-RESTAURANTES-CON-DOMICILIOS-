<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago del Depósito — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--gold:#C48B57;--dark:#1A1208;--surface:#2C2218;--surface2:#362A1C;--border:rgba(196,139,87,.2);--text:#F5EFE6;--text-dim:#A0907A;--radius:14px}
        body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1rem;
            background-image:radial-gradient(ellipse at 30% 0%,rgba(196,139,87,.08) 0%,transparent 55%)}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;max-width:560px;width:100%;box-shadow:0 30px 70px rgba(0,0,0,.5)}
        .card-header{padding:2rem;border-bottom:1px solid var(--border);text-align:center}
        .header-icon{font-size:2.5rem;margin-bottom:.75rem;display:block}
        .card-header h1{font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:.35rem}
        .card-header p{color:var(--text-dim);font-size:.9rem}
        .amount-box{margin:2rem 2rem 0;background:rgba(196,139,87,.08);border:1px solid var(--border);border-radius:12px;padding:1.5rem;text-align:center}
        .amount-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text-dim);display:block;margin-bottom:8px}
        .amount-value{font-family:'Playfair Display',serif;font-size:2.5rem;color:var(--gold)}
        .amount-sub{font-size:.82rem;color:var(--text-dim);margin-top:4px}
        .card-body{padding:2rem}
        .method-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-bottom:1.5rem}
        .method-btn{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:.85rem .5rem;text-align:center;cursor:pointer;transition:all .2s;font-size:.82rem;color:var(--text-dim)}
        .method-btn:hover{border-color:rgba(196,139,87,.4);color:var(--text)}
        .method-btn.active{border-color:var(--gold);background:rgba(196,139,87,.1);color:var(--gold);font-weight:600}
        .method-icon{font-size:1.3rem;display:block;margin-bottom:.35rem}
        .form-group{margin-bottom:1.25rem}
        .form-label{display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);font-weight:600;margin-bottom:.5rem}
        .form-control{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:.85rem 1rem;color:var(--text);font-family:'Inter',sans-serif;font-size:.95rem;width:100%;transition:border-color .2s}
        .form-control:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(196,139,87,.1)}
        .method-fields{display:none}
        .method-fields.active{display:block}
        .info-nequi{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:1rem;margin-bottom:1.25rem;font-size:.85rem;color:var(--text-dim);line-height:1.6}
        .info-nequi strong{color:var(--gold)}
        .btn-pagar{width:100%;background:linear-gradient(135deg,var(--gold),#a67040);color:#fff;border:none;border-radius:12px;padding:1.1rem;font-family:'Inter',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;transition:all .2s;box-shadow:0 8px 20px rgba(196,139,87,.25);margin-top:.5rem}
        .btn-pagar:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(196,139,87,.35)}
        .info-reserva{background:rgba(255,255,255,.02);border-radius:10px;padding:1rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,.06)}
        .info-row{display:flex;justify-content:space-between;padding:.4rem 0;font-size:.88rem}
        .il{color:var(--text-dim)}
        .iv{font-weight:600}
        .errors{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:1rem;margin-bottom:1.25rem;font-size:.88rem;color:#f87171}
        input[type=hidden]{display:none}
    </style>
</head>
<body>
<div class="card">
    <div class="card-header">
        <span class="header-icon">💳</span>
        <h1>Pago del Depósito</h1>
        <p>Para confirmar tu reserva, completa el pago del depósito de garantía</p>
    </div>

    <div class="amount-box">
        <span class="amount-label">Monto del depósito</span>
        <div class="amount-value">${{ number_format($reserva->monto_deposito, 0, ',', '.') }}</div>
        <div class="amount-sub">COP · Reserva {{ $reserva->codigo_reserva }}</div>
    </div>

    <div class="card-body">
        {{-- Resumen de reserva --}}
        <div class="info-reserva">
            <div class="info-row"><span class="il">Fecha</span><span class="iv">{{ $reserva->fecha_reserva->format('d/m/Y') }}</span></div>
            <div class="info-row"><span class="il">Hora</span><span class="iv">{{ $reserva->hora_inicio }}</span></div>
            <div class="info-row"><span class="il">Personas</span><span class="iv">{{ $reserva->numero_personas }}</span></div>
            <div class="info-row"><span class="il">A nombre de</span><span class="iv">{{ $reserva->nombre_cliente }}</span></div>
        </div>

        @if($errors->any())
        <div class="errors">⚠ {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('cliente.reservas.deposito.procesar', ['slug' => $sucursal->slug, 'codigo' => $reserva->codigo_reserva]) }}">
            @csrf

            {{-- Selector de método --}}
            <div class="form-group">
                <div class="form-label">Método de pago</div>
                <div class="method-grid">
                    <div class="method-btn active" data-method="efectivo" onclick="selectMethod('efectivo', this)">
                        <span class="method-icon">💵</span>
                        Efectivo
                    </div>
                    <div class="method-btn" data-method="nequi" onclick="selectMethod('nequi', this)">
                        <span class="method-icon">📱</span>
                        Nequi
                    </div>
                    <div class="method-btn" data-method="transferencia" onclick="selectMethod('transferencia', this)">
                        <span class="method-icon">🏦</span>
                        Transferencia
                    </div>
                </div>
                <input type="hidden" name="metodo" id="metodoInput" value="efectivo">
            </div>

            {{-- Efectivo --}}
            <div class="method-fields active" id="fields-efectivo">
                <div class="info-nequi">
                    💵 El pago en <strong>efectivo</strong> se realiza directamente en el restaurante al momento de la reserva o llegada.
                    Tu reserva quedará registrada como pendiente hasta que el staff confirme el pago.
                </div>
                <div class="form-group">
                    <label class="form-label">Nota adicional (opcional)</label>
                    <input type="text" name="notas" class="form-control" placeholder="Ej: Pagaré al llegar">
                </div>
            </div>

            {{-- Nequi --}}
            <div class="method-fields" id="fields-nequi">
                <div class="info-nequi">
                    📱 Realiza el pago a nuestro número Nequi: <strong>{{ $sucursal->telefono ?? '310 000 0000' }}</strong>
                    <br>Luego ingresa el número de tu celular y el comprobante.
                </div>
                <div class="form-group">
                    <label class="form-label">Tu número de celular Nequi *</label>
                    <input type="tel" name="nequi_telefono" class="form-control" placeholder="300 000 0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Número de comprobante (opcional)</label>
                    <input type="text" name="referencia" class="form-control" placeholder="Referencia de transacción">
                </div>
            </div>

            {{-- Transferencia --}}
            <div class="method-fields" id="fields-transferencia">
                <div class="info-nequi">
                    🏦 Realiza una transferencia a la siguiente cuenta:
                    <br><strong>Banco:</strong> {{ $sucursal->configuracion['banco'] ?? 'Bancolombia' }}
                    <br><strong>Cuenta:</strong> {{ $sucursal->configuracion['cuenta_banco'] ?? 'Contacta al restaurante' }}
                    <br><strong>Titular:</strong> {{ $sucursal->nombre }}
                </div>
                <div class="form-group">
                    <label class="form-label">Número de referencia de la transferencia *</label>
                    <input type="text" name="referencia" class="form-control" placeholder="Número de comprobante" required>
                </div>
            </div>

            <button type="submit" class="btn-pagar">Registrar pago →</button>
        </form>
    </div>
</div>

<script>
function selectMethod(method, el) {
    document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.method-fields').forEach(f => f.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('fields-' + method).classList.add('active');
    document.getElementById('metodoInput').value = method;
}
</script>
</body>
</html>
