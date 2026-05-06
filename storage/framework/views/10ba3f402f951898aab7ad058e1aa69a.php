<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Bambu — Acceso</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/login.css']); ?>
</head>
<body>

<div class="container" id="contenedor">

    
    <div class="panel-imagen" id="panelImagen">
        <span class="flotante">🍷</span>
        <span class="flotante">🫕</span>
        <span class="flotante">🥩</span>

        <div class="panel-contenido">
            <div class="top-bar">
                <span class="top-bar-label">Cafe Bambu</span>
                <div class="nav-tabs">
                    <button class="nav-tab activo" id="btn-login"    onclick="cambiarModo('login')">Ingresar</button>
                    <button class="nav-tab"        id="btn-register" onclick="cambiarModo('register')">Registro</button>
                </div>
            </div>

            <div class="panel-bottom">
                <p class="panel-eyebrow">Sistema de gestión</p>
                <h1 class="panel-titulo" id="panel-titulo">
                    El arte de<br><em>servir bien</em><br>comienza aquí
                </h1>
                <div class="panel-profile">
                    <div class="panel-avatar">🍴</div>
                    <div>
                        <div class="panel-nombre">Cafe Bambu</div>
                        <div class="panel-rol">Gestión de restaurante</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="form-login" id="formLogin">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">Cafe <span>Bambu</span></span>
            </div>
            <h2 class="form-titulo">Bienvenido</h2>
            <p class="form-sub">Ingresa tus credenciales para continuar</p>

            <?php if(session('error')): ?>
                <div class="alerta-error"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>
                <div class="campo">
                    <input type="email" name="email" placeholder="Correo electrónico"
                        value="<?php echo e(old('email')); ?>" autocomplete="email" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="campo-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="campo">
                    <div class="campo-rel">
                        <input type="password" id="pass-login" name="password"
                            placeholder="Contraseña" autocomplete="current-password" required>
                        <button type="button" class="toggle-pass" onclick="togglePass('pass-login')">👁</button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="campo-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <button type="submit" class="btn-submit">Ingresar al sistema</button>
            </form>
            <p class="form-footer">Cafe Bambu · <?php echo e(date('Y')); ?></p>
        </div>
    </div>

    
    <div class="form-register" id="formRegister">
        <div class="form-inner">
            <div class="logo">
                <span class="logo-icono">🍴</span>
                <span class="logo-nombre">Cafe <span>Bambu</span></span>
            </div>
            <h2 class="form-titulo">Crear cuenta</h2>
            <p class="form-sub">Completa los datos para registrarte</p>

            <?php if($errors->any() && old('_form') === 'register'): ?>
                <div class="alerta-error">Revisa los campos e intenta de nuevo.</div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('register')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_form" value="register">
                <div class="campo">
                    <input type="text" name="nombre" placeholder="Nombre completo"
                        value="<?php echo e(old('nombre')); ?>" required>
                    <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="campo-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="campo">
                    <input type="email" name="email" placeholder="Correo electrónico"
                        value="<?php echo e(old('email')); ?>" autocomplete="email" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="campo-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="grupo">
            <select id="rol_id" name="rol_id" required>
                <option value="">— Selecciona un rol —</option>
                
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($rol->id); ?>" <?php echo e(old('rol_id') == $rol->id ? 'selected' : ''); ?>>
                        <?php echo e($rol->nombre); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['rol_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="error-campo"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
                <div class="campo">
                    <div class="campo-rel">
                        <input type="password" id="pass-reg" name="password"
                            placeholder="Contraseña" required>
                        <button type="button" class="toggle-pass" onclick="togglePass('pass-reg')">👁</button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="campo-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="campo">
                    <input type="password" name="password_confirmation"
                        placeholder="Confirmar contraseña" required>
                </div>
                <button type="submit" class="btn-submit">Crear cuenta</button>
            </form>
            <p class="form-footer">Cafe Bambu · <?php echo e(date('Y')); ?></p>
        </div>
    </div>

</div>

<script>
    // ── Limpiar token de pestaña al llegar desde logout ─────────────────
    // El logout redirige a /login?_clear=1 — aquí limpiamos sessionStorage
    (function() {
        const url = new URL(window.location);
        if (url.searchParams.has('_clear')) {
            sessionStorage.removeItem('staff_token');
            url.searchParams.delete('_clear');
            window.history.replaceState(null, '', url.pathname + url.search + url.hash);
        }
    })();

    let modoActual = 'login';

    function cambiarModo(modo) {
        if (modo === modoActual) return;
        modoActual = modo;

        const contenedor = document.getElementById('contenedor');
        const btnLogin    = document.getElementById('btn-login');
        const btnRegister = document.getElementById('btn-register');
        const titulo      = document.getElementById('panel-titulo');

        if (modo === 'register') {
            contenedor.classList.add('modo-register');
            btnLogin.classList.remove('activo');
            btnRegister.classList.add('activo');
            titulo.style.opacity = '0';
            setTimeout(() => {
                titulo.innerHTML = 'Únete a<br><em>la experiencia</em><br>culinaria';
                titulo.style.opacity = '1';
            }, 300);
        } else {
            contenedor.classList.remove('modo-register');
            btnLogin.classList.add('activo');
            btnRegister.classList.remove('activo');
            titulo.style.opacity = '0';
            setTimeout(() => {
                titulo.innerHTML = 'El arte de<br><em>servir bien</em><br>comienza aquí';
                titulo.style.opacity = '1';
            }, 300);
        }
    }

    function togglePass(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    <?php if(old('_form') === 'register' || ($errors->any() && old('_form') === 'register')): ?>
        document.addEventListener('DOMContentLoaded', () => cambiarModo('register'));
    <?php endif; ?>
</script>

</body>
</html><?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/auth/login.blade.php ENDPATH**/ ?>