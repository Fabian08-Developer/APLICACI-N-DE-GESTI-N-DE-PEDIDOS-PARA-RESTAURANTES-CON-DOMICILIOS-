<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago confirmado</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f3f4f6;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #111827;
            padding: 2rem 1rem;
        }

        .wrapper {
            max-width: 520px;
            margin: 0 auto;
        }

        .header {
            background: #4f46e5;
            border-radius: 16px 16px 0 0;
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        .header .icono {
            font-size: 2.5rem;
            margin-bottom: 0.6rem;
        }

        .header h1 {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .header p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin-top: 0.3rem;
        }

        .cuerpo {
            background: #fff;
            padding: 1.8rem 2rem;
        }

        .monto-badge {
            background: #eef2ff;
            color: #4f46e5;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        td {
            padding: 0.55rem 0;
            font-size: 0.9rem;
            border-bottom: 1px solid #f3f4f6;
        }

        td:first-child {
            color: #6b7280;
        }

        td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .items-titulo {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9ca3af;
            margin-bottom: 0.6rem;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.88rem;
            padding: 0.4rem 0;
            border-bottom: 1px solid #f9fafb;
        }

        .aviso {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 8px;
            padding: 0.9rem 1rem;
            font-size: 0.875rem;
            color: #166534;
            margin-top: 1.5rem;
        }

        .footer {
            background: #f9fafb;
            border-radius: 0 0 16px 16px;
            padding: 1.2rem 2rem;
            text-align: center;
            font-size: 0.78rem;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <div class="header">
            <div class="icono">✅</div>
            <h1>¡Pago confirmado!</h1>
            <p>Tu pedido ya está en camino a la cocina.</p>
        </div>

        <div class="cuerpo">

            <div class="monto-badge">$<?php echo e(number_format($pago->monto, 2)); ?> COP</div>

            <table>
                <tr>
                    <td>Pedido</td>
                    <td>#<?php echo e($pago->pedido_id); ?></td>
                </tr>
                <tr>
                    <td>Método</td>
                    <td>Nequi</td>
                </tr>
                <tr>
                    <td>Número Nequi</td>
                    <td><?php echo e($pago->telefono); ?></td>
                </tr>
                <tr>
                    <td>Referencia</td>
                    <td style="font-family:monospace;font-size:0.8rem"><?php echo e($pago->referencia_transaccion); ?></td>
                </tr>
                <tr>
                    <td>Fecha</td>
                    <td><?php echo e($pago->updated_at->format('d/m/Y H:i')); ?></td>
                </tr>
            </table>

            <?php if($pago->pedido && $pago->pedido->detalles->isNotEmpty()): ?>
            <div class="items-titulo">Resumen del pedido</div>
            <?php $__currentLoopData = $pago->pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="item-row">
                <span><?php echo e($detalle->producto?->nombre); ?> <span style="color:#9ca3af">×<?php echo e($detalle->cantidad); ?></span></span>
                <span style="font-weight:500">$<?php echo e(number_format($detalle->subtotal, 2)); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <div class="aviso">
                🍽️ Tu pedido ya está siendo preparado. ¡Gracias por tu compra!
            </div>

        </div>

        <div class="footer">
            Este es un correo automático, no es necesario responder.
        </div>

    </div>
</body>

</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/emails/pago_aprobado.blade.php ENDPATH**/ ?>