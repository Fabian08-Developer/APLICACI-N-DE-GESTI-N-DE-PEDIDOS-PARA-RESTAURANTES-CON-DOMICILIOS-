<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago — Pedido #{{ $pedido->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite('resources/css/pago.css')
</head>

<body>
    <div class="contenedor">

        {{-- ── HEADER ── --}}
        <div class="header">
            <a href="{{ route('cliente.menu', ['t' => $token]) }}" class="btn-volver">← Volver</a>
            <h1>Pago</h1>
            <div style="width: 60px"></div>{{-- spacer para centrar el título --}}
        </div>

        {{-- ── RESUMEN DEL PEDIDO ── --}}
        <div class="tarjeta">
            <div class="tarjeta-header">Pedido #{{ $pedido->id }}</div>

            @foreach($pedido->detalles as $detalle)
            <div class="item-pedido">
                <span>
                    <span class="item-nombre">{{ $detalle->producto?->nombre }}</span>
                    <span class="item-cant"> &times;{{ $detalle->cantidad }}</span>
                </span>
                <span class="item-precio">${{ number_format($detalle->subtotal, 2) }}</span>
            </div>
            @endforeach

            <div class="total-row">
                <span>Total</span>
                <span>${{ number_format($pedido->total, 2) }}</span>
            </div>
        </div>

        {{-- ── MÉTODOS DE PAGO ── --}}
        <div class="tarjeta">
            <div class="tarjeta-header">Método de pago</div>

            <form method="POST" action="{{ route('cliente.pago.procesar') }}" id="form-pago" novalidate>
                @csrf
                <input type="hidden" name="_t" value="{{ $token }}">
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

                <div class="metodos">

                    {{-- EFECTIVO --}}
                    <label class="metodo-label {{ old('metodo_pago') === 'EFECTIVO' ? 'seleccionado' : '' }}">
                        <input type="radio" name="metodo_pago" value="EFECTIVO"
                            {{ old('metodo_pago') === 'EFECTIVO' ? 'checked' : '' }}
                            onchange="onMetodoCambiado(this)">
                        <span class="metodo-icono" aria-hidden="true">💵</span>
                        <div class="metodo-info">
                            <strong>Efectivo</strong>
                            <span>El mesero confirma el pago en tu mesa</span>
                        </div>
                    </label>

                    {{-- NEQUI --}}
                    <label class="metodo-label {{ old('metodo_pago') === 'NEQUI' ? 'seleccionado' : '' }}">
                        <input type="radio" name="metodo_pago" value="NEQUI"
                            {{ old('metodo_pago') === 'NEQUI' ? 'checked' : '' }}
                            onchange="onMetodoCambiado(this)">
                        <span class="metodo-icono" aria-hidden="true">📱</span>
                        <div class="metodo-info">
                            <strong>Nequi</strong>
                            <span>Recibirás una notificación en tu app</span>
                        </div>
                    </label>

                </div>

                @error('metodo_pago')
                <div class="error-campo" style="padding: 0 1.2rem">{{ $message }}</div>
                @enderror

                {{-- Nota efectivo --}}
                <div class="nota-efectivo" id="nota-efectivo"
                    style="{{ old('metodo_pago') === 'EFECTIVO' ? '' : 'display:none' }}">
                    Un mesero se acercará a tu mesa para confirmar el pago en efectivo.
                </div>

                <div class="campo-nequi" id="campo-nequi"
                    style="{{ old('metodo_pago') === 'NEQUI' ? '' : 'display:none' }}">

                    <label class="campo-label" for="telefono">Número de celular Nequi</label>
                    <div class="input-wrapper">
                        <span class="prefijo">🇨🇴 +57</span>
                        <input type="tel" id="telefono" name="telefono"
                            class="input-field {{ $errors->has('telefono') ? 'input-error' : '' }}"
                            placeholder="300 000 0000" maxlength="10"
                            inputmode="numeric" pattern="3[0-9]{9}"
                            value="{{ old('telefono') }}" autocomplete="tel-national">
                    </div>
                    @error('telefono')
                    <div class="error-campo">{{ $message }}</div>
                    @enderror

                    <label class="campo-label" for="email" style="margin-top: 1rem">
                        Correo electrónico
                    </label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email"
                            class="input-field {{ $errors->has('email') ? 'input-error' : '' }}"
                            placeholder="tucorreo@ejemplo.com"
                            value="{{ old('email') }}"
                            autocomplete="email" inputmode="email">
                    </div>
                    @error('email')
                    <div class="error-campo">{{ $message }}</div>
                    @enderror

                    <p style="font-size: 0.75rem; color: #888; margin-top: 0.6rem; line-height: 1.4">
                        Recibirás el comprobante de <strong>${{ number_format($pedido->total, 2) }}</strong>
                        en este correo al confirmarse el pago.
                    </p>

                </div>

                <div style="padding: 1rem 1.2rem 1.2rem">
                    <button type="submit" class="btn-pagar">Confirmar pago</button>
                </div>

            </form>
        </div>

    </div>

    <script>
        function onMetodoCambiado(input) {
            const notaEfectivo = document.getElementById('nota-efectivo');
            const campoNequi = document.getElementById('campo-nequi');
            const telInput = document.getElementById('telefono');
            const emailInput = document.getElementById('email');

            notaEfectivo.style.display = input.value === 'EFECTIVO' ? 'block' : 'none';
            campoNequi.style.display = input.value === 'NEQUI' ? 'block' : 'none';

            telInput.required = input.value === 'NEQUI';
            emailInput.required = input.value === 'NEQUI';

            document.querySelectorAll('.metodo-label').forEach(l => l.classList.remove('seleccionado'));
            input.closest('.metodo-label').classList.add('seleccionado');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const seleccionado = document.querySelector('input[name="metodo_pago"]:checked');
            if (seleccionado) onMetodoCambiado(seleccionado);
        });

        document.getElementById('telefono').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>

</html>