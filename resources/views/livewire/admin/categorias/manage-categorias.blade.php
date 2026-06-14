<div wire:key="manage-categorias-container"
    x-data="{ 
        isOpen: @entangle('showModal').live,
        showModalEliminar: false,
        catId: '',
        catNombre: ''
    }"
    @open-sidebar.window="isOpen = true"
    @close-sidebar.window="isOpen = false"
    @close-modal.window="showModalEliminar = false; window.dispatchEvent(new CustomEvent('close-sidebar'))">
    @vite(['resources/css/categorias.css'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- OVERLAY PARA EL DRAWER MANUAL --}}
    <div class="drawer-overlay" id="drawerOverlay" x-cloak :class="{ 'activo': isOpen }" @click="isOpen = false"></div>

    {{-- DRAWER --}}
    <div class="drawer" id="drawer" x-cloak :class="{ 'activo': isOpen }">
        <form wire:submit.prevent="save" id="drawerForm" style="display: flex; flex-direction: column; height: 100%;">
            <div class="drawer-cabecera">
                <span class="drawer-titulo" id="drawerTitulo">
                    {{ $isEditing ? 'Editar categoría' : 'Nueva categoría' }}
                </span>
                <button type="button" class="btn-cerrar" @click="isOpen = false" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="drawer-cuerpo" style="position: relative;">
                <!-- Overlay de Carga -->
                <div wire:loading.flex wire:target="edit" style="position: absolute; inset: 0; background: rgba(253, 251, 247, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center;">
                    <div style="width: 30px; height: 30px; border: 3px solid rgba(224, 122, 95, 0.2); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <span style="margin-top: 0.8rem; font-size: 0.85rem; color: #2C241B; font-weight: 500;">Cargando información...</span>
                </div>
                <style>
                    @keyframes spin {
                        to {
                            transform: rotate(360deg);
                        }
                    }
                </style>

                <div class="grupo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" wire:model="nombre"
                        placeholder="Ej: Bebidas, Entradas..."
                        required autofocus>
                    @error('nombre')
                    <div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="grupo">
                    <label for="descripcion">Descripción <small style="opacity:.5">(opcional)</small></label>
                    <textarea id="descripcion" wire:model="descripcion" rows="4"
                        placeholder="Descripción breve de la categoría..."></textarea>
                    @error('descripcion')
                    <div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="grupo" style="margin-top: 1.5rem;">
                    <label>Estado de la Categoría</label>
                    <label class="toggle-switch-wrapper" style="margin-top: 0.5rem;">
                        <input type="checkbox" class="toggle-checkbox" wire:model="activo">
                        <div class="toggle-switch"></div>
                        <span style="font-size: 0.9rem; color: #f7f3ee;">
                            {{ $activo ? 'Activa (visible en menú)' : 'Inactiva (oculta en menú)' }}
                        </span>
                    </label>
                    @error('activo')
                    <div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="drawer-footer">
                <button type="submit" class="btn-principal" id="drawerSubmitBtn">
                    {{ $isEditing ? 'Guardar cambios' : '+ Crear categoría' }}
                </button>
                <button type="button" class="btn-cancelar" @click="isOpen = false">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    {{-- CONTENIDO DE PÁGINA --}}
    <div class="pagina-header">
        <div class="pagina-header-texto">
            <h1>Categorías</h1>
            <p>Administra las categorías del menú</p>
        </div>
        <button class="btn-nuevo" wire:click="openCreateModal" @click="isOpen = true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; margin-right: 6px;">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            Nueva categoría
        </button>
    </div>

    @if($errors->has('general'))
    <div style="background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.2); color: #f87171; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p style="font-size: 0.9rem; font-weight: 600; margin: 0;">{{ $errors->first('general') }}</p>
    </div>
    @endif

    {{-- TABLA --}}
    <div class="tarjeta">
        <div class="tarjeta-header">{{ $total }} categorías registradas</div>

        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);">
            <input wire:model.live.debounce.300ms="search" type="text"
                style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; background: var(--bg); border: 1px solid var(--border); color: var(--text-main);"
                placeholder="Buscar por nombre de categoría...">
        </div>

        @if($categorias->isEmpty())
        <div class="vacio">No hay categorías todavía. ¡Crea la primera!</div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorias as $categoria)
                <tr wire:key="cat-{{ $categoria->id }}">
                    <td class="texto-gris">{{ $categoria->short_id }}</td>
                    <td>{{ $categoria->nombre }}</td>
                    <td class="texto-gris">{{ $categoria->descripcion ?? '—' }}</td>
                    <td>
                        <label class="toggle-switch-wrapper" wire:loading.attr="disabled" style="cursor: pointer;">
                            <input type="checkbox" class="toggle-checkbox"
                                wire:click="toggleActivo('{{ $categoria->id }}')"
                                {{ $categoria->activo ? 'checked' : '' }}>
                            <div class="toggle-switch"></div>
                            <span class="badge-estado {{ $categoria->activo ? 'badge-estado--activo' : 'badge-estado--inactivo' }}">
                                {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </label>
                    </td>
                    <td>
                        <div class="acciones">
                            <button type="button" class="btn-editar"
                                wire:click="edit('{{ $categoria->id }}')" @click="isOpen = true">
                                Editar
                            </button>

                            <button type="button" class="btn-eliminar"
                                data-id="{{ $categoria->id }}"
                                data-nombre="{{ $categoria->nombre }}"
                                @click="catId = $el.dataset.id; catNombre = $el.dataset.nombre; showModalEliminar = true;">
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($categorias->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
            {{ $categorias->links(data: ['scrollTo' => false]) }}
        </div>
        @endif
        @endif
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    <div class="modal-overlay" id="modalEliminar" x-cloak
         x-show="showModalEliminar"
         x-transition.opacity>
        <div class="modal-confirm" @click.away="showModalEliminar = false">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-family: 'DM Serif Display', serif;">¿Eliminar categoría?</h3>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.5;">
                Estás a punto de eliminar la categoría <strong x-text="catNombre"></strong>. Esta acción no se puede deshacer.
            </p>
            
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" @click="showModalEliminar = false">Cancelar</button>
                <button type="button" class="btn-modal btn-modal-confirm" @click="$wire.eliminarCategoria(catId)">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2500;
        }

        .modal-confirm {
            background: var(--surface);
            width: 90%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 1.2rem;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn-modal {
            flex: 1;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.95rem;
        }

        .btn-modal-cancel {
            background: rgba(0,0,0,0.05);
            color: var(--text-main);
        }

        .btn-modal-cancel:hover {
            background: rgba(0,0,0,0.1);
        }

        .btn-modal-confirm {
            background: #ef4444;
            color: white;
        }

        .btn-modal-confirm:hover {
            background: #dc2626;
        }
    </style>
</div>