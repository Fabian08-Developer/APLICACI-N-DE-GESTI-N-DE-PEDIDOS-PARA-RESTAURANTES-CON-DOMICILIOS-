<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido cancelado</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite('resources/css/canceelacion_exitosa.css')
</head>

<body>
    <div class="contenedor">

        <div class="icono-cancelado" aria-hidden="true">✕</div>
        <h1 style="text-align: center">Pedido cancelado</h1>
        <p class="subtexto" style="text-align: center">
            Tu pedido fue cancelado correctamente.
            @if($emailEnviado)
            Te enviamos un comprobante al correo.
            @endif
        </p>

        {{-- ── DETALLE ── --}}
        <div class="cancelacion-card">
            <div class="cancelacion-header">Detalle de la cancelación</div>

            <div class="cancelacion-row">
                <span class="label">Pedido</span>
                <span class="valor">#{{ $pedido->id }}</span>
            </div>
            <div class="cancelacion-row">
                <span class="label">Total cancelado</span>
                <span class="valor">${{ number_format($pedido->total, 2) }}</span>
            </div>
            <div class="cancelacion-row">
                <span class="label">Fecha</span>
                <span class="valor">{{ $pedido->fecha_cancelacion?->format('d/m/Y H:i') }}</span>
            </div>

            @if($pedido->motivo_cancelacion)
            <div class="motivo-badge">
                <strong>Motivo:</strong> {{ $pedido->motivo_cancelacion }}
            </div>
            @endif
        </div>

        {{-- ── AVISO REEMBOLSO ── --}}
        @if($teniaPagoAprobado)
        <div class="aviso-reembolso">
            Tu pago fue procesado mediante Nequi. El reembolso se gestionará en los próximos días hábiles y recibirás una notificación cuando se complete.
        </div>
        @else
        <div class="aviso-ok">
            No se realizó ningún cobro a tu cuenta. No necesitas hacer nada más.
        </div>
        @endif

        {{-- ── PRODUCTOS CANCELADOS ── --}}
        <div class="tarjeta">
            <div class="tarjeta-header">
                <span>Productos cancelados</span>
                <span class="pedido-num">#{{ $pedido->id }}</span>
            </div>

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

        <a href="{{ route('cliente.menu', ['t' => $token]) }}" class="btn-nuevo-pedido"> + Hacer un nuevo pedido</a>

    </div>
</body>

</html>