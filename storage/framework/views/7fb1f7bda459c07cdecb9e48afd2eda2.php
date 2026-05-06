
<?php $__env->startSection('titulo', 'Usuarios'); ?>
<?php $__env->startSection('contenido'); ?>
<?php echo app('Illuminate\Foundation\Vite')(['resources/css/usuarios.css']); ?>


<div class="drawer-overlay" id="drawerOverlay" onclick="cerrarDrawer()"></div>


<div class="drawer" id="drawer">
    <div class="drawer-cabecera">
        <span class="drawer-titulo" id="drawerTitulo">Nuevo usuario</span>
        <button class="btn-cerrar" onclick="cerrarDrawer()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="drawer-cuerpo">
        <div id="drawerFormContent"></div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-principal" id="drawerSubmitBtn">
            + Crear usuario
        </button>
        <button type="button" class="btn-cancelar" onclick="cerrarDrawer()">Cancelar</button>
    </div>
</div>


<template id="tplCrear">
    <form method="POST" action="<?php echo e(route('admin.usuarios.store')); ?>" id="drawerForm">
        <?php echo csrf_field(); ?>
        <div class="grupo">
            <label for="nombre_crear">Nombre completo</label>
            <input type="text" id="nombre_crear" name="nombre"
                   value="<?php echo e(old('nombre')); ?>"
                   placeholder="Juan Pérez" required autofocus>
            <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="error-campo"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grupo">
            <label for="email_crear">Correo electrónico</label>
            <input type="email" id="email_crear" name="email"
                   value="<?php echo e(old('email')); ?>"
                   placeholder="correo@ejemplo.com" required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="error-campo"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grupo">
            <label for="rol_id_crear">Rol</label>
            <select id="rol_id_crear" name="rol_id" required>
                <option value="">— Selecciona un rol —</option>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($rol->id); ?>"
                        <?php echo e(old('rol_id') == $rol->id ? 'selected' : ''); ?>>
                        <?php echo e($rol->nombre); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['rol_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="error-campo"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grupo">
            <label for="password_crear">Contraseña</label>
            <input type="password" id="password_crear" name="password"
                   placeholder="Mínimo 6 caracteres" required>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="error-campo"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grupo">
            <div class="toggle-grupo">
                <span class="toggle-label">Usuario activo</span>
                <label class="toggle">
                    <input type="checkbox" name="estado" <?php echo e(old('estado', true) ? 'checked' : ''); ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
    </form>
</template>


<script>
    const rolesData = <?php echo json_encode($roles->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre]), 512) ?>;
</script>


<div class="pagina-header">
    <div class="pagina-header-texto">
        <h1>Usuarios</h1>
        <p>Administra los usuarios del sistema</p>
    </div>
    <button class="btn-nuevo" onclick="abrirDrawerCrear()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nuevo usuario
    </button>
</div>


