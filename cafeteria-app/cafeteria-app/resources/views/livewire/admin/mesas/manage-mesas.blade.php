<div wire:key="manage-mesas-container"
     x-data="{ isOpen: @entangle('showModal').live }"
     @open-sidebar.window="isOpen = true"
     @close-sidebar.window="isOpen = false"
     @close-modal.window="window.dispatchEvent(new CustomEvent('close-sidebar'))">
@vite(['resources/css/mesas.css'])
<style> [x-cloak] { display: none !important; } </style>

{{-- OVERLAY DRAWER --}}
<div class="drawer-overlay" id="drawerOverlay" x-cloak :class="{ 'activo': isOpen }" @click="isOpen = false"></div>

{{-- DRAWER --}}
<div class="drawer" id="drawer" x-cloak :class="{ 'activo': isOpen }">
    <form wire:submit.prevent="save" id="drawerForm" style="display: flex; flex-direction: column; height: 100%;">
        <div class="drawer-cabecera">
            <span class="drawer-titulo" id="drawerTitulo">
                {{ $isEditing ? 'Editar mesa' : 'Nueva mesa' }}
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
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

            <div class="grupo">
                <label for="numero">Número de mesa</label>
                <input type="number" id="numero" wire:model="numero"
                    placeholder="Ej: 1, 2, 3..." min="1" required autofocus>
                @error('numero')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
            <div class="grupo">
                <label for="capacidad">Capacidad <small style="opacity:.5">(opcional)</small></label>
                <input type="number" id="capacidad" wire:model="capacidad"
                    placeholder="Ej: 4 personas" min="1">
                @error('capacidad')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
            <div class="grupo">
                <label for="estado">Estado</label>
                <select id="estado" wire:model="estado" required>
                    <option value="disponible">Disponible</option>
                    <option value="ocupada">Ocupada</option>
                </select>
                @error('estado')<div class="error-campo" style="color: #f87171; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="drawer-footer">
            <button type="submit" class="btn-principal" id="drawerSubmitBtn">
                {{ $isEditing ? 'Guardar cambios' : '+ Crear mesa' }}
            </button>
            <button type="button" class="btn-cancelar" @click="isOpen = false">Cancelar</button>
        </div>
    </form>
</div>

{{-- El modal de eliminación duplicado fue removido para usar la versión reactiva con Alpine.js abajo --}}

{{-- PÁGINA --}}
<div class="pagina-header">
    <div class="pagina-header-texto">
        <h1>Mesas</h1>
        <p>Administra las mesas del restaurante</p>
    </div>
    <button class="btn-nuevo" @click="isOpen = true" wire:click="openCreateModal">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Nueva mesa
    </button>
</div>

<div style="padding: 1rem 1.5rem; margin-bottom: 1.5rem; background: var(--surface); border-radius: 12px; border: 1px solid var(--border);">
    <input wire:model.live.debounce.300ms="search" type="text" 
        style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; background: var(--bg); border: 1px solid var(--border); color: var(--text-main);"
        placeholder="Buscar mesa por número...">
</div>

{{-- GRID DE MESAS --}}
<div class="tarjeta">
    <div class="tarjeta-header">{{ $total }} mesas registradas</div>

    @if($mesas->isEmpty())
        <div class="vacio">No hay mesas todavía. ¡Crea la primera!</div>
    @else
        <div class="mesas-grid">
            @foreach($mesas as $mesa)
                @php
                    $claseCard   = strtolower($mesa->estado);
                    $claseEstado = 'badge-' . strtolower($mesa->estado);
                @endphp
                <div class="mesa-card {{ $claseCard }}">
                    <div class="mesa-numero">{{ $mesa->numero }}</div>
                    <div class="mesa-capacidad">
                        {{ $mesa->capacidad ? $mesa->capacidad . ' personas' : 'Sin capacidad' }}
                    </div>
                    <div>
                        <span class="badge-estado {{ $claseEstado }}">{{ $mesa->estado }}</span>
                    </div>
                    @php
                        $sesionActiva = $mesa->sesionActiva;
                    @endphp
                    @if($sesionActiva)
                        <div style="margin-top: 0.75rem; padding: 0.6rem; background: rgba(196, 139, 87, 0.08); border-radius: 8px; border: 1px dashed rgba(196, 139, 87, 0.2); font-size: 0.8rem; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="color: var(--text-main); font-weight: 500; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; text-align: center;">
                                👤 {{ $sesionActiva->nombre_cliente }}
                            </div>
                            <button type="button" 
                                    class="btn-eliminar" 
                                    style="padding: 0.4rem; font-size: 0.75rem; line-height: 1; border-color: rgba(220, 38, 38, 0.3); background: rgba(220, 38, 38, 0.08); color: #EF4444; cursor: pointer; width: 100%;"
                                    wire:click="confirmarCerrarSesion('{{ $sesionActiva->id }}')">
                                Finalizar mesa
                            </button>
                        </div>
                    @endif

                    <div class="mesa-acciones" style="margin-top: 1.2rem; flex-wrap: wrap;">
                        <button type="button" class="btn-editar"
                            wire:click="edit('{{ $mesa->id }}')" @click="isOpen = true">Editar</button>

                        <button type="button" class="btn-eliminar"
                            @click="$dispatch('abrir-modal-eliminar', { id: '{{ $mesa->id }}', numero: '{{ $mesa->numero }}' })">
                            Eliminar
                        </button>

                        <button type="button" class="btn-editar" wire:click="openQrModal('{{ $mesa->id }}')" @click="$dispatch('abrir-modal-qr')">Ver QR</button>
                    </div>
                </div>
            @endforeach
        </div>
        @if($mesas->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border); margin-top: 1.5rem;">
                {{ $mesas->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    @endif
</div>

{{-- MODAL CONFIRMAR ELIMINAR --}}
<div class="modal-overlay" id="modalEliminarOverlay" x-cloak
     x-data="{ show: false, mesaId: '', mesaNumero: '' }"
     @abrir-modal-eliminar.window="show = true; mesaId = $event.detail.id; mesaNumero = $event.detail.numero;"
     @close-modal.window="show = false"
     :class="{ 'activo': show }">
    <div class="modal-caja" @click.away="show = false">
        <div class="modal-icono">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21
                       c.342.052.682.107 1.022.166m-1.022-.165L18.16
                       19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25
                       2.25 0 0 1-2.244-2.077L4.772 5.79m14.456
                       0a48.108 48.108 0 0 0-3.478-.397m-12 .562
                       c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11
                       0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164
                       -2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18
                       .037-2.09 1.022-2.09 2.201v.916m7.5
                       0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
        </div>
        <h2 class="modal-titulo">¿Eliminar mesa?</h2>
        <p class="modal-mensaje">
            ¿Seguro que deseas eliminar la mesa <strong x-text="mesaNumero"></strong>? Esta acción no se puede deshacer.
        </p>
        <div class="modal-acciones">
            <button type="button" class="btn-modal-eliminar" @click="$wire.delete(mesaId)">
                Sí, eliminar
            </button>
            <button type="button" class="btn-modal-cancelar" @click="show = false">
                Cancelar
            </button>
        </div>
    </div>
</div>

{{-- MODAL VER QR --}}
<div class="modal-overlay" id="modalQrOverlay" x-cloak
     x-data="{ show: @entangle('showQrModal') }"
     @abrir-modal-qr.window="show = true"
     @close-modal.window="show = false"
     :class="{ 'activo': show }">
    <div class="modal-caja" @click.away="show = false" style="max-width: 400px; text-align: center; position: relative;">
        <!-- Overlay de Carga -->
        <div wire:loading.flex wire:target="openQrModal, regenerateQr" style="position: absolute; inset: 0; background: rgba(253, 251, 247, 0.8); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center; border-radius: 1rem;">
            <div style="width: 30px; height: 30px; border: 3px solid rgba(224, 122, 95, 0.2); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <span style="margin-top: 0.8rem; font-size: 0.85rem; color: #2C241B; font-weight: 500;">Generando QR...</span>
        </div>
        <div class="drawer-cabecera" style="border: none; padding: 0 0 1rem 0; background: transparent;">
            <h2 class="modal-titulo" style="font-size: 1.3rem;">Código QR - Mesa {{ $selectedMesa?->numero }}</h2>
            <button type="button" class="btn-cerrar" @click="show = false">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
        
        @if($selectedMesa)
            @php
                $url = route('cliente.qr', [
                    'sucursal_slug' => Auth::user()->sucursal->slug,
                    'codigo' => $selectedMesa->codigo_qr
                ]);
            @endphp
            <div style="background: white; padding: 1.5rem; border-radius: 12px; display: inline-block; margin: 1rem 0; border: 1px solid rgba(255, 255, 255, 0.1);">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->margin(1)->generate($url) !!}
            </div>
            
            <p class="modal-text" style="color: rgba(44, 36, 27, 0.6); font-size: 0.85rem; margin-bottom: 1.5rem; line-height: 1.4;">
                Escanea este código o descárgalo para imprimirlo en tu local.
            </p>
            
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ route('admin.mesas.imprimir-qr', $selectedMesa->id) }}" target="_blank" class="btn-principal" style="text-decoration: none; display: block; text-align: center; line-height: 1;">
                    Descargar PDF para Imprimir
                </a>
                <button type="button" wire:click.stop="regenerateQr('{{ $selectedMesa->id }}')" class="btn-editar" style="width: 100%; margin-top: 0.25rem; font-size: 0.8rem; padding: 0.5rem 1rem;" wire:loading.attr="disabled" wire:target="regenerateQr">
                    <span wire:loading.remove wire:target="regenerateQr">Regenerar Código QR</span>
                    <span wire:loading wire:target="regenerateQr">Actualizando...</span>
                </button>
                <button type="button" class="btn-cancelar" @click="show = false" style="margin-top: 0.5rem;">
                    Cerrar
                </button>
            </div>
        @endif
    </div>
</div>

{{-- MODAL CONFIRMAR CERRAR SESION CLIENTE --}}
<div class="modal-overlay" id="modalCerrarSesionOverlay" x-cloak
     x-data="{ show: @entangle('showCerrarSesionModal') }"
     :class="{ 'activo': show }">
    <div class="modal-caja" @click.away="show = false">
        <div class="modal-icono" style="background: rgba(220, 38, 38, 0.1); color: #EF4444; border-color: rgba(220, 38, 38, 0.25);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.75" stroke="currentColor" style="width: 28px; height: 28px; display: inline-block;">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
            </svg>
        </div>
        <h2 class="modal-titulo">¿Finalizar sesión del cliente?</h2>
        <p class="modal-mensaje" style="color: rgba(44, 36, 27, 0.6); font-size: 0.875rem; margin-top: 0.5rem; line-height: 1.5;">
            ¿Seguro que deseas finalizar la sesión de <strong>{{ $sesionACerrarCliente }}</strong> en la mesa <strong>{{ $sesionACerrarMesa }}</strong>? Se cancelarán los pedidos pendientes de esta sesión.
        </p>
        <div class="modal-acciones" style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <button type="button" class="btn-modal-eliminar" style="background: #dc2626; color: white;" wire:click="cerrarSesionConfirmada">
                Sí, finalizar
            </button>
            <button type="button" class="btn-modal-cancelar" @click="show = false">
                Cancelar
            </button>
        </div>
    </div>
</div>
</div>
