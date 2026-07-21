@section('titulo', 'Usuarios')
<div x-data="{ 
        isOpen: {{ ($errors->any() || $editar) ? 'true' : 'false' }},
        showModalEliminar: false,
        deleteUrl: '',
        deleteName: '',
        showModalToggle: false,
        toggleUserId: '',
        toggleName: '',
        toggleEstado: false
     }"
     @open-sidebar.window="isOpen = true"
     @close-sidebar.window="isOpen = false">
    @vite(['resources/css/usuarios.css'])
    <style> [x-cloak] { display: none !important; } </style>

    {{-- OVERLAY --}}
    <div class="drawer-overlay" id="drawerOverlay" x-cloak :class="{ 'activo': isOpen }" @click="isOpen = false" wire:ignore></div>

    {{-- DRAWER --}}
    <div class="drawer" id="drawer" x-cloak :class="{ 'activo': isOpen }" wire:ignore>
        <div class="drawer-cabecera">
            <span class="drawer-titulo" id="drawerTitulo">Nuevo usuario</span>
            <button class="btn-cerrar" @click="isOpen = false" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <div class="drawer-cuerpo" style="position: relative;">
            <!-- Overlay de Carga -->
            <div id="formLoadingOverlay" style="display: none; position: absolute; inset: 0; background: rgba(253, 251, 247, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center;">
                <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                <div style="width: 30px; height: 30px; border: 3px solid rgba(224, 122, 95, 0.2); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <span style="margin-top: 0.8rem; font-size: 0.85rem; color: #2C241B; font-weight: 500;">Cargando información...</span>
            </div>

            <div id="drawerFormContent" style="transition: opacity 0.2s;"></div>
        </div>

        <div class="drawer-footer">
            <button type="button" class="btn-principal" id="drawerSubmitBtn">
                + Crear usuario
            </button>
            <button type="button" class="btn-cancelar" @click="isOpen = false">Cancelar</button>
        </div>
    </div>

    {{-- Template CREAR --}}
    <template id="tplCrear">
        <form method="POST" action="{{ route('admin.usuarios.store') }}" id="drawerForm">
            @csrf
            <div class="grupo">
                <label for="nombre_crear">Nombre completo</label>
                <input type="text" id="nombre_crear" name="nombre"
                    value="{{ old('nombre') }}"
                    placeholder="Juan Pérez" required autofocus
                    oninput="this.value = this.value.replace(/[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]/gu, '')">
                @error('nombre')<div class="error-campo">{{ $message }}</div>@enderror
            </div>
            <div class="grupo">
                <label for="email_crear">Correo electrónico</label>
                <input type="email" id="email_crear" name="email"
                    value="{{ old('email') }}"
                    placeholder="correo@ejemplo.com" required>
                @error('email')<div class="error-campo">{{ $message }}</div>@enderror
            </div>
            <div class="grupo">
                <label for="rol_id_crear">Rol</label>
                <select id="rol_id_crear" name="rol_id" required>
                    <option value="">— Selecciona un rol —</option>
                    @foreach($roles as $rol)
                    <option value="{{ $rol->id }}"
                        {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                        {{ $rol->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('rol_id')<div class="error-campo">{{ $message }}</div>@enderror
            </div>
            <div class="grupo">
                <label for="password_crear">Contraseña</label>
                <input type="password" id="password_crear" name="password"
                    placeholder="Mínimo 6 caracteres" required>
                @error('password')<div class="error-campo">{{ $message }}</div>@enderror
            </div>
            <div class="grupo">
                <div class="toggle-grupo">
                    <span class="toggle-label">Usuario activo</span>
                    <label class="toggle">
                        <input type="checkbox" name="estado" {{ old('estado', true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </form>
    </template>

    {{-- Datos de roles para JS --}}
    <script>
        const rolesData = @json($roles->map(fn($r) => ['id' => $r->id, 'nombre' => $r->nombre]));
    </script>

    {{-- PÁGINA --}}
    <div class="pagina-header">
        <div class="pagina-header-texto">
            <h1>Usuarios</h1>
            <p>Administra los usuarios del sistema</p>
        </div>
        <button class="btn-nuevo" @click="isOpen = true; abrirDrawerCrear()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Usuario
        </button>
    </div>

    <div class="tarjeta">
        <div class="tarjeta-header">
            Listado de Usuarios ({{ method_exists($usuarios, 'total') ? $usuarios->total() : $usuarios->count() }})
        </div>

        @if($usuarios->isEmpty())
            <div class="vacio">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: #EAE5DD;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p>No se encontraron usuarios que coincidan con la búsqueda.</p>
            </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Fecha de Registro</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $user)
                <tr>
                    <td>
                        <div class="usuario-info">
                            <div class="avatar">{{ substr($user->nombre, 0, 1) }}</div>
                            <div>
                                <div style="font-weight: 700;">{{ $user->nombre }}</div>
                                <div class="texto-gris">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-rol">{{ $user->rol->nombre ?? 'Sin rol' }}</span>
                    </td>
                    <td>
                        @if($user->estado)
                            <span class="badge-activo">Activo</span>
                        @else
                            <span class="badge-inactivo">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 600;">{{ $user->created_at ? $user->created_at->format('d M, Y') : '' }}</div>
                        <div class="texto-gris">{{ $user->created_at ? $user->created_at->format('h:i A') : '' }}</div>
                    </td>
                    <td>
                        <div class="acciones" style="justify-content: flex-end;">
                            <button type="button" class="btn-editar" onclick="isOpen = true; abrirDrawerEditar('{{ $user->id }}', '{{ addslashes($user->nombre) }}', '{{ addslashes($user->email) }}', '{{ $user->rol_id ?? ($user->roles->first()?->name ?? '') }}', {{ $user->estado ? 'true' : 'false' }}, '{{ route('admin.usuarios.store') }}')" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button type="button" class="{{ $user->estado ? 'btn-toggle-on' : 'btn-toggle-off' }}" @click.prevent.stop="toggleUserId = '{{ $user->id }}'; toggleName = '{{ addslashes($user->nombre) }}'; toggleEstado = {{ $user->estado ? 'true' : 'false' }}; showModalToggle = true;" title="{{ $user->estado ? 'Desactivar' : 'Activar' }}">
                                @if($user->estado)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @endif
                            </button>
                            <button type="button" class="btn-eliminar" @click.prevent.stop="deleteUrl = '{{ route('admin.usuarios.destroy', $user->id) }}'; deleteName = '{{ addslashes($user->nombre) }}'; showModalEliminar = true;" title="Eliminar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(method_exists($usuarios, 'links'))
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
                {{ $usuarios->links() }}
            </div>
        @endif
        @endif
    </div>

    @if($errors->any() || $editar)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if($editar)
            abrirDrawerEditar(
                '{{ $editar->id }}',
                '{{ addslashes($editar->nombre) }}',
                '{{ addslashes($editar->email) }}',
                '{{ $editar->rol->name }}',
                {{ $editar->estado ? 'true' : 'false' }},
                '{{ route('admin.usuarios.store') }}'
            );
            @else
            abrirDrawerCrear();
            @endif
        });
    </script>
    @endif

    {{-- FORMULARIO DE ELIMINACIÓN GLOBAL --}}
    <form method="POST" :action="deleteUrl" style="display: none;" id="formEliminarUsuario">
        @csrf
        @method('DELETE')
    </form>

    {{-- FORMULARIO DE TOGGLE GLOBAL --}}
    <form method="POST" action="{{ route('admin.usuarios.store') }}" style="display: none;" id="formToggleUsuario">
        @csrf
        <input type="hidden" name="toggle_user_id" :value="toggleUserId">
    </form>

    {{-- MODAL DE CONFIRMACIÓN DE TOGGLE --}}
    <div class="modal-eliminar-overlay" x-cloak x-show="showModalToggle">
        <div class="modal-eliminar-caja" @click.away="showModalToggle = false">
            <div class="modal-eliminar-icono" :style="toggleEstado ? 'background: rgba(245, 158, 11, 0.1); color: #f59e0b;' : 'background: rgba(16, 185, 129, 0.1); color: #10b981;'">
                <template x-if="toggleEstado">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>
                <template x-if="!toggleEstado">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>
            <h3 class="modal-eliminar-titulo" x-text="toggleEstado ? '¿Desactivar usuario?' : '¿Activar usuario?'"></h3>
            <p class="modal-eliminar-mensaje">
                Estás a punto de <span x-text="toggleEstado ? 'desactivar' : 'activar'"></span> a <strong x-text="toggleName"></strong>.<br>
                <span x-text="toggleEstado ? 'No podrá iniciar sesión en el sistema.' : 'Podrá acceder al sistema nuevamente.'"></span>
            </p>
            
            <div class="modal-eliminar-acciones">
                <button type="button" class="btn-modal-cancelar" @click="showModalToggle = false">Cancelar</button>
                <button type="button" class="btn-modal-eliminar" :style="toggleEstado ? 'background: #f59e0b;' : 'background: #10b981;'" @click="document.getElementById('formToggleUsuario').submit()" x-text="toggleEstado ? 'Sí, desactivar' : 'Sí, activar'"></button>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    <div class="modal-eliminar-overlay" x-cloak x-show="showModalEliminar">
        <div class="modal-eliminar-caja" @click.away="showModalEliminar = false">
            <div class="modal-eliminar-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="modal-eliminar-titulo">¿Eliminar usuario?</h3>
            <p class="modal-eliminar-mensaje">
                Estás a punto de eliminar a <strong x-text="deleteName"></strong>. Esta acción no se puede deshacer.
            </p>
            
            <div class="modal-eliminar-acciones">
                <button type="button" class="btn-modal-cancelar" @click="showModalEliminar = false">Cancelar</button>
                <button type="button" class="btn-modal-eliminar" @click="document.getElementById('formEliminarUsuario').submit()">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <style>
        .modal-eliminar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-eliminar-caja {
            background: var(--surface);
            width: 90%;
            max-width: 400px;
            padding: 2.5rem 2rem;
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-eliminar-icono {
            width: 60px;
            height: 60px;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .modal-eliminar-icono svg {
            width: 28px;
            height: 28px;
        }

        .modal-eliminar-titulo {
            font-family: 'DM Serif Display', serif;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--text-main);
        }

        .modal-eliminar-mensaje {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .modal-eliminar-mensaje strong {
            color: var(--text-main);
        }

        .modal-eliminar-acciones {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-modal-cancelar {
            flex: 1;
            padding: 0.875rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid var(--border);
            font-size: 0.95rem;
            background: transparent;
            color: var(--text-main);
        }

        .btn-modal-cancelar:hover {
            background: rgba(0,0,0,0.05);
        }

        .btn-modal-eliminar {
            flex: 1;
            padding: 0.875rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.95rem;
            background: #ef4444;
            color: white;
        }

        .btn-modal-eliminar:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>

    <script>
        const overlay = document.getElementById('drawerOverlay');
        const drawer = document.getElementById('drawer');
        const titulo = document.getElementById('drawerTitulo');
        const contenido = document.getElementById('drawerFormContent');
        const submitBtn = document.getElementById('drawerSubmitBtn');

        function abrirDrawer() {
            window.dispatchEvent(new CustomEvent('open-sidebar'));
            document.body.style.overflow = 'hidden';
        }

        function cerrarDrawer() {
            window.dispatchEvent(new CustomEvent('close-sidebar'));
            document.body.style.overflow = '';
        }

        function abrirDrawerCrear() {
            const tpl = document.getElementById('tplCrear');
            contenido.innerHTML = '';
            contenido.appendChild(tpl.content.cloneNode(true));
            titulo.innerHTML = 'Nuevo usuario';
            submitBtn.textContent = '+ Crear usuario';
            abrirDrawer();
            setTimeout(() => {
                const i = contenido.querySelector('input[type="text"]');
                if (i) i.focus();
            }, 350);
        }

        function abrirDrawerEditar(id, nombre, email, rolActual, estado, actionUrl) {
            const opcionesRol = `<option value="">— Selecciona un rol —</option>` +
                rolesData.map(r =>
                    `<option value="${r.id}" ${r.id === rolActual ? 'selected' : ''}>${escHtml(r.nombre)}</option>`
                ).join('');

            contenido.innerHTML = `
            <form method="POST" action="${actionUrl}" id="drawerForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="user_id" value="${id}">
                <div class="grupo">
                    <label for="nombre_editar">Nombre completo</label>
                    <input type="text" id="nombre_editar" name="nombre"
                           value="${escHtml(nombre)}" placeholder="Juan Pérez" required autofocus
                           oninput="this.value = this.value.replace(/[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]/gu, '')">
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
            submitBtn.textContent = 'Guardar cambios';
            abrirDrawer();
            
            const overlayLoader = document.getElementById('formLoadingOverlay');
            if (overlayLoader) {
                overlayLoader.style.display = 'flex';
                contenido.style.opacity = '0';
                setTimeout(() => {
                    overlayLoader.style.display = 'none';
                    contenido.style.opacity = '1';
                    const i = contenido.querySelector('input[type="text"]');
                    if (i) i.focus();
                }, 400); // Simulamos 400ms de carga para mantener consistencia UI
            } else {
                setTimeout(() => {
                    const i = contenido.querySelector('input[type="text"]');
                    if (i) i.focus();
                }, 350);
            }
        }

        function escHtml(str) {
            if (!str) return '';
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(String(str)));
            return d.innerHTML;
        }

        // ── Envío de formulario ───────────────────────────────────────────────
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
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

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') cerrarDrawer();
        });
    </script>

</div>