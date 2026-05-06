<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reembolso procesado</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:#f3f4f6; font-family:'Helvetica Neue',Arial,sans-serif; color:#111827; padding:2rem 1rem; }
        .wrapper { max-width:520px; margin:0 auto; }
        .header  { background:#16a34a; border-radius:16px 16px 0 0; padding:2rem; text-align:center; color:#fff; }
        .header .icono { font-size:2.5rem; margin-bottom:0.6rem; }
        .header h1 { font-size:1.4rem; font-weight:700; }
        .header p  { font-size:0.9rem; opacity:0.85; margin-top:0.3rem; }
        .cuerpo { background:#fff; padding:1.8rem 2rem; }
        .monto-badge { background:#f0fdf4; color:#16a34a; font-size:1.6rem; font-weight:700; text-align:center; border-radius:12px; padding:1rem; margin-bottom:1.5rem; }
        table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
        td { padding:0.55rem 0; font-size:0.9rem; border-bottom:1px solid #f3f4f6; }
        td:first-child { color:#6b7280; }
        td:last-child  { text-align:right; font-weight:600; }
        .aviso { background:#f0f9ff; border-left:4px solid #0ea5e9; border-radius:8px; padding:0.9rem 1rem; font-size:0.875rem; color:#0c4a6e; margin-top:1.2rem; }
        .footer { background:#f9fafb; border-radius:0 0 16px 16px; padding:1.2rem 2rem; text-align:center; font-size:0.78rem; color:#9ca3af; border-top:1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <div class="icono">💰</div>
        <h1>Reembolso procesado</h1>
        <p>Tu devolución ha sido registrada correctamente.</p>
    </div>

    <div class="cuerpo">

        <div class="monto-badge">$<?php echo e(number_format($pago->monto, 2)); ?> COP</div>

        <table>
            <tr><td>Pedido</td>            <td>#<?php echo e($pedido->id); ?></td></tr>
            <tr><td>Referencia de pago</td><td><?php echo e($pago->referencia_transaccion ?? 'N/A'); ?></td></tr>
            <tr><td>Fecha de reembolso</td><td><?php echo e($pago->fecha_reembolso?->format('d/m/Y H:i')); ?></td></tr>
        </table>

        <div class="aviso">
            💡 El reembolso fue registrado en nuestro sistema. Si pagaste con Nequi,
            el valor se reflejará en tu cuenta en los próximos días hábiles según
            los tiempos de tu operador.
        </div>

    </div>

    <div class="footer">
        Este es un correo automático, no es necesario responder.
    </div>

</div>
</body>
</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/emails/reembolso.blade.php ENDPATH**/ ?>