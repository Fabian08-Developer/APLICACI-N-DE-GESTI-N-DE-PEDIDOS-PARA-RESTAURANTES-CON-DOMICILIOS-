<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Pago — SGPD</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/pago.css'])
</head>
<body>
    <div class="contenedor">
        <div class="header">
            <a href="{{ route('cliente.menu', ['t' => $sesion->token]) }}" class="btn-volver">← Menú</a>
            <h1>Finalizar Pago</h1>
        </div>

        @if(session('error'))
            <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.8rem; margin-bottom:1rem;">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div style="background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.8rem; margin-bottom:1rem;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Desglose del pedido (RF-C24) --}}
        <div class="tarjeta">
            <div class="tarjeta-header">Resumen de tu Pedido</div>
            @foreach($pedido->detalles as $detalle)
                <div class="item-pedido">
                    <div>
                        <span class="item-nombre">{{ $detalle->nombre_producto }}</span>
                        <span class="item-cant">x{{ $detalle->cantidad }}</span>
                    </div>
                    <span class="item-precio">${{ number_format($detalle->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
            
            <div class="item-pedido">
                <span class="item-nombre" style="color: var(--text-muted);">Subtotal</span>
                <span class="item-precio" style="color: var(--text-muted); font-weight: normal;">${{ number_format($pedido->subtotal, 0, ',', '.') }}</span>
            </div>

            @if($pedido->tipo === 'domicilio')
                <div class="item-pedido">
                    <span class="item-nombre" style="color: var(--text-muted);">Costo de Envío (Domicilio)</span>
                    <span class="item-precio" style="color: var(--text-muted); font-weight: normal;">${{ number_format($pedido->costo_envio, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="total-row">
                <span>Total</span>
                <span>${{ number_format($pedido->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Formulario de selección y procesamiento (RF-C25) --}}
        <form action="{{ route('cliente.pago.procesar', ['t' => $sesion->token]) }}" method="POST" id="form-pago">
            @csrf
            
            <div class="tarjeta">
                <div class="tarjeta-header">Método de Pago</div>
                
                <div class="metodos">
                    {{-- Nequi option --}}
                    @if(!$nequiBloqueado)
                        <label class="metodo-label seleccionado" id="label-nequi">
                            <input type="radio" name="metodo" value="Nequi" id="radio-nequi" checked>
                            <span class="metodo-icono">📱</span>
                            <div class="metodo-info">
                                <strong>Nequi</strong>
                                <span>Pago digital instantáneo</span>
                            </div>
                        </label>
                    @else
                        <label class="metodo-label" style="opacity: 0.5; cursor: not-allowed;" id="label-nequi">
                            <input type="radio" name="metodo" value="Nequi" id="radio-nequi" disabled>
                            <span class="metodo-icono">📱</span>
                            <div class="metodo-info">
                                <strong>Nequi (Bloqueado)</strong>
                                <span>Superado límite de intentos</span>
                            </div>
                        </label>
                    @endif

                    {{-- Efectivo option --}}
                    <label class="metodo-label {{ $nequiBloqueado ? 'seleccionado' : '' }}" id="label-efectivo">
                        <input type="radio" name="metodo" value="Efectivo" id="radio-efectivo" {{ $nequiBloqueado ? 'checked' : '' }}>
                        <span class="metodo-icono">💵</span>
                        <div class="metodo-info">
                            <strong>Efectivo</strong>
                            @if($pedido->tipo === 'local')
                                <span>Paga directamente al mesero</span>
                            @else
                                <span>Paga al recibir en tu domicilio</span>
                            @endif
                        </div>
                    </label>
                </div>

                {{-- Campo Nequi (se muestra si se selecciona nequi) --}}
                <div class="campo-nequi" id="seccion-nequi" style="{{ $nequiBloqueado ? 'display: none;' : '' }}">
                    <div style="margin-bottom: 0.8rem;">
                        <label class="campo-label">Número celular Nequi</label>
                        <div class="input-wrapper">
                            <span class="prefijo">+57</span>
                            <input type="text" name="nequi_telefono" id="nequi_telefono" class="input-field" placeholder="3001234567" maxlength="10" value="{{ old('nequi_telefono') }}">
                        </div>
                        <div class="error-campo" id="error-telefono"></div>
                        @error('nequi_telefono')
                            <div class="error-campo">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="campo-label">Correo electrónico</label>
                        <div class="input-wrapper">
                            <input type="email" name="nequi_correo" id="nequi_correo" class="input-field" placeholder="tu@correo.com" value="{{ old('nequi_correo') }}">
                        </div>
                        <div class="error-campo" id="error-correo"></div>
                        @error('nequi_correo')
                            <div class="error-campo">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Nota Efectivo --}}
                <div class="nota-efectivo" id="seccion-efectivo" style="{{ !$nequiBloqueado ? 'display: none;' : '' }}">
                    @if($pedido->tipo === 'local')
                        <span><strong>Nota:</strong> Tu pedido quedará registrado. Por favor, solicita al mesero que confirme el pago de tu cuenta para enviar tu orden a cocina.</span>
                    @else
                        <span><strong>Nota:</strong> El domiciliario cobrará el monto total en efectivo al momento de la entrega de tu pedido.</span>
                    @endif
                </div>
            </div>

            <button type="submit" class="btn-pagar" id="btn-pagar-submit">
                @if(!$nequiBloqueado)
                    Proceder al Pago
                @else
                    Confirmar Pedido en Efectivo
                @endif
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioNequi = document.getElementById('radio-nequi');
            const radioEfectivo = document.getElementById('radio-efectivo');
            const labelNequi = document.getElementById('label-nequi');
            const labelEfectivo = document.getElementById('label-efectivo');
            const seccionNequi = document.getElementById('seccion-nequi');
            const seccionEfectivo = document.getElementById('seccion-efectivo');
            const btnPagar = document.getElementById('btn-pagar-submit');

            const nequiTelefono = document.getElementById('nequi_telefono');
            const nequiCorreo = document.getElementById('nequi_correo');
            const errorTelefono = document.getElementById('error-telefono');
            const errorCorreo = document.getElementById('error-correo');
            const form = document.getElementById('form-pago');

            function alternarMetodo() {
                if (radioNequi && radioNequi.checked) {
                    labelNequi.classList.add('seleccionado');
                    labelEfectivo.classList.remove('seleccionado');
                    seccionNequi.style.display = 'block';
                    seccionEfectivo.style.display = 'none';
                    btnPagar.textContent = 'Proceder al Pago';
                } else {
                    labelEfectivo.classList.add('seleccionado');
                    if (labelNequi) labelNequi.classList.remove('seleccionado');
                    seccionNequi.style.display = 'none';
                    seccionEfectivo.style.display = 'block';
                    btnPagar.textContent = 'Confirmar Pedido en Efectivo';
                }
            }

            if (radioNequi) radioNequi.addEventListener('change', alternarMetodo);
            if (radioEfectivo) radioEfectivo.addEventListener('change', alternarMetodo);

            // Validaciones en caliente del lado del cliente (RF-C28)
            form.addEventListener('submit', function(e) {
                let valid = true;

                if (radioNequi && radioNequi.checked) {
                    // Validar celular Nequi
                    const telefono = nequiTelefono.value.trim();
                    const regexTel = /^3\d{9}$/;
                    if (!regexTel.test(telefono)) {
                        errorTelefono.textContent = 'El número celular de Nequi debe ser de 10 dígitos y comenzar con 3.';
                        valid = false;
                    } else {
                        errorTelefono.textContent = '';
                    }

                    // Validar correo
                    const correo = nequiCorreo.value.trim();
                    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regexCorreo.test(correo)) {
                        errorCorreo.textContent = 'Debes ingresar un correo electrónico válido.';
                        valid = false;
                    } else {
                        errorCorreo.textContent = '';
                    }
                }

                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
