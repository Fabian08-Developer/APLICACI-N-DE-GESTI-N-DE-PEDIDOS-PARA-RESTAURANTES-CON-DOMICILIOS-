<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Pago — SGPD</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/pago_pendiente.css'])
    <style>
        body {
            background: #F9F7F3;
            color: #2C2621;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }
    </style>
</head>
<body>
    <div style="width: 100%; max-width: 400px; margin: 0 auto;">
        <div class="header">
            <h1>SGPD</h1>
        </div>

        <div class="pantalla">
            <span class="icono">🔒</span>
            <h2>Procesando tu Pago</h2>
            
            @if($pago->metodo === 'Nequi')
                <p>Por favor, revisa tu celular Nequi para autorizar la notificación de cobro.</p>
                <p>No cierres ni refresques esta página.</p>
            @else
                @if($pago->pedido?->tipo === 'local')
                    <p>Por favor, espera a que el mesero confirme la recepción del pago en efectivo.</p>
                @else
                    <p>Tu orden ha sido registrada. Puedes ver el estado de preparación.</p>
                @endif
            @endif

            <div class="spinner"></div>

            <span class="ref-badge">Ref: {{ $pago->referencia }}</span>
        </div>

        {{-- Barra de tiempo (10 segundos para nequi/polling visual) --}}
        <div class="barra-tiempo-wrap">
            <div class="barra-tiempo-bg">
                <div class="barra-tiempo-fill" id="progress-bar" style="width: 0%;"></div>
            </div>
            <div class="barra-tiempo-label" id="timer-label">Conectando...</div>
        </div>

        <div class="aviso-conexion" id="aviso-conexion">
            Estamos teniendo dificultades para conectar con el servidor, reintentando...
        </div>

        {{-- Simulador de Pago (RF-C29 & RF-C30) --}}
        @if($pago->metodo === 'Nequi')
            <div class="dev-tools">
                <p>⚡ <strong>Herramientas de Simulación (Pruebas)</strong></p>
                <form action="{{ route('cliente.pago.simular', ['pagoId' => $pago->id, 't' => $sesion->token]) }}" method="POST" id="form-simular">
                    @csrf
                    <input type="hidden" name="resultado" id="resultado-simulacion" value="">
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center;">
                        <button type="button" class="btn-sim-ok" onclick="simular('approved')">Aprobar Pago</button>
                        <button type="button" class="btn-sim-fail" onclick="simular('declined')">Rechazar Pago</button>
                        <button type="button" style="background:#fee2e2; color:#9a3412; border:none; border-radius:7px; padding:0.45rem 0.9rem; font-size:0.82rem; font-weight:600; cursor:pointer;" onclick="simular('timeout')">Simular Timeout</button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <script>
        function simular(resultado) {
            document.getElementById('resultado-simulacion').value = resultado;
            document.getElementById('form-simular').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Polling de estado de pago (RF-C34)
            const urlEstado = '{{ route("cliente.pago.estado", ["pagoId" => $pago->id, "t" => $sesion->token]) }}';
            
            let progressBar = document.getElementById('progress-bar');
            let timerLabel = document.getElementById('timer-label');
            let avisoConexion = document.getElementById('aviso-conexion');
            let progress = 0;

            // Simulación visual de barra de carga
            let progressInterval = setInterval(() => {
                progress += 5;
                if (progress <= 100) {
                    progressBar.style.width = progress + '%';
                    timerLabel.textContent = `Procesando (${Math.ceil(progress / 10)}s)...`;
                } else {
                    progress = 0; // reset loop just for visual
                }
            }, 500);

            async function checkStatus() {
                try {
                    const response = await fetch(urlEstado, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    
                    if (data.resolved) {
                        clearInterval(progressInterval);
                        window.location.href = data.redirigir;
                    }
                    
                    if (avisoConexion) avisoConexion.style.display = 'none';
                } catch (error) {
                    console.error('Error polling status:', error);
                    if (avisoConexion) avisoConexion.style.display = 'block';
                }
            }

            // Realizar polling cada 3 segundos
            let pollingInterval = setInterval(checkStatus, 3000);
            checkStatus(); // Primera verificación inmediata
        });
    </script>
</body>
</html>
