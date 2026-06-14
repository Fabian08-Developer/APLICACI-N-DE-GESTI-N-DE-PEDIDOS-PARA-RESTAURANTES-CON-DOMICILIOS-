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
                    $clase='completado' ;
                    } elseif ($index===$currentIndex) {
                    $clase='activo' ;
                    }

                    $labels=[ 'CREADO'=> 'Nuevo',
                    'EN_PREPARACION' => 'Preparando',
                    'LISTO' => 'Listo',
                    'ENTREGADO' => 'Entregado'
                    ];
                    @endphp
                    <div class="tracker-paso {{ $clase }}" id="paso-{{ $est }}">
                        <div class="paso-circulo">
                            <span class="paso-check">✓</span>
                            <span class="paso-pulso"></span>
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

        {{-- Formulario de Calificación si es domicilio, entregado y sin calificar --}}
        @if($pedido->estado === 'ENTREGADO' && $pedido->tipo === 'domicilio' && !$pedido->calificacionDomiciliario)
        <div class="tarjeta" style="margin-bottom: 1rem; padding: 1.5rem 1.25rem; text-align: center; background: linear-gradient(180deg, #ffffff 0%, #fdfdfc 100%);">
            <div style="width: 48px; height: 48px; background: rgba(196, 139, 87, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; color: var(--primary); font-size: 1.5rem;">
                ⭐
            </div>
            <h3 style="font-family: 'DM Serif Display', serif; font-size: 1.3rem; color: var(--text-main); margin-bottom: 0.25rem;">Califica tu entrega</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem;">¿Qué tal fue el servicio de nuestro domiciliario?</p>

            <form id="form-calificacion" onsubmit="enviarCalificacion(event)">
                @csrf
                <input type="hidden" name="puntuacion" id="puntuacion-input" value="0">
                <div class="estrellas-container" style="display: flex; justify-content: center; gap: 0.4rem; margin-bottom: 1.25rem;">
                    <span class="estrella-btn" data-valor="1">★</span>
                    <span class="estrella-btn" data-valor="2">★</span>
                    <span class="estrella-btn" data-valor="3">★</span>
                    <span class="estrella-btn" data-valor="4">★</span>
                    <span class="estrella-btn" data-valor="5">★</span>
                </div>
                <textarea name="comentario" rows="2" placeholder="Opcional: Deja un comentario sobre el servicio..." style="width: 100%; border: 1.5px solid var(--border); border-radius: 0.75rem; padding: 0.75rem; font-family: inherit; font-size: 0.85rem; margin-bottom: 1rem; resize: none; background: #fafafa; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"></textarea>

                <div id="error-calificacion" style="color: var(--error); font-size: 0.8rem; margin-bottom: 1rem; display: none; font-weight: 500;">Por favor, selecciona una puntuación.</div>

                <button type="submit" style="width: 100%; padding: 0.8rem; background: var(--primary); color: white; border: none; border-radius: 0.75rem; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(196, 139, 87, 0.2);">Enviar Calificación</button>
            </form>
            <div id="mensaje-exito" style="display: none; color: var(--success); font-weight: 600; font-size: 0.9rem; padding: 1rem; background: rgba(16, 185, 129, 0.1); border-radius: 0.75rem; margin-top: 0.5rem;">
                ¡Gracias por tu calificación! ⭐
            </div>
        </div>

        <style>
            .estrella-btn {
                font-size: 2.2rem;
                color: #e5e7eb;
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                display: inline-block;
            }

            .estrella-btn:hover {
                transform: scale(1.15);
            }

            .estrella-btn.activa {
                color: #f59e0b;
            }
        </style>
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

        document.addEventListener('DOMContentLoaded', () => {
            const estrellas = document.querySelectorAll('.estrella-btn');
            const inputPuntuacion = document.getElementById('puntuacion-input');

            estrellas.forEach(estrella => {
                estrella.addEventListener('click', function() {
                    const valor = this.getAttribute('data-valor');
                    inputPuntuacion.value = valor;

                    estrellas.forEach(e => {
                        if (parseInt(e.getAttribute('data-valor')) <= parseInt(valor)) {
                            e.classList.add('activa');
                        } else {
                            e.classList.remove('activa');
                        }
                    });
                });

                // Efecto hover interactivo
                estrella.addEventListener('mouseenter', function() {
                    const valor = this.getAttribute('data-valor');
                    estrellas.forEach(e => {
                        if (parseInt(e.getAttribute('data-valor')) <= parseInt(valor)) {
                            e.style.color = '#fbbf24';
                        }
                    });
                });

                estrella.addEventListener('mouseleave', function() {
                    const valorGuardado = inputPuntuacion.value;
                    estrellas.forEach(e => {
                        e.style.color = '';
                        if (parseInt(e.getAttribute('data-valor')) <= parseInt(valorGuardado)) {
                            e.classList.add('activa');
                        } else {
                            e.classList.remove('activa');
                        }
                    });
                });
            });
        });

        function enviarCalificacion(e) {
            e.preventDefault();
            const form = e.target;
            const puntuacion = document.getElementById('puntuacion-input').value;
            const errorDiv = document.getElementById('error-calificacion');

            if (puntuacion == 0) {
                errorDiv.style.display = 'block';
                return;
            }
            errorDiv.style.display = 'none';

            const formData = new FormData(form);

            fetch("{{ route('cliente.pedido.calificar', ['pedidoId' => $pedido->id, 't' => $sesion->token]) }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(async res => {
                    if (!res.ok) {
                        const text = await res.text();
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.error || json.message || 'Error del servidor');
                        } catch (e) {
                            throw new Error('Error ' + res.status + ': ' + text.substring(0, 50));
                        }
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        form.style.display = 'none';
                        document.getElementById('mensaje-exito').style.display = 'block';
                    } else {
                        errorDiv.innerText = data.error || 'Ocurrió un error. Inténtalo de nuevo.';
                        errorDiv.style.display = 'block';
                    }
                })
                .catch(err => {
                    errorDiv.innerText = err.message || 'Ocurrió un error de conexión.';
                    errorDiv.style.display = 'block';
                });
        }

        // Polling para actualizar en vivo el estado del pedido
        @if(!in_array($pedido - > estado, ['ENTREGADO', 'CANCELADO']))
        setTimeout(function() {
            location.reload();
        }, 10000); // Recargar cada 10 segundos para ver actualizaciones en vivo
        @endif
    </script>
</body>

</html>