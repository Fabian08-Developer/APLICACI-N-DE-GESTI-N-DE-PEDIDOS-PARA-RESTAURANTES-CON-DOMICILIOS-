<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago del Depósito — {{ $reserva->codigo_reserva }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        
        :root {
            --gold:     #C48B57;
            --gold-light: #d8aa7e;
            --gold-dark:#A67040;
            --dark:     #0D0B09;
            --surface:  rgba(25, 20, 16, 0.7);
            --border:   rgba(196,139,87,0.15);
            --border-hover: rgba(196,139,87,0.3);
            --text:     #F5EFE6;
            --text-dim: #A0907A;
            --radius:   20px;
            --shadow-glow: 0 0 50px rgba(196,139,87,0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
                radial-gradient(ellipse at 50% 0%, rgba(196,139,87,0.08) 0%, transparent 60%);
        }

        .checkout-card {
            background: var(--surface);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            max-width: 580px;
            width: 100%;
            box-shadow: var(--shadow-glow);
            overflow: hidden;
        }

        .checkout-header {
            padding: 3rem 2rem 2.5rem;
            text-align: center;
            background: linear-gradient(180deg, rgba(196,139,87,0.05) 0%, transparent 100%);
            border-bottom: 1px solid rgba(255,255,255,0.03);
            position: relative;
        }
        
        .amount-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-dim);
            margin-bottom: 0.5rem;
        }
        
        .amount-value {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 6vw, 4rem);
            font-weight: 600;
            color: var(--gold);
            line-height: 1;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 20px rgba(196,139,87,0.2);
        }
        
        .amount-sub {
            font-size: 0.9rem;
            color: var(--text-dim);
        }

        .checkout-body { padding: 2rem; }

        /* ── Resumen ── */
        .info-reserva {
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.03);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .info-box { display: flex; flex-direction: column; gap: 0.25rem; }
        .il { font-size: 0.75rem; text-transform: uppercase; color: var(--text-dim); letter-spacing: 0.05em; }
        .iv { font-size: 0.95rem; font-weight: 500; }

        /* ── Métodos ── */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
            color: var(--text);
        }

        .method-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .method-btn {
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        .method-btn:hover { border-color: var(--border-hover); transform: translateY(-2px); }
        .method-btn.active {
            background: rgba(196,139,87,0.1);
            border-color: var(--gold);
            box-shadow: 0 4px 15px rgba(196,139,87,0.15);
        }
        .method-icon { font-size: 1.8rem; display: block; margin-bottom: 0.5rem; }
        .method-name { font-size: 0.85rem; font-weight: 500; color: var(--text-dim); transition: var(--transition); }
        .method-btn.active .method-name { color: var(--gold); }

        /* ── Campos ── */
        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-dim);
            margin-bottom: 0.6rem;
        }
        .form-control {
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            width: 100%;
            transition: var(--transition);
        }
        .form-control:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(196,139,87,0.1); }

        .info-alert {
            background: rgba(196,139,87,0.08);
            border: 1px dashed var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-dim);
            line-height: 1.6;
        }
        .info-alert strong { color: var(--gold); font-weight: 600; }

        .btn-pagar {
            width: 100%;
            background: var(--gold);
            color: var(--dark);
            border: none;
            border-radius: 12px;
            padding: 1.2rem;
            font-family: 'Inter', sans-serif;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }
        .btn-pagar:hover { background: var(--gold-light); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(196,139,87,0.3); }

        .errors {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #f87171;
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>
<body>

<div class="checkout-card" x-data="{ method: 'efectivo' }">
    <div class="checkout-header">
        <div class="amount-label">Monto del depósito</div>
        <div class="amount-value">${{ number_format($reserva->monto_deposito, 0, ',', '.') }}</div>
        <div class="amount-sub">Para confirmar la reserva {{ $reserva->codigo_reserva }}</div>
    </div>

    <div class="checkout-body">
        
        <!-- Resumen -->
        <div class="info-reserva">
            <div class="info-box"><span class="il">Fecha</span><span class="iv">{{ $reserva->fecha_reserva->translatedFormat('d \d\e M, Y') }}</span></div>
            <div class="info-box"><span class="il">Hora</span><span class="iv">{{ substr($reserva->hora_inicio,0,5) }}</span></div>
            <div class="info-box"><span class="il">Mesa(s)</span><span class="iv">{{ $reserva->mesas->count() > 0 ? 'Mesa(s) '.$reserva->mesas->pluck('numero')->join(', ') : 'Automática' }}</span></div>
            <div class="info-box"><span class="il">A nombre de</span><span class="iv">{{ $reserva->nombre_cliente }}</span></div>
        </div>

        @if($errors->any())
        <div class="errors">⚠ {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('cliente.reservas.deposito.procesar', ['slug' => $sucursal->slug, 'codigo' => $reserva->codigo_reserva]) }}">
            @csrf

            <h3 class="section-title">Elige cómo pagar</h3>
            
            <div class="method-grid">
                <div class="method-btn" :class="{'active': method === 'efectivo'}" @click="method = 'efectivo'">
                    <span class="method-icon">💵</span>
                    <span class="method-name">Efectivo</span>
                </div>
                <div class="method-btn" :class="{'active': method === 'nequi'}" @click="method = 'nequi'">
                    <span class="method-icon">📱</span>
                    <span class="method-name">Nequi</span>
                </div>
                <div class="method-btn" :class="{'active': method === 'transferencia'}" @click="method = 'transferencia'">
                    <span class="method-icon">🏦</span>
                    <span class="method-name">Transf.</span>
                </div>
            </div>
            
            <input type="hidden" name="metodo" :value="method">

            <!-- Efectivo -->
            <div x-show="method === 'efectivo'" x-transition x-cloak>
                <div class="info-alert">
                    El pago en <strong>efectivo</strong> se realiza directamente en el restaurante al momento de la reserva o llegada. Tu reserva quedará pendiente hasta confirmarlo.
                </div>
                <div class="form-group">
                    <label class="form-label">Nota (Opcional)</label>
                    <input type="text" name="notas" class="form-control" placeholder="Ej: Pagaré al llegar">
                </div>
            </div>

            <!-- Nequi -->
            <div x-show="method === 'nequi'" x-transition x-cloak>
                <div class="info-alert">
                    Realiza el pago a nuestro número Nequi: <strong>{{ $sucursal->telefono ?? '310 000 0000' }}</strong>.<br>Luego ingresa tu número y comprobante.
                </div>
                <div class="form-group">
                    <label class="form-label">Tu celular Nequi *</label>
                    <input type="tel" name="nequi_telefono" class="form-control" placeholder="300 000 0000" :required="method === 'nequi'">
                </div>
                <div class="form-group">
                    <label class="form-label">Comprobante (Opcional)</label>
                    <input type="text" name="referencia" class="form-control" placeholder="Número de transacción">
                </div>
            </div>

            <!-- Transferencia -->
            <div x-show="method === 'transferencia'" x-transition x-cloak>
                <div class="info-alert">
                    Transfiere a:<br>
                    <strong>Banco:</strong> {{ $sucursal->configuracion['banco'] ?? 'Bancolombia' }}<br>
                    <strong>Cuenta:</strong> {{ $sucursal->configuracion['cuenta_banco'] ?? '123456789' }}<br>
                    <strong>Titular:</strong> {{ $sucursal->nombre }}
                </div>
                <div class="form-group">
                    <label class="form-label">Referencia *</label>
                    <input type="text" name="referencia" class="form-control" placeholder="Número de comprobante" :required="method === 'transferencia'">
                </div>
            </div>

            <button type="submit" class="btn-pagar">Registrar pago →</button>
        </form>
    </div>
</div>

</body>
</html>
