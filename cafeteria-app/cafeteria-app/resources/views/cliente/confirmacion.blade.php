<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Pedido — SGPD</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/confirmacion.css'])
</head>
<body>
    <div class="contenedor">
        @if($pedido->estado !== 'CANCELADO')
            <div class="icono-exito">✓</div>
            <h1>¡Pedido Confirmado!</h1>
            <p class="subtexto">Tu pedido ha sido recibido y está en proceso.</p>
        @else
            <div class="icono-exito" style="background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.3); color:#ef4444;">✕</div>
            <h1>Pedido Cancelado</h1>
            <p class="subtexto">Este pedido ha sido cancelado.</p>
        @endif

        @if(session('error'))
            <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.8rem; margin-bottom:1rem; text-align:center;">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div style="background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:0.75rem 1rem; border-radius:0.75rem; font-size:0.8rem; margin-bottom:1rem; text-align:center;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tracker Card --}}
        <div class="tracker-card">
            @php
                $estados = ['CREADO', 'EN_PREPARACION', 'LISTO', 'ENTREGADO'];
                $estadoActual = $pedido->estado;
                $currentIndex = array_search($estadoActual, $estados);
                
                // Si no se encuentra (ej. CANCELADO o PENDIENTE_PAGO)
                if ($currentIndex === false) {
                    $currentIndex = -1;
                }
                
                // Calcular ancho de barra
                $ancho = 0;
                if ($estadoActual === 'CANCELADO') {
                    $ancho = 100;
                } elseif ($currentIndex >= 0) {
                    $ancho = $currentIndex * 33.33; // 3 intervalos: 0%, 33%, 66%, 100%
                }
            @endphp

            <div class="tracker-barra-wrap">
                <div class="tracker-barra-bg">
                    <div class="tracker-barra-fill {{ $estadoActual === 'CANCELADO' ? 'rojo' : ($estadoActual === 'ENTREGADO' ? 'verde' : '') }}" style="width: {{ $ancho }}%;"></div>
                </div>
            </div>

            <div class="tracker-pasos">
                @foreach($estados as $index => $est)
                    @php
                        $clase = '';
                        if ($estadoActual === 'CANCELADO') {
                            $clase = '';
                        } elseif ($index < $currentIndex) {
                            $clase = 'completado';
                        } elseif ($index === $currentIndex) {
                            $clase = 'activo';
                        }
                        
                        $iconos = [
                            'CREADO' => '📝',
                            'EN_PREPARACION' => '👨‍🍳',
                            'LISTO' => '🛎️',
                            'ENTREGADO' => '🎁'
                        ];
                        
                        $labels = [
                            'CREADO' => 'Nuevo',
                            'EN_PREPARACION' => 'Preparando',
                            'LISTO' => 'Listo',
                            'ENTREGADO' => 'Entregado'
                        ];
                    @endphp
                    <div class="tracker-paso {{ $clase }}" id="paso-{{ $est }}">
                        <div class="paso-circulo">
                            <span class="paso-check">✓</span>
                            <span class="paso-pulso"></span>
                            <span style="font-size:12px; z-index:1; {{ $clase === 'completado' ? 'display:none;' : '' }}">{{ $iconos[$est] }}</span>
                        </div>
                        <span class="paso-label">{{ $labels[$est] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Mensaje de estado --}}
            @if($estadoActual === 'CANCELADO')
                <div class="tracker-mensaje cancelado">
                    <span class="tracker-dot rojo"></span>
                    <span>Pedido Cancelado: {{ $pedido->motivo_cancelacion ?? 'Cancelado por el cliente.' }}</span>
                </div>
            @elseif($estadoActual === 'ENTREGADO')
                <div class="tracker-mensaje entregado">
                    <span class="tracker-dot verde"></span>
                    <span>Pedido Entregado. ¡Que disfrutes tu comida!</span>
                </div>
            @else
                @php
                    $mensajes = [
                        'CREADO' => 'Tu pedido ha sido recibido y está en cola de espera.',
                        'EN_PREPARACION' => 'El chef está preparando tus deliciosos platillos.',
                        'LISTO' => '¡Tu pedido está listo! El mesero te lo llevará en breve.',
                    ];
                @endphp
                <div class="tracker-mensaje">
                    <span class="tracker-dot"></span>
                    <span>{{ $mensajes[$estadoActual] ?? 'Procesando tu orden...' }}</span>
                </div>
            @endif
        </div>

        {{-- Resumen de Pedido --}}
        <div class="tarjeta">
            <div class="tarjeta-header">
                <span>Orden #{{ $pedido->id }}</span>
                <span>{{ $pedido->created_at?->format('g:i A') }}</span>
            </div>
            
            @foreach($pedido->detalles as $detalle)
                <div class="item-pedido">
                    <span>{{ $detalle->nombre_producto }} x{{ $detalle->cantidad }}</span>
                    <span class="item-precio">${{ number_format($detalle->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach

            @if($pedido->tipo === 'domicilio')
                <div class="item-pedido">
                    <span style="color: var(--text-muted);">Envío Domicilio</span>
                    <span class="item-precio" style="color: var(--text-muted); font-weight: normal;">${{ number_format($pedido->costo_envio, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="total-row">
                <span>Total</span>
                <span>${{ number_format($pedido->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Cancelar Pedido (RF-C34 / RF-C35 / RF-C36: Permitir cancelar si está en CREADO) --}}
        @if($pedido->estado === 'CREADO')
            <button class="btn-cancelar" onclick="abrirModal()">Cancelar Pedido</button>
        @endif

        <a href="{{ route('cliente.menu', ['t' => $sesion->token]) }}" class="btn-volver-menu">Volver al Menú</a>
    </div>

    {{-- Modal de Cancelación --}}
    <div class="modal-overlay" id="modal-cancelar">
        <div class="modal-box">
            <h3 class="modal-titulo">¿Cancelar tu pedido?</h3>
            <p class="modal-desc">Si cancelas tu pedido y ya realizaste el pago, se procesará un reembolso automático y te llegará una notificación por correo.</p>
            
            <form action="{{ route('cliente.pedido.cancelar', ['pedidoId' => $pedido->id, 't' => $sesion->token]) }}" method="POST">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label class="modal-label">Motivo de la cancelación</label>
                    <textarea name="motivo" class="modal-textarea" rows="3" placeholder="Por ejemplo: Deseo cambiar mi orden, me equivoqué de plato..."></textarea>
                </div>

                <div class="modal-acciones">
                    <button type="button" class="btn-modal-volver" onclick="cerrarModal()">Volver</button>
                    <button type="submit" class="btn-modal-confirmar">Confirmar Cancelación</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modal-cancelar').classList.add('visible');
        }
        function cerrarModal() {
            document.getElementById('modal-cancelar').classList.remove('visible');
        }

        // Polling para actualizar en vivo el estado del pedido
        @if(!in_array($pedido->estado, ['ENTREGADO', 'CANCELADO']))
            setTimeout(function() {
                location.reload();
            }, 10000); // Recargar cada 10 segundos para ver actualizaciones en vivo
        @endif
    </script>
</body>
</html>
