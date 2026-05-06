<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago — Pedido #<?php echo e($pedido->id); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/pago.css'); ?>
</head>

<body>
    <div class="contenedor">

        
        <div class="header">
            <a href="<?php echo e(route('cliente.menu', ['t' => $token])); ?>" class="btn-volver">← Volver</a>
            <h1>Pago</h1>
            <div style="width: 60px"></div>
        </div>

        
        <div class="tarjeta">
            <div class="tarjeta-header">Pedido #<?php echo e($pedido->id); ?></div>

            <?php $__currentLoopData = $pedido->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="item-pedido">
                <span>
                    <span class="item-nombre"><?php echo e($detalle->producto?->nombre); ?></span>
                    <span class="item-cant"> &times;<?php echo e($detalle->cantidad); ?></span>
                </span>
                <span class="item-precio">$<?php echo e(number_format($detalle->subtotal, 2)); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="total-row">
                <span>Total</span>
                <span>$<?php echo e(number_format($pedido->total, 2)); ?></span>
            </div>
        </div>

        
        <div class="tarjeta">
            <div class="tarjeta-header">Método de pago</div>

            <form method="POST" action="<?php echo e(route('cliente.pago.procesar')); ?>" id="form-pago" novalidate>
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_t" value="<?php echo e($token); ?>">
                <input type="hidden" name="pedido_id" value="<?php echo e($pedido->id); ?>">

                <div class="metodos">

                    
                    <label class="metodo-label <?php echo e(old('metodo_pago') === 'EFECTIVO' ? 'seleccionado' : ''); ?>">
                        <input type="radio" name="metodo_pago" value="EFECTIVO"
                            <?php echo e(old('metodo_pago') === 'EFECTIVO' ? 'checked' : ''); ?>

                            onchange="onMetodoCambiado(this)">
                        <span class="metodo-icono" aria-hidden="true">💵</span>
                        <div class="metodo-info">
                            <strong>Efectivo</strong>
                            <span>El mesero confirma el pago en tu mesa</span>
                        </div>
                    </label>

                    
                    <label class="metodo-label <?php echo e(old('metodo_pago') === 'NEQUI' ? 'seleccionado' : ''); ?>">
                        <input type="radio" name="metodo_pago" value="NEQUI"
                            <?php echo e(old('metodo_pago') === 'NEQUI' ? 'checked' : ''); ?>

                            onchange="onMetodoCambiado(this)">
                        <span class="metodo-icono" aria-hidden="true">📱</span>
                        <div class="metodo-info">
                            <strong>Nequi</strong>
                            <span>Recibirás una notificación en tu app</span>
                        </div>
                    </label>

                </div>

                <?php $__errorArgs = ['metodo_pago'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="error-campo" style="padding: 0 1.2rem"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                
                <div class="nota-efectivo" id="nota-efectivo"
                    style="<?php echo e(old('metodo_pago') === 'EFECTIVO' ? '' : 'display:none'); ?>">
                    Un mesero se acercará a tu mesa para confirmar el pago en efectivo.
                </div>

                <div class="campo-nequi" id="campo-nequi"
                    style="<?php echo e(old('metodo_pago') === 'NEQUI' ? '' : 'display:none'); ?>">

                    <label class="campo-label" for="telefono">Número de celular Nequi</label>
                    <div class="input-wrapper">
                        <span class="prefijo">🇨🇴 +57</span>
                        <input type="tel" id="telefono" name="telefono"
                            class="input-field <?php echo e($errors->has('telefono') ? 'input-error' : ''); ?>"
                            placeholder="300 000 0000" maxlength="10"
                            inputmode="numeric" pattern="3[0-9]{9}"
                            value="<?php echo e(old('telefono')); ?>" autocomplete="tel-national">
                    </div>
                    <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-campo"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <label class="campo-label" for="email" style="margin-top: 1rem">
                        Correo electrónico
                    </label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email"
                            class="input-field <?php echo e($errors->has('email') ? 'input-error' : ''); ?>"
                            placeholder="tucorreo@ejemplo.com"
                            value="<?php echo e(old('email')); ?>"
                            autocomplete="email" inputmode="email">
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="error-campo"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <p style="font-size: 0.75rem; color: #888; margin-top: 0.6rem; line-height: 1.4">
                        Recibirás el comprobante de <strong>$<?php echo e(number_format($pedido->total, 2)); ?></strong>
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

</html><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/cliente/pago.blade.php ENDPATH**/ ?>