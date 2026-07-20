<div wire:key="manage-productos-container"
     x-data="{ 
        isOpen: @entangle('showModal').live,
        showModalEliminar: false,
        deleteId: '',
        deleteName: ''
     }"
     @open-sidebar.window="isOpen = true"
     @close-sidebar.window="isOpen = false"
     @close-modal.window="window.dispatchEvent(new CustomEvent('close-sidebar'))">
@vite(['resources/css/productos.css'])
<style> [x-cloak] { display: none !important; } </style>

{{-- OVERLAY PARA EL DRAWER MANUAL --}}
<div class="drawer-overlay" id="drawerOverlay" x-cloak :class="{ 'activo': isOpen }" @click="isOpen = false"></div>

{{-- DRAWER --}}
<div class="drawer" id="drawer" x-cloak :class="{ 'activo': isOpen }">
    <form wire:submit.prevent="save" id="drawerForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; height: 100%;">
        <div class="drawer-cabecera">
            <span class="drawer-titulo" id="drawerTitulo">
                {{ $isEditing ? 'Editar producto' : 'Nuevo producto' }}
            </span>
            <button type="button" class="btn-cerrar" @click="isOpen = false" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="drawer-cuerpo" style="position: relative;">
            <!-- Overlay de Carga -->
            <div wire:loading.flex wire:target="edit" style="position: absolute; inset: 0; background: rgba(253, 251, 247, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center;">
                <div style="width: 30px; height: 30px; border: 3px solid rgba(224, 122, 95, 0.2); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <span style="margin-top: 0.8rem; font-size: 0.85rem; color: #2C241B; font-weight: 500;">Cargando información...</span>
            </div>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

            <div class="grupo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" wire:model="nombre"
                       placeholder="Ej: Hamburguesa clásica" required autofocus>
                @error('nombre')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="grupo">
                <label for="descripcion">Descripción <small style="opacity:.5">(opcional)</small></label>
                <textarea id="descripcion" wire:model="descripcion" rows="2"
                          placeholder="Descripción del producto..."></textarea>
                @error('descripcion')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>

            <div class="grupo">
                <label for="receta">Receta y Preparación <small style="opacity:.5">(Solo visible en cocina)</small></label>
                <textarea id="receta" wire:model="receta" rows="4"
                          placeholder="Ingredientes y pasos para preparar este producto..."></textarea>
                @error('receta')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="grupo">
                <label for="precio">Precio</label>
                <input type="number" id="precio" wire:model="precio"
                       placeholder="0.00" step="0.01" min="0" required>
                @error('precio')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>

            <div class="grupo">
                <label for="precio_oferta">Precio de Oferta <small style="opacity:.5">(opcional)</small></label>
                <input type="number" id="precio_oferta" wire:model="precio_oferta"
                       placeholder="0.00" step="0.01" min="0">
                @error('precio_oferta')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="grupo">
                <label for="categoria_id">Categoría <small style="opacity:.5">(opcional)</small></label>
                <select id="categoria_id" wire:model="categoria_id">
                    <option value="">— Sin categoría —</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}{{ !$categoria->activo ? ' (Inactiva)' : '' }}</option>
                    @endforeach
                </select>
                @error('categoria_id')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>

            <div class="grupo">
                <label for="imagen">Imagen del producto</label>
                @if($imagenPath && !$imagen)
                    <div class="imagen-actual-preview" style="margin-bottom: 1rem;">
                        <img src="{{ asset('storage/' . $imagenPath) }}" alt="Vista previa" style="max-height: 100px; border-radius: 8px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.5rem;">Imagen actual</span>
                    </div>
                @endif
                @if($imagen)
                    <div class="imagen-actual-preview" style="margin-bottom: 1rem;">
                        <img src="{{ $imagen->temporaryUrl() }}" alt="Vista previa" style="max-height: 100px; border-radius: 8px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 0.5rem;">Nueva imagen seleccionada</span>
                    </div>
                @endif

                <div class="input-archivo-diseno">
                    <input type="file" id="imagen" wire:model="imagen" accept="image/*">
                    <div class="label-archivo">
                        <span class="icono-archivo"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg></span>
                        <span class="texto-archivo">Haz clic para subir foto</span>
                    </div>
                </div>
                <div wire:loading wire:target="imagen" style="font-size: 0.8rem; color: var(--primary); margin-top: 0.5rem;">Subiendo imagen...</div>
                @error('imagen')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
            
            <div class="grupo">
                <div class="toggle-grupo">
                    <span class="toggle-label">Producto activo (Visible)</span>
                    <label class="toggle">
                        <input type="checkbox" wire:model="activo">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="grupo">
                <div class="toggle-grupo">
                    <span class="toggle-label">Permitir notas/observaciones</span>
                    <label class="toggle">
                        <input type="checkbox" wire:model="permite_notas">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="grupo" style="display: flex; gap: 1rem;">
                <div style="flex: 1;">
                    <label for="limite_minimo_adiciones">Mínimo Adiciones</label>
                    <input type="number" id="limite_minimo_adiciones" wire:model="limite_minimo_adiciones"
                           placeholder="0" min="0" required>
                    @error('limite_minimo_adiciones')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
                </div>
                <div style="flex: 1;">
                    <label for="limite_maximo_adiciones">Máximo Adiciones</label>
                    <input type="number" id="limite_maximo_adiciones" wire:model="limite_maximo_adiciones"
                           placeholder="Sin límite" min="0">
                    @error('limite_maximo_adiciones')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="drawer-footer">
            <button type="submit" class="btn-principal" id="drawerSubmitBtn">
                {{ $isEditing ? 'Guardar cambios' : '+ Crear producto' }}
            </button>
            <button type="button" class="btn-cancelar" @click="isOpen = false">Cancelar</button>
        </div>
    </form>
</div>

{{-- PÁGINA --}}
<div class="pagina-header">
    <div class="pagina-header-texto">
        <h1>Productos</h1>
        <p>Administra los productos del menú</p>
    </div>
    <div style="display:flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <div class="excel-actions" style="display: flex; gap: 0.5rem; background: rgba(0,0,0,0.03); padding: 0.3rem; border-radius: 0.75rem; border: 1px solid var(--border);">
            <button type="button" class="btn-secundario" style="font-size: 0.8rem; padding: 0.5rem 0.8rem; display: inline-flex; align-items: center; gap: 0.4rem; background: transparent; border: none; color: var(--text-main); font-weight: 600; cursor: pointer; border-radius: 0.5rem; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'" @click="$dispatch('abrir-modal-import')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importar
            </button>
            <div style="width: 1px; background: var(--border); margin: 0.3rem 0;"></div>
            <a href="{{ route('admin.productos.exportar') }}" class="btn-secundario" style="font-size: 0.8rem; padding: 0.5rem 0.8rem; display: inline-flex; align-items: center; gap: 0.4rem; background: transparent; border: none; color: var(--text-main); font-weight: 600; cursor: pointer; border-radius: 0.5rem; transition: background 0.2s; text-decoration: none;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exportar
            </a>
            <div style="width: 1px; background: var(--border); margin: 0.3rem 0;"></div>
            <a href="{{ route('admin.productos.plantilla') }}" class="btn-secundario" style="font-size: 0.8rem; padding: 0.5rem 0.8rem; display: inline-flex; align-items: center; gap: 0.4rem; background: transparent; border: none; color: var(--text-main); font-weight: 600; cursor: pointer; border-radius: 0.5rem; transition: background 0.2s; text-decoration: none;" onmouseover="this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.background='transparent'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Plantilla
            </a>
        </div>
        <button class="btn-nuevo" style="padding: 0.65rem 1.2rem; display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 0.75rem;" @click="isOpen = true" wire:click="openCreateModal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo producto
        </button>
    </div>
</div>


{{-- FILTROS --}}
<div class="tarjeta" style="margin-bottom: 1.5rem; padding: 1.2rem; background: var(--surface);">
    <div class="filtros-form" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-grupo" style="flex: 1; min-width: 200px;">
            <label for="buscar" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Buscar</label>
            <input type="text" wire:model.live.debounce.300ms="search" id="buscar" placeholder="Nombre o descripción..." style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.5rem; border: 1px solid var(--border); background: rgba(0,0,0,0.02); color: var(--text-main);">
        </div>
        
        <div class="form-grupo" style="flex: 1; min-width: 150px;">
            <label for="categoria" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Categoría</label>
            <select wire:model.live="filterCategoria" id="categoria" style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.5rem; border: 1px solid var(--border); background: rgba(0,0,0,0.02); color: var(--text-main);">
                <option value="todas">Todas las categorías</option>
                <option value="sin_categoria">Sin categoría</option>
                @foreach($categorias as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}{{ !$c->activo ? ' (Inactiva)' : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- TABLA --}}
<div class="tarjeta">
    <div class="tarjeta-header">{{ $total }} productos registrados</div>

    @if($productos->isEmpty())
        <div class="vacio">No hay productos todavía. ¡Crea el primero!</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 70px">Foto</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                <tr wire:key="prod-{{ $producto->id }}">
                    <td>
                        <div class="producto-miniatura">
                            @if($producto->imagen)
                                <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                            @else
                                <div class="producto-sin-foto" style="display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg></div>
                            @endif
                        </div>
                    </td>
                    <td>
                        {{ $producto->nombre }}
                        @if($producto->receta)
                            <span style="font-size: 0.65rem; background: rgba(52, 211, 153, 0.15); color: #34D399; padding: 0.1rem 0.3rem; border-radius: 4px; border: 1px solid rgba(52, 211, 153, 0.3); margin-left: 0.4rem;" title="Tiene receta configurada">📖 Receta</span>
                        @endif
                        @if($producto->descripcion)
                            <div class="texto-gris descripcion-movil">{{ Str::limit($producto->descripcion, 40) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($producto->categoria)
                            <span class="badge-categoria">{{ $producto->categoria->nombre }}</span>
                        @else
                            <span class="texto-gris">—</span>
                        @endif
                    </td>
                    <td class="precio">
                        @if($producto->precio_oferta)
                            <span style="text-decoration: line-through; color: rgba(247, 243, 238, 0.3); font-size: 0.8rem; margin-right: 0.4rem;">
                                ${{ number_format($producto->precio, 2) }}
                            </span>
                            <span>${{ number_format($producto->precio_oferta, 2) }}</span>
                        @else
                            <span>${{ number_format($producto->precio, 2) }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            @if($producto->activo)
                                <span class="badge-activo">Visible</span>
                            @else
                                <span class="badge-inactivo">Oculto</span>
                            @endif
                            @if($producto->disponible)
                                <span class="badge-activo" style="background: rgba(121, 82, 179, 0.1); color: #bca0dc; border-color: rgba(121, 82, 179, 0.3);">Disponible</span>
                            @else
                                <span class="badge-inactivo" style="background: rgba(220, 38, 38, 0.1); color: #f87171; border-color: rgba(220, 38, 38, 0.25);">Agotado</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="acciones" x-data="{ openDropdown: false }" @click.away="openDropdown = false" style="position: relative; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button type="button" class="btn-editar" style="display: inline-flex; align-items: center; gap: 0.4rem;"
                                wire:click="edit('{{ $producto->id }}')" @click="isOpen = true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                Editar
                            </button>

                            <button type="button" class="btn-opciones-dropdown" @click="openDropdown = !openDropdown" :class="{ 'activo': openDropdown }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                Opciones
                            </button>

                            <!-- Menú Desplegable -->
                            <div x-show="openDropdown" x-transition.opacity x-transition:enter.duration.150ms x-transition:leave.duration.100ms class="dropdown-menu-flotante" style="display: none;" x-cloak>
                                <!-- Variantes -->
                                <button type="button" class="dropdown-item" wire:click="openVariantesModal('{{ $producto->id }}')" @click="openDropdown = false">
                                    <span class="dropdown-icon" style="background: rgba(79, 142, 247, 0.1); color: #4F8EF7;">🎨</span>
                                    <span style="flex:1; text-align:left;">Variantes</span>
                                    <span style="font-weight:700; color:var(--text-main);">{{ $producto->variantes->count() }}</span>
                                </button>

                                <!-- Adiciones -->
                                <button type="button" class="dropdown-item" wire:click="openAdicionesModalForProducto('{{ $producto->id }}')" @click="openDropdown = false">
                                    <span class="dropdown-icon" style="background: rgba(201, 168, 76, 0.15); color: #C9A84C;">➕</span>
                                    <span style="flex:1; text-align:left;">Adiciones</span>
                                    <span style="font-weight:700; color:var(--text-main);">{{ $producto->adiciones->count() }}</span>
                                </button>
                                
                                <div class="dropdown-divider"></div>

                                <!-- Mostrar / Ocultar -->
                                @if($producto->activo)
                                    <button type="button" class="dropdown-item" wire:click="toggleActivo('{{ $producto->id }}')" @click="openDropdown = false">
                                        <span class="dropdown-icon" style="background: rgba(245, 158, 11, 0.1); color: #F59E0B;">👁️</span>
                                        <span style="flex:1; text-align:left;">Ocultar del menú</span>
                                    </button>
                                @else
                                    <button type="button" class="dropdown-item" wire:click="toggleActivo('{{ $producto->id }}')" @click="openDropdown = false">
                                        <span class="dropdown-icon" style="background: rgba(76, 175, 125, 0.1); color: #4CAF7D;">👁️</span>
                                        <span style="flex:1; text-align:left; color:#4CAF7D; font-weight:600;">Mostrar en menú</span>
                                    </button>
                                @endif

                                <!-- Disponible / Agotado -->
                                @if($producto->disponible)
                                    <button type="button" class="dropdown-item" wire:click="toggleDisponible('{{ $producto->id }}')" @click="openDropdown = false">
                                        <span class="dropdown-icon" style="background: rgba(220, 38, 38, 0.1); color: #DC2626;">❌</span>
                                        <span style="flex:1; text-align:left;">Marcar Agotado</span>
                                    </button>
                                @else
                                    <button type="button" class="dropdown-item" wire:click="toggleDisponible('{{ $producto->id }}')" @click="openDropdown = false">
                                        <span class="dropdown-icon" style="background: rgba(76, 175, 125, 0.1); color: #4CAF7D;">✅</span>
                                        <span style="flex:1; text-align:left; color:#4CAF7D; font-weight:600;">Marcar Disponible</span>
                                    </button>
                                @endif

                                <div class="dropdown-divider"></div>

                                <!-- Eliminar -->
                                <button type="button" class="dropdown-item dropdown-item-danger"
                                        @click.prevent.stop="deleteId = '{{ $producto->id }}'; deleteName = {{ json_encode($producto->nombre) }}; showModalEliminar = true; openDropdown = false;">
                                    <span class="dropdown-icon" style="background: transparent;">🗑️</span>
                                    <span style="flex:1; text-align:left; font-weight:600;">Eliminar Producto</span>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($productos->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
                {{ $productos->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    @endif
</div>

{{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN CON ALPINE JS (SÚPER RÁPIDO) --}}
<div class="modal-eliminar-overlay" x-cloak x-show="showModalEliminar">
    <div class="modal-eliminar-caja" @click.away="showModalEliminar = false">
        <div class="modal-eliminar-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <h3 class="modal-eliminar-titulo">¿Eliminar producto?</h3>
        <p class="modal-eliminar-mensaje">
            Estás a punto de eliminar el producto <strong x-text="deleteName"></strong>. Esta acción no se puede deshacer.
        </p>
        
        <div class="modal-eliminar-acciones">
            <button type="button" class="btn-modal-cancelar" @click="showModalEliminar = false">Cancelar</button>
            <button type="button" class="btn-modal-eliminar" @click="$wire.eliminarProducto(deleteId); showModalEliminar = false">Sí, eliminar</button>
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
        z-index: 9999; /* Asegura que siempre esté por encima de todo */
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

    .modal-icon {
        width: 50px;
        height: 50px;
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
    }

    .modal-icon svg {
        width: 24px;
        height: 24px;
    }

    .modal-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.4rem;
        margin-bottom: 0.5rem;
        color: var(--text-main);
    }

    .modal-desc {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 2rem;
        line-height: 1.5;
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

    /* ══════════════════════════════════════════
       RESPONSIVE — MANAGE PRODUCTOS
    ══════════════════════════════════════════ */

    /* Tablet Landscape (768–1023px) */
    @media (min-width: 768px) and (max-width: 1023.98px) {
        .pagina-header {
            gap: 0.85rem;
        }
        .pagina-header > div:last-child {
            flex-wrap: wrap;
        }
        .modal-confirm {
            max-width: 680px;
        }
    }

    /* Tablet Portrait (480–767px) */
    @media (min-width: 480px) and (max-width: 767.98px) {
        /* Page header: título arriba, acciones abajo */
        .pagina-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
        }
        /* Filtros en columna */
        .filtros-form {
            flex-direction: column;
        }
        /* Grid de 2 cols del modal → 1 col */
        .grid-dos-columnas {
            grid-template-columns: 1fr !important;
        }
        /* Tabla: scroll horizontal */
        .tarjeta table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
        }
    }

    /* Phone (< 480px) */
    @media (max-width: 479.98px) {
        /* Page header compacto */
        .pagina-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .pagina-header h1 { font-size: 1.2rem; }
        .pagina-header p  { font-size: 0.78rem; }

        /* Botones de excel → horizontal scroll */
        .excel-actions {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }
        /* Botón nuevo producto ancho completo */
        .btn-nuevo {
            width: 100%;
            justify-content: center !important;
        }
        /* Filtros en columna */
        .filtros-form {
            flex-direction: column;
            gap: 0.75rem;
        }
        /* Tabla: scroll horizontal */
        .tarjeta table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            min-width: 600px;
        }
        /* Acciones en tabla: envolver */
        .acciones {
            flex-wrap: wrap;
            max-width: 200px !important;
            gap: 0.3rem !important;
        }
        /* Modal de variantes y adiciones → 1 columna */
        .modal-confirm {
            width: 98%;
            padding: 1.25rem;
            border-radius: 1rem;
            max-height: 92dvh;
            overflow-y: auto;
        }
        .modal-confirm .grid-dos-columnas {
            grid-template-columns: 1fr !important;
        }
        /* Modal eliminar */
        .modal-eliminar-caja {
            width: 96%;
            padding: 1.75rem 1.25rem;
        }
        .modal-eliminar-acciones {
            flex-direction: column;
        }
        .btn-modal-cancelar,
        .btn-modal-eliminar {
            width: 100%;
            min-height: 48px;
        }
        /* Modal footer */
        .modal-footer-large,
        .modal-header-large {
            padding: 0.75rem 1rem;
        }
        .modal-body-scroll {
            padding: 0 !important;
        }
    }
</style>
{{-- MODAL DE IMPORTACIÓN DE EXCEL --}}
<div class="modal-overlay" id="modalImport"
     x-cloak
     x-data="{ show: {{ session('importErrors') || session('importSuccess') || $errors->has('archivoImportacion') ? 'true' : 'false' }} }"
     @abrir-modal-import.window="show = true"
     x-show="show"
     :class="{ 'active': show }">
    <div class="modal-confirm" @click.away="show = false">
        <div class="modal-header-large">
            <h3 class="modal-title">Importar Productos</h3>
            <button type="button" class="btn-cerrar" @click="show = false">✕</button>
        </div>
        <form action="{{ route('admin.productos.importar') }}" method="POST" enctype="multipart/form-data" style="margin: 0; padding: 0;">
            @csrf
            <div class="modal-body-scroll" style="margin-top: 1rem;">
                <p class="modal-desc" style="text-align: left; margin-bottom: 1rem;">
                    Sube tu archivo de Excel con los productos. Asegúrate de usar la plantilla estructurada para evitar errores de formato.
                </p>
                <div class="grupo">
                    <label for="archivoImportacion">Seleccionar Archivo Excel (.xlsx, .xls)</label>
                    <input type="file" id="archivoImportacion" name="archivoImportacion" accept=".xlsx, .xls" required style="width: 100%; border: 1px dashed var(--border); padding: 1rem; border-radius: 8px; background: rgba(0,0,0,0.01);">
                    @error('archivoImportacion') <div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>

                @if(session('importSuccess'))
                    <div class="alerta alerta-exito" style="margin-top: 1rem; padding: 0.75rem; background: rgba(76, 175, 125, 0.1); color: #4caf7d; border-radius: 0.5rem; border: 1px solid rgba(76, 175, 125, 0.2);">
                        {{ session('importSuccess') }}
                    </div>
                @endif

                @if(session('importErrors'))
                    <div class="alerta alerta-error" style="margin-top: 1rem; max-height: 150px; overflow-y: auto; padding: 0.75rem; background: rgba(220, 38, 38, 0.1); color: #f87171; border-radius: 0.5rem; border: 1px solid rgba(220, 38, 38, 0.2);">
                        <ul style="list-style: disc; padding-left: 1.2rem; margin: 0;">
                            @foreach(session('importErrors') as $err)
                                <li style="font-size: 0.8rem; margin-bottom: 0.25rem;">{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="modal-footer-large">
                <button type="button" class="btn-modal btn-modal-cancel" @click="show = false">Cancelar</button>
                <button type="submit" class="btn-modal btn-modal-confirm" style="background: #c9a84c; color: #000;">Importar Archivo</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DE VARIANTES OBLIGATORIAS --}}
<div class="modal-overlay large"
     x-cloak
     x-data="{ show: @entangle('showVariantesModal').live }"
     x-show="show"
     :class="{ 'active': show }">
    <div class="modal-confirm" @click.away="show = false">
        <div class="modal-header-large">
            <h3 class="modal-title">Variantes de: {{ $selectedProductoNombre }}</h3>
            <button type="button" class="btn-cerrar" wire:click="$set('showVariantesModal', false)">✕</button>
        </div>
        <div class="modal-body-scroll">
            <div class="grid-dos-columnas">
                <!-- Formulario de Variante -->
                <div style="position: relative;">
                    <!-- Overlay de Carga -->
                    <div wire:loading.flex wire:target="editVariante" style="position: absolute; inset: -10px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center; border-radius: 8px;">
                        <div style="width: 30px; height: 30px; border: 3px solid rgba(201, 168, 76, 0.2); border-top-color: #c9a84c; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <span style="margin-top: 0.8rem; font-size: 0.85rem; color: var(--text-main, #333); font-weight: 500;">Cargando...</span>
                    </div>

                    <h4 style="margin-bottom: 1rem; color: #c9a84c;">{{ $editingVarianteId ? 'Editar Grupo' : 'Nuevo Grupo de Variantes' }}</h4>
                    <div class="grupo">
                        <label for="nuevaVarianteNombre">Nombre del Grupo (ej: Tamaño, Sabor)</label>
                        <input type="text" id="nuevaVarianteNombre" wire:model="nuevaVarianteNombre" placeholder="Ej: Tamaño">
                        @error('nuevaVarianteNombre') <div class="error-campo">{{ $message }}</div> @enderror
                    </div>
                    <div class="grupo">
                        <div class="toggle-grupo" style="padding: 0.5rem 0.75rem;">
                            <span class="toggle-label" style="font-size: 0.8rem;">Selección obligatoria (Elige una opción)</span>
                            <label class="toggle" style="transform: scale(0.85);">
                                <input type="checkbox" wire:model="nuevaVarianteObligatorio">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: rgba(247, 243, 238, 0.5); text-transform: uppercase;">Opciones de la variante</span>
                        <button type="button" class="btn-editar" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" wire:click="addOpcionToNuevaVariante">+ Añadir opción</button>
                    </div>
                    @error('nuevaVarianteOpciones') <div class="error-campo" style="margin-bottom: 0.5rem;">{{ $message }}</div> @enderror

                    <div style="max-height: 250px; overflow-y: auto; padding-right: 0.25rem;">
                        @foreach($nuevaVarianteOpciones as $index => $opcion)
                            <div class="opcion-row" wire:key="opcion-{{ $index }}">
                                <input type="text" wire:model="nuevaVarianteOpciones.{{ $index }}.nombre" placeholder="Nombre (ej: Mediano)" style="padding: 0.4rem 0.6rem; font-size: 0.8rem;">
                                <input type="number" wire:model="nuevaVarianteOpciones.{{ $index }}.precio" placeholder="Precio" step="0.01" min="0" style="padding: 0.4rem 0.6rem; font-size: 0.8rem;">
                                <select wire:model="nuevaVarianteOpciones.{{ $index }}.tipo_impacto" style="padding: 0.4rem 0.6rem; font-size: 0.8rem; height: auto;">
                                    <option value="incremental">Incremental (+)</option>
                                    <option value="fijo">Precio Fijo (=)</option>
                                </select>
                                <button type="button" class="btn-eliminar-opcion" wire:click="removeOpcionFromNuevaVariante({{ $index }})" title="Eliminar opción">✕</button>
                            </div>
                            @error("nuevaVarianteOpciones.{$index}.nombre") <div class="error-campo" style="display: block; margin-bottom: 0.5rem;">{{ $message }}</div> @enderror
                            @error("nuevaVarianteOpciones.{$index}.precio") <div class="error-campo" style="display: block; margin-bottom: 0.5rem;">{{ $message }}</div> @enderror
                        @endforeach
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                        <button type="button" class="btn-principal" wire:click="saveVariante" style="flex: 1;">
                            {{ $editingVarianteId ? 'Actualizar' : 'Guardar variante' }}
                        </button>
                        @if($editingVarianteId)
                            <button type="button" class="btn-cancelar" wire:click="resetVarianteForm" style="flex: 0.5; padding: 0.8rem; background: rgba(255,255,255,0.05); border-radius: 0.6rem;">Cancelar</button>
                        @endif
                    </div>
                </div>

                <!-- Listado de Variantes Existentes -->
                <div>
                    <h4 style="margin-bottom: 1rem; color: #c9a84c;">Grupos Registrados</h4>
                    @php
                        $selectedProducto = \App\Models\Producto::find($selectedProductoId);
                        $variantesRegistradas = $selectedProducto ? $selectedProducto->variantes : collect();
                    @endphp
                    @if($variantesRegistradas->isEmpty())
                        <div class="vacio" style="padding: 2rem 1rem; font-size: 0.8rem;">No hay grupos de variantes configurados.</div>
                    @else
                        <div class="listado-items-modal" style="max-height: 400px;">
                            @foreach($variantesRegistradas as $var)
                                <div class="item-listado" style="flex-direction: column; align-items: stretch; gap: 0.5rem; padding: 0.75rem;" wire:key="var-reg-{{ $var->id }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <strong style="color: var(--text-main, #333);">{{ $var->nombre }}</strong>
                                        <span class="badge" style="font-size: 0.6rem; padding: 0.15rem 0.4rem; {{ $var->obligatorio ? 'background: rgba(201, 168, 76, 0.15); color: #c9a84c;' : 'background: rgba(0,0,0,0.05); color: var(--text-muted, #666);' }}">
                                            {{ $var->obligatorio ? 'Obligatorio' : 'Opcional' }}
                                        </span>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                        @foreach($var->opciones as $opc)
                                            <span style="font-size: 0.75rem; background: rgba(0,0,0,0.02); border: 1px solid var(--border, #e2e8f0); color: var(--text-main, #333); padding: 0.15rem 0.4rem; border-radius: 0.25rem;">
                                                {{ $opc['nombre'] }} ({{ $opc['tipo_impacto'] == 'incremental' ? '+' : '=' }}${{ number_format($opc['precio'], 2) }})
                                                @if(!($opc['disponible'] ?? true)) <small style="color: #f87171; font-weight: bold;">(Agotado)</small> @endif
                                            </span>
                                        @endforeach
                                    </div>
                                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.25rem; border-top: 1px solid var(--border, #e2e8f0); padding-top: 0.5rem;">
                                        <button type="button" class="btn-editar" wire:click="editVariante('{{ $var->id }}')">Editar</button>
                                        <button type="button" class="btn-eliminar" wire:click="deleteVariante('{{ $var->id }}')">Eliminar</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="modal-footer-large">
            <button type="button" class="btn-modal btn-modal-cancel" wire:click="$set('showVariantesModal', false)" style="width: auto;">Cerrar</button>
        </div>
    </div>
</div>

{{-- MODAL DE ADICIONES --}}
<div class="modal-overlay large"
     x-cloak
     x-data="{ show: @entangle('showAdicionesModal').live }"
     x-show="show"
     :class="{ 'active': show }">
    <div class="modal-confirm" @click.away="show = false">
        <div class="modal-header-large">
            <h3 class="modal-title">Adiciones de: {{ $selectedProductoNombreAdicion }}</h3>
            <button type="button" class="btn-cerrar" wire:click="$set('showAdicionesModal', false)">✕</button>
        </div>
        <div class="modal-body-scroll">
            <div class="grid-dos-columnas">
                <!-- Formulario de Adición -->
                <div style="position: relative;">
                    <!-- Overlay de Carga -->
                    <div wire:loading.flex wire:target="editAdicionSimple" style="position: absolute; inset: -10px; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center; border-radius: 8px;">
                        <div style="width: 30px; height: 30px; border: 3px solid rgba(201, 168, 76, 0.2); border-top-color: #c9a84c; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <span style="margin-top: 0.8rem; font-size: 0.85rem; color: var(--text-main, #333); font-weight: 500;">Cargando...</span>
                    </div>

                    <h4 style="margin-bottom: 1rem; color: #c9a84c;">{{ $editingAdicionId ? 'Editar Adición' : 'Nueva Adición' }}</h4>
                    <div class="grupo">
                        <label for="nuevaAdicionNombre">Nombre (ej: Extra Queso)</label>
                        <input type="text" id="nuevaAdicionNombre" wire:model="nuevaAdicionNombre" placeholder="Ej: Extra Queso">
                        @error('nuevaAdicionNombre') <div class="error-campo">{{ $message }}</div> @enderror
                    </div>
                    <div class="grupo">
                        <label for="nuevaAdicionPrecio">Precio</label>
                        <input type="number" id="nuevaAdicionPrecio" wire:model="nuevaAdicionPrecio" placeholder="0.00" step="0.01" min="0">
                        @error('nuevaAdicionPrecio') <div class="error-campo">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                        <button type="button" class="btn-principal" wire:click="saveNuevaAdicion" style="flex: 1;">
                            {{ $editingAdicionId ? 'Actualizar' : 'Guardar adición' }}
                        </button>
                        @if($editingAdicionId)
                            <button type="button" class="btn-cancelar" wire:click="resetNuevaAdicionForm" style="flex: 0.5; padding: 0.8rem; background: rgba(255,255,255,0.05); border-radius: 0.6rem;">Cancelar</button>
                        @endif
                    </div>
                </div>

                <!-- Listado de Adiciones Existentes -->
                <div>
                    <h4 style="margin-bottom: 1rem; color: #c9a84c;">Adiciones Registradas</h4>
                    @if(empty($adicionesDelProducto))
                        <div class="vacio" style="padding: 2rem 1rem; font-size: 0.8rem;">No hay adiciones configuradas.</div>
                    @else
                        <div class="listado-items-modal" style="max-height: 400px;">
                            @foreach($adicionesDelProducto as $adicion)
                                <div class="item-listado" style="flex-direction: column; align-items: stretch; gap: 0.5rem; padding: 0.75rem;" wire:key="adicion-{{ $adicion['id'] }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <strong style="color: var(--text-main, #333);">{{ $adicion['nombre'] }}</strong>
                                        <span class="badge" style="font-size: 0.75rem; padding: 0.15rem 0.4rem; background: rgba(201, 168, 76, 0.15); color: #c9a84c;">
                                            +${{ number_format($adicion['precio'], 2) }}
                                        </span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem; border-top: 1px solid var(--border, #e2e8f0); padding-top: 0.5rem;">
                                        <div style="display: flex; gap: 0.5rem;">
                                            <button type="button" class="btn-editar" wire:click="editAdicionSimple('{{ $adicion['id'] }}')">Editar</button>
                                            <button type="button" class="btn-eliminar" wire:click="deleteAdicionSimple('{{ $adicion['id'] }}')">Eliminar</button>
                                        </div>
                                        <button type="button" class="badge" style="font-size: 0.72rem; padding: 0.3rem 0.6rem; cursor: pointer; border: none; {{ $adicion['activo'] ? 'background: rgba(76, 175, 125, 0.1); color: #4caf7d;' : 'background: rgba(220, 38, 38, 0.15); color: #f87171;' }}" wire:click="toggleAdicionSimpleActivo('{{ $adicion['id'] }}')">
                                            {{ $adicion['activo'] ? 'Activo' : 'Oculto' }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="modal-footer-large">
            <button type="button" class="btn-modal btn-modal-cancel" wire:click="$set('showAdicionesModal', false)" style="width: auto;">Cerrar</button>
        </div>
    </div>
</div>
</div>