<div class="tarjeta">
    <div class="tarjeta-header"><?php echo e($usuarios->count()); ?> usuarios registrados</div>

    <?php if($usuarios->isEmpty()): ?>
        <div class="vacio">No hay usuarios todavía</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último login</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="usuario-info">
                            <div class="avatar"><?php echo e(substr($usuario->nombre, 0, 1)); ?></div>
                            <div>
                                <div><?php echo e($usuario->nombre); ?></div>
                                <div class="texto-gris"><?php echo e($usuario->email); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-rol"><?php echo e($usuario->rol?->nombre ?? '—'); ?></span>
                    </td>
                    <td>
                        <?php if($usuario->estado): ?>
                            <span class="badge-activo">Activo</span>
                        <?php else: ?>
                            <span class="badge-inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="texto-gris">
                        <?php echo e($usuario->ultimo_login ? \Carbon\Carbon::parse($usuario->ultimo_login)->format('d/m H:i') : 'Nunca'); ?>

                    </td>
                    <td>
                        <div class="acciones">
                            <button type="button" class="btn-editar"
                                onclick="abrirDrawerEditar(
                                    <?php echo e($usuario->id); ?>,
                                    '<?php echo e(addslashes($usuario->nombre)); ?>',
                                    '<?php echo e(addslashes($usuario->email)); ?>',
                                    <?php echo e($usuario->rol_id ?? 'null'); ?>,
                                    <?php echo e($usuario->estado ? 'true' : 'false'); ?>,
                                    '<?php echo e(route('admin.usuarios.actualizar', $usuario->id)); ?>'
                                )">Editar</button>

                            <form method="POST" action="<?php echo e(route('admin.usuarios.toggle', $usuario->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php if($usuario->estado): ?>
                                    <button type="submit" class="btn-toggle-on"
                                            onclick="return confirm('¿Desactivar a <?php echo e($usuario->nombre); ?>?')">
                                        Desactivar
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn-toggle-off">Activar</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if($errors->any() || $editar): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if($editar): ?>
            abrirDrawerEditar(
                <?php echo e($editar->id); ?>,
                '<?php echo e(addslashes($editar->nombre)); ?>',
                '<?php echo e(addslashes($editar->email)); ?>',
                <?php echo e($editar->rol_id ?? 'null'); ?>,
                <?php echo e($editar->estado ? 'true' : 'false'); ?>,
                '<?php echo e(route('admin.usuarios.actualizar', $editar->id)); ?>'
            );
        <?php else: ?>
            abrirDrawerCrear();
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<script>
    const overlay   = document.getElementById('drawerOverlay');
    const drawer    = document.getElementById('drawer');
    const titulo    = document.getElementById('drawerTitulo');
    const contenido = document.getElementById('drawerFormContent');
    const submitBtn = document.getElementById('drawerSubmitBtn');

    function abrirDrawer() {
        overlay.classList.add('activo');
        drawer.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }

    function cerrarDrawer() {
        overlay.classList.remove('activo');
        drawer.classList.remove('activo');
        document.body.style.overflow = '';
    }

    function abrirDrawerCrear() {
        const tpl = document.getElementById('tplCrear');
        contenido.innerHTML = '';
        contenido.appendChild(tpl.content.cloneNode(true));
        titulo.innerHTML = 'Nuevo usuario';
        submitBtn.textContent = '+ Crear usuario';
        abrirDrawer();
        setTimeout(() => { const i = contenido.querySelector('input[type="text"]'); if (i) i.focus(); }, 350);
    }

    function abrirDrawerEditar(id, nombre, email, rolId, estado, actionUrl) {
        const opcionesRol = `<option value="">— Selecciona un rol —</option>` +
            rolesData.map(r =>
                `<option value="${r.id}" ${r.id === rolId ? 'selected' : ''}>${escHtml(r.nombre)}</option>`
            ).join('');

        contenido.innerHTML = `
            <form method="POST" action="${actionUrl}" id="drawerForm">
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                <div class="grupo">
                    <label for="nombre_editar">Nombre completo</label>
                    <input type="text" id="nombre_editar" name="nombre"
                           value="${escHtml(nombre)}" placeholder="Juan Pérez" required autofocus>
                </div>
                <div class="grupo">
                    <label for="email_editar">Correo electrónico</label>
                    <input type="email" id="email_editar" name="email"
                           value="${escHtml(email)}" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="grupo">
                    <label for="rol_editar">Rol</label>
                    <select id="rol_editar" name="rol_id" required>${opcionesRol}</select>
                </div>
                <div class="grupo">
                    <label for="password_editar">
                        Contraseña
                        <small style="opacity:.5; text-transform:none; letter-spacing:0">(dejar vacío para no cambiar)</small>
                    </label>
                    <input type="password" id="password_editar" name="password"
                           placeholder="Nueva contraseña...">
                    <div class="hint">Solo escribe si deseas cambiarla</div>
                </div>
                <div class="grupo">
                    <div class="toggle-grupo">
                        <span class="toggle-label">Usuario activo</span>
                        <label class="toggle">
                            <input type="checkbox" name="estado" ${estado ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </form>
        `;

        titulo.innerHTML = `Editar usuario <span class="badge-editar">Editando</span>`;
        submitBtn.textContent = '💾 Guardar cambios';
        abrirDrawer();
        setTimeout(() => { const i = contenido.querySelector('input[type="text"]'); if (i) i.focus(); }, 350);
    }

    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str)));
        return d.innerHTML;
    }

    // ── Envío de formulario ───────────────────────────────────────────────
    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            const form = document.getElementById('drawerForm');
            if (!form) return;
            
            if (form.checkValidity()) {
                // Inyección manual del token para asegurar autenticación
                if (window.__STAFF_TOKEN && !form.querySelector('input[name="_st"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_st';
                    input.value = window.__STAFF_TOKEN;
                    form.appendChild(input);
                }
                form.submit();
            } else {
                form.reportValidity();
            }
        });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarDrawer(); });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/admin/usuarios/index.blade.php ENDPATH**/ ?>