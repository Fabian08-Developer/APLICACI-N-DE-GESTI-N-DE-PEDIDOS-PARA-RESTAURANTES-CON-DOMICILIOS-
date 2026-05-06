<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmando pago…</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/pago_pendiente.css']); ?>
</head>

<body>
    <div class="contenedor">

        <div class="header">
            <h1>Nequi</h1>
        </div>

        
        <div id="pantalla-pendiente" class="tarjeta">
            <div class="pantalla">
                <span class="icono" aria-hidden="true">📱</span>
                <h2>Revisa tu app Nequi</h2>
                <p>Enviamos una notificación al número</p>
                <p><strong><?php echo e($pago->telefono); ?></strong></p>
                <p style="margin-top: .75rem">
                    Acepta el cobro de <strong>$<?php echo e(number_format($pedido->total, 2)); ?></strong> desde tu app.
                </p>
                <div class="spinner" id="spinner" aria-hidden="true"></div>
                <p id="texto-estado" style="font-size: .83rem">Esperando confirmación…</p>
                <span class="ref-badge">Ref: <?php echo e($pago->referencia_transaccion); ?></span>
            </div>

            <div class="barra-tiempo-wrap">
                <div class="barra-tiempo-bg">
                    <div class="barra-tiempo-fill" id="barra-tiempo" style="width: 100%"></div>
                </div>
                <div class="barra-tiempo-label">
                    <span id="seg-restantes"><?php echo e($timeout); ?></span>s restantes
                </div>
            </div>

            <div class="aviso-conexion" id="aviso-conexion">
                Problemas de conexión. Seguimos verificando el estado de tu pago…
            </div>
        </div>

        
        <div id="pantalla-aprobado" class="tarjeta" style="display: none">
            <div class="pantalla">
                <span class="icono" aria-hidden="true">✓</span>
                <h2>Pago aprobado</h2>
                <p>Tu pedido fue enviado a cocina.</p>
                <p style="font-size: .8rem; margin-top: .45rem; color: #9ca3af">Redirigiendo en un momento…</p>
            </div>
        </div>

        
        <div id="pantalla-fallido" class="tarjeta" style="display: none">
            <div class="pantalla">
                <span class="icono" aria-hidden="true">✕</span>
                <h2>Pago no confirmado</h2>
                <p>No recibimos la confirmación de Nequi. Puedes intentarlo de nuevo.</p>
            </div>
            <a href="<?php echo e(route('cliente.pago', ['t' => $token, 'pedido_id' => $pedido->id])); ?>" class="btn-reintentar">Intentar de nuevo</a>
        </div>

        
        <div id="pantalla-timeout" class="tarjeta" style="display: none">
            <div class="pantalla">
                <span class="icono" aria-hidden="true">⏱</span>
                <h2>Tiempo agotado</h2>
                <p>No recibimos respuesta en el tiempo esperado.</p>
                <p style="font-size: .83rem; margin-top: .4rem">
                    Si ya aceptaste el cobro en Nequi, el pago de todas formas se procesará.
                </p>
            </div>
            <a href="<?php echo e(route('cliente.pago', ['t' => $token, 'pedido_id' => $pedido->id])); ?>" class="btn-reintentar">Volver a intentar</a>
        </div>

        
        <div id="pantalla-sin-conexion" class="tarjeta" style="display: none">
            <div class="pantalla">
                <span class="icono" aria-hidden="true">📡</span>
                <h2>Sin conexión</h2>
                <p>No podemos verificar el estado de tu pago. Revisa tu conexión a internet.</p>
            </div>
            <a href="<?php echo e(route('cliente.pago.pendiente', [$pago->id, 't' => $token])); ?>" class="btn-reintentar">
                Recargar página
            </a>
        </div>

        
        <?php if(app()->environment('local', 'testing')): ?>
        <div class="dev-tools">
            <p>Herramientas de simulación — solo visible en entornos locales</p>
            <button class="btn-sim-ok" onclick="simularEstado('COMPLETADO')">Simular aprobado</button>
            <button class="btn-sim-fail" onclick="simularEstado('FALLIDO')">Simular fallido</button>
        </div>
        <?php endif; ?>

    </div>

    <script>
        const PAGO_ID     = <?php echo e($pago->id); ?>;
        const TIMEOUT_SEG = <?php echo e($timeout); ?>;
        const RUTA_ESTADO    = "<?php echo route('cliente.pago.estado', [$pago->id, 't' => $token]); ?>";
        const RUTA_CONFIRMAR = "<?php echo route('cliente.confirmacion', ['t' => $token, 'pedido_id' => $pedido->id]); ?>";
        const RUTA_SIMULAR   = "<?php echo route('cliente.pago.simular', [$pago->id, 't' => $token]); ?>";
        const CSRF = "<?php echo e(csrf_token()); ?>";

        const INTERVALO_INICIAL_MS = 5000;
        const MAX_INTERVALO_MS = 60000;
        const MAX_FALLOS = 5;

        let timeoutPolling;
        let intervaloTiempo;
        let finalizado = false;
        let fallosConsecutivos = 0;
        let intervaloActualMs = INTERVALO_INICIAL_MS;

        const endTimeKey = `pago_end_${PAGO_ID}`;
        let endTime = sessionStorage.getItem(endTimeKey);
        if (!endTime) {
            endTime = Date.now() + (TIMEOUT_SEG * 1000);
            sessionStorage.setItem(endTimeKey, endTime);
        }
        let segRestantes = Math.max(0, Math.floor((endTime - Date.now()) / 1000));

        function mostrarPantalla(id) {
            ['pantalla-pendiente', 'pantalla-aprobado', 'pantalla-fallido',
                'pantalla-timeout', 'pantalla-sin-conexion'
            ]
            .forEach(p => document.getElementById(p).style.display = p === id ? 'block' : 'none');
        }

        function consultarEstado() {
            if (finalizado) return;

            fetch(RUTA_ESTADO, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    fallosConsecutivos = 0;
                    intervaloActualMs = INTERVALO_INICIAL_MS;

                    document.getElementById('aviso-conexion').style.display = 'none';
                    document.getElementById('texto-estado').textContent = 'Esperando confirmación…';

                    procesarEstado(data.estado);
                    if (!finalizado) timeoutPolling = setTimeout(consultarEstado, intervaloActualMs);
                })
                .catch(() => {
                    fallosConsecutivos++;

                    if (fallosConsecutivos >= MAX_FALLOS) {
                        finalizado = true;
                        detenerTimers();
                        mostrarPantalla('pantalla-sin-conexion');
                        return;
                    }

                    intervaloActualMs = Math.min(intervaloActualMs * 2, MAX_INTERVALO_MS);

                    document.getElementById('aviso-conexion').style.display = 'block';
                    document.getElementById('texto-estado').textContent =
                        `Sin conexión. Reintentando en ${Math.round(intervaloActualMs / 1000)}s… (${fallosConsecutivos}/${MAX_FALLOS})`;

                    timeoutPolling = setTimeout(consultarEstado, intervaloActualMs);
                });
        }

        function procesarEstado(estado) {
            if (finalizado) return;

            if (estado === 'COMPLETADO') {
                finalizado = true;
                detenerTimers();
                mostrarPantalla('pantalla-aprobado');
                setTimeout(() => window.location.href = RUTA_CONFIRMAR, 1800);
            } else if (estado === 'FALLIDO') {
                finalizado = true;
                detenerTimers();
                mostrarPantalla('pantalla-fallido');
            }
        }

        function tickTiempo() {
            if (finalizado) return;
            
            segRestantes = Math.max(0, Math.floor((sessionStorage.getItem(endTimeKey) - Date.now()) / 1000));
            const porcentaje = Math.max(0, (segRestantes / TIMEOUT_SEG) * 100);
            document.getElementById('barra-tiempo').style.width = porcentaje + '%';
            document.getElementById('seg-restantes').textContent = segRestantes;

            if (segRestantes <= 0) {
                finalizado = true;
                detenerTimers();
                mostrarPantalla('pantalla-timeout');
            }
        }

        function detenerTimers() {
            clearTimeout(timeoutPolling);
            clearInterval(intervaloTiempo);
            const spinner = document.getElementById('spinner');
            if (spinner) spinner.style.display = 'none';
        }

        function simularEstado(estado) {
            fetch(RUTA_SIMULAR, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        estado
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) setTimeout(consultarEstado, 1500);
                })
                .catch(err => console.error('Error en simulación:', err));
        }

        document.addEventListener('DOMContentLoaded', function() {
            timeoutPolling = setTimeout(consultarEstado, 3000);
            intervaloTiempo = setInterval(tickTiempo, 1000);
        });
    </script>
</body>

</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cliente/pago_pendiente.blade.php ENDPATH**/ ?>