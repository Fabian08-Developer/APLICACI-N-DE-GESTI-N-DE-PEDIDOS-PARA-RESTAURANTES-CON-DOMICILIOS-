<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,500;0,600;1,300&family=DM+Mono:wght@300;400&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    @vite('resources/css/cerrar_sesion.css')
</head>

<body>
    <div class="tarjeta">
        <div class="tarjeta-franja" style="background: {{ $tienePendientes ? 'var(--amber)' : 'var(--rojo)' }}"></div>

        <div class="tarjeta-cuerpo">

            <span class="icono" aria-hidden="true">{{ $tienePendientes ? '⚠' : '👋' }}</span>

            <h1 class="titulo">
                {{ $tienePendientes ? 'Hay elementos pendientes' : 'Cerrar sesión' }}
            </h1>

            <p class="subtitulo">
                @if($tienePendientes)
                Revisa tus pedidos o pagos antes de salir. No podrás volver sin escanear el código QR nuevamente.
                @else
                ¿Confirmas que deseas cerrar tu sesión y desvincularte de esta mesa?
                @endif
            </p>

            @if(session('error'))
            <div class="alerta-error">{{ session('error') }}</div>
            @endif

            @if($tienePendientes)
            <div class="advertencia">
                <div class="advertencia-titulo">// elementos pendientes</div>
                @if($pedidosPendientes > 0)
                <div class="advertencia-item">
                    <span class="advertencia-dot"></span>
                    {{ $pedidosPendientes }} {{ $pedidosPendientes === 1 ? 'pedido aún en proceso' : 'pedidos aún en proceso' }}
                </div>
                @endif
                @if($pagosPendientes > 0)
                <div class="advertencia-item">
                    <span class="advertencia-dot"></span>
                    {{ $pagosPendientes }} {{ $pagosPendientes === 1 ? 'pago pendiente de confirmar' : 'pagos pendientes de confirmar' }}
                </div>
                @endif
            </div>
            @endif

            <div class="botones">
                <form method="POST" action="{{ route('cliente.cerrar.ejecutar') }}">
                    @csrf
                    <input type="hidden" name="_t" value="{{ $token }}">
                    <input type="hidden" name="sub_sesion_id" value="{{ $subSesionId }}">
                    <button type="submit" class="btn btn-cerrar"
                        {{ ($pedidosPendientes > 0) ? 'disabled' : '' }}>
                        <span>
                            @if($pedidosPendientes > 0)
                            Espera a que tus pedidos estén listos
                            @else
                            Sí, cerrar sesión
                            @endif
                        </span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>

                <a href="{{ route('cliente.menu', ['t' => $token]) }}" class="btn btn-volver">
                    <span>Volver al menú</span>
                    <span aria-hidden="true">←</span>
                </a>
            </div>
        </div>

        <div class="pie">// Mi Restaurante &middot; cierre de sesión</div>
    </div>
</body>

</html>