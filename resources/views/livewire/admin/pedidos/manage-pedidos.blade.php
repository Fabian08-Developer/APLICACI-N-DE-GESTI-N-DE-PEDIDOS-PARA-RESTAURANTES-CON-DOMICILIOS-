@section('titulo', 'Gestión de Pedidos')
<div x-data="{ showDetail: false }" @open-detail-modal.window="showDetail = true" @close-detail-modal.window="showDetail = false">
@vite(['resources/css/pedidos.css'])
<style>
    .drawer-overlay:not(.show),
    .modal-overlay:not(.show) {
        display: none !important;
    }
</style>
<div class="pagina-header">
    <h1>Gestión de Pedidos</h1>
    <p>Visualiza, filtra y administra el flujo de pedidos locales y a domicilio de la sucursal.</p>
</div>

{{-- ALERTAS DE ÉXITO O ERROR --}}
@if (session()->has('success'))
    <div class="alerta alerta-success">
        <span class="alerta-icon">✓</span>
        <span class="alerta-message">{{ session('success') }}</span>
    </div>
@endif
@if (session()->has('error'))
    <div class="alerta alerta-danger">
        <span class="alerta-icon">✕</span>
        <span class="alerta-message">{{ session('error') }}</span>
    </div>
@endif

{{-- ESTADÍSTICAS RÁPIDAS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-hoy">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $totalPedidosHoy }}</span>
            <span class="stat-label">Pedidos Hoy</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-pendientes">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $pendientesHoy }}</span>
            <span class="stat-label">Pendientes Hoy</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-completados">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $completadosHoy }}</span>
            <span class="stat-label">Entregados Hoy</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-activos">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div class="stat-info">
            <span class="stat-value">{{ $cantDomiciliosActivos }}</span>
            <span class="stat-label">Domicilios Activos</span>
        </div>
    </div>
</div>

{{-- NAVEGACIÓN DE PESTAÑAS --}}
<div class="tabs-container">
    <button wire:click="setTab('local')" class="tab-btn {{ $tab === 'local' ? 'activo' : '' }}" wire:loading.class="opacity-50 pointer-events-none">
        <svg wire:loading.remove wire:target="setTab('local')" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span wire:loading wire:target="setTab('local')" style="width: 16px; height: 16px; border: 2px solid #ccc; border-top-color: #f97316; border-radius: 50%; animation: spin 1s linear infinite;"></span>
        Pedidos de Salón
    </button>
    <button wire:click="setTab('domicilio')" class="tab-btn {{ $tab === 'domicilio' ? 'activo' : '' }}" wire:loading.class="opacity-50 pointer-events-none">
        <svg wire:loading.remove wire:target="setTab('domicilio')" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span wire:loading wire:target="setTab('domicilio')" style="width: 16px; height: 16px; border: 2px solid #ccc; border-top-color: #f97316; border-radius: 50%; animation: spin 1s linear infinite;"></span>
        Historial Domicilios
    </button>
    <button wire:click="setTab('domicilios_activos')" class="tab-btn {{ $tab === 'domicilios_activos' ? 'activo' : '' }}" wire:loading.class="opacity-50 pointer-events-none">
        <svg wire:loading.remove wire:target="setTab('domicilios_activos')" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span wire:loading wire:target="setTab('domicilios_activos')" style="width: 16px; height: 16px; border: 2px solid #ccc; border-top-color: #f97316; border-radius: 50%; animation: spin 1s linear infinite;"></span>
        Domicilios Activos
        @if($cantDomiciliosActivos > 0)
            <span class="tab-badge" wire:loading.remove wire:target="setTab('domicilios_activos')">{{ $cantDomiciliosActivos }}</span>
        @endif
    </button>
</div>

{{-- FILTROS AVANZADOS REACTIVOS --}}
<div class="elegant-filter-card">
    <div class="filter-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        FILTROS DE BÚSQUEDA
    </div>
    
    <div class="elegant-filter-grid">
        <div class="elegant-group">
            <label>DESDE</label>
            <input type="date" wire:model.live="filtroFechaInicio">
        </div>

        <div class="elegant-group">
            <label>HASTA</label>
            <input type="date" wire:model.live="filtroFechaFin">
        </div>
        
        <div class="elegant-group">
            <label>ESTADO</label>
            <select wire:model.live="filtroEstado">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE_PAGO">Pendiente de Pago</option>
                <option value="CREADO">Creado</option>
                <option value="EN_PREPARACION">En Preparación</option>
                <option value="LISTO">Listo</option>
                <option value="ENTREGADO">Entregado</option>
                <option value="CANCELADO">Cancelado</option>
            </select>
        </div>

        @if($tab === 'local')
            <div class="elegant-group">
                <label>MESA</label>
                <select wire:model.live="filtroMesa">
                    <option value="">Todas las mesas</option>
                    @foreach($mesas as $mesa)
                        <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero }}</option>
                    @endforeach
                </select>
            </div>

            <div class="elegant-group">
                <label>MESERO</label>
                <select wire:model.live="filtroMesero">
                    <option value="">Todos los meseros</option>
                    @foreach($meseros as $mesero)
                        <option value="{{ $mesero->id }}">{{ $mesero->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="elegant-group">
                <label>DOMICILIARIO</label>
                <select wire:model.live="filtroDomiciliario">
                    <option value="">Todos los domiciliarios</option>
                    @dump(gettype($domiciliarios))
                    @foreach($domiciliarios as $dom)
                        <option value="{{ is_object($dom) ? $dom->id : 'error' }}">{{ is_object($dom) ? $dom->nombre : 'error' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="elegant-group">
                <label>ZONA</label>
                <select wire:model.live="filtroZona">
                    <option value="">Todas las zonas</option>
                    @foreach($zonas as $z)
                        <option value="{{ $z->id }}">{{ $z->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="elegant-actions">
            <button type="button" wire:click="limpiarFiltros" class="btn-limpiar" title="Limpiar filtros">✕</button>
        </div>
    </div>
</div>

{{-- TABLA DE RESULTADOS --}}
<div class="tarjeta">
    <div class="tarjeta-header">
        Resultados: {{ $pedidos->total() }} pedidos encontrados (página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }})
    </div>

    @if($pedidos->isEmpty())
        <div class="vacio">No se encontraron pedidos con los criterios indicados.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @if($tab === 'local')
                        <th>Mesa</th>
                        <th>Mesero</th>
                    @else
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Domiciliario</th>
                        <th>Zona</th>
                    @endif
                    <th>Items</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                @php
                    $claseEstado = 'badge-' . strtolower(str_replace('_', '_', $pedido->estado));
                @endphp
                <tr>
                    <td class="texto-gris">#{{ substr($pedido->id, 0, 8) }}</td>
                    
                    @if($tab === 'local')
                        <td>Mesa {{ $pedido->sesionCliente?->mesa?->numero ?? '—' }}</td>
                        <td>{{ $pedido->mesero?->nombre ?? '—' }}</td>
                    @else
                        <td>
                            <div class="cliente-info-col">
                                <span class="cliente-nombre">{{ $pedido->sesionCliente?->nombre_cliente ?? '—' }}</span>
                                <span class="cliente-tel">{{ $pedido->sesionCliente?->telefono_cliente ?? '' }}</span>
                            </div>
                        </td>
                        <td class="direccion-col" title="{{ $pedido->direccion_entrega }}">
                            {{ Str::limit($pedido->direccion_entrega ?? '—', 35) }}
                        </td>
                        <td>
                            @if($pedido->domiciliario)
                                <span class="domiciliario-badge">{{ $pedido->domiciliario->nombre }}</span>
                            @else
                                <span class="sin-asignar">Sin Asignar</span>
                            @endif
                        </td>
                        <td>{{ $pedido->zona?->nombre ?? '—' }}</td>
                    @endif

                    <td class="texto-gris">{{ $pedido->detalles_count }} items</td>
                    <td class="precio">${{ number_format($pedido->total, 2) }}</td>
                    <td>
                        <span class="badge {{ $claseEstado }}">{{ $pedido->estado }}</span>
                    </td>
                    <td class="texto-gris">{{ $pedido->creado_en?->format('d/m H:i') ?? 'N/A' }}</td>
                    <td>
                        <div class="acciones">
                            <button type="button" class="btn-ver" wire:click="openDetailModal('{{ $pedido->id }}')" @click="showDetail = true">
                                Ver Detalle
                            </button>
                            @if($pedido->tipo === 'domicilio' && !$pedido->perfil_domiciliario_id && $pedido->estado !== 'CANCELADO' && $pedido->estado !== 'ENTREGADO')
                                <button type="button" class="btn-asignar" wire:click="openAsignarModal('{{ $pedido->id }}')">
                                    Asignar
                                </button>
                                <button type="button" class="btn-asignar" wire:click="autoAsignar('{{ $pedido->id }}')"
                                    title="Asignación automática basada en disponibilidad, zona y carga de trabajo"
                                    style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                                    ⚡ Auto
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- PAGINACIÓN --}}
        <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(0,0,0,0.06);">
            {{ $pedidos->links() }}
        </div>
    @endif
</div>

{{-- CAJÓN DE DETALLE LATERAL (DRAWER) --}}
<div class="drawer-overlay" :class="{ 'show': showDetail }" @click="showDetail = false; $wire.closeDetailModal()" wire:ignore.self>
    <div class="drawer-content" :class="{ 'show': showDetail }" wire:ignore.self>
        <div wire:loading.flex wire:target="openDetailModal" style="position: absolute; inset: 0; background: rgba(253, 251, 247, 0.8); z-index: 50; display: none; flex-direction: column; align-items: center; justify-content: center;">
            <div style="width: 40px; height: 40px; border: 3px solid rgba(224, 122, 95, 0.3); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 1rem; color: #2C241B; font-weight: 500;">Cargando pedido...</p>
        </div>

        @if($selectedPedido)
            <div class="drawer-header">
                <div>
                    <h2>Pedido #{{ substr($selectedPedido->id, 0, 8) }}</h2>
                    <span class="texto-gris">Creado: {{ $selectedPedido->creado_en?->format('d/m/Y H:i') }}</span>
                </div>
                <button type="button" class="btn-close-drawer" @click="showDetail = false; $wire.closeDetailModal()">✕</button>
            </div>

            <div class="drawer-body">
                <!-- Información General -->
                <div class="drawer-section">
                    <h3>Información del Pedido</h3>
                    <div class="info-grid">
                        <div>
                            <span class="info-label">Tipo:</span>
                            <span class="badge {{ $selectedPedido->tipo === 'local' ? 'badge-local' : 'badge-domicilio' }}">
                                {{ $selectedPedido->tipo === 'local' ? 'Salón' : 'A Domicilio' }}
                            </span>
                        </div>
                        <div>
                            <span class="info-label">Estado:</span>
                            <span class="badge badge-{{ strtolower($selectedPedido->estado) }}">{{ $selectedPedido->estado }}</span>
                        </div>

                        @if($selectedPedido->tipo === 'local')
                            <div>
                                <span class="info-label">Mesa:</span>
                                <span>Mesa {{ $selectedPedido->sesionCliente?->mesa?->numero ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="info-label">Mesero:</span>
                                <span>{{ $selectedPedido->mesero?->nombre ?? '—' }}</span>
                            </div>
                        @else
                            <div>
                                <span class="info-label">Cliente:</span>
                                <span>{{ $selectedPedido->sesionCliente?->nombre_cliente ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="info-label">Teléfono:</span>
                                <span>{{ $selectedPedido->sesionCliente?->telefono_cliente ?? '—' }}</span>
                            </div>
                            <div class="full-width">
                                <span class="info-label">Dirección:</span>
                                <span>{{ $selectedPedido->direccion_entrega ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="info-label">Zona:</span>
                                <span>{{ $selectedPedido->zona?->nombre ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="info-label">Domiciliario:</span>
                                <div class="domiciliario-select-action">
                                    <span>{{ $selectedPedido->domiciliario?->nombre ?? 'Sin Asignar' }}</span>
                                    @if($selectedPedido->estado !== 'CANCELADO' && $selectedPedido->estado !== 'ENTREGADO')
                                        <button type="button" class="btn-action-mini" wire:click="openAsignarModal('{{ $selectedPedido->id }}')">
                                            Asignar / Cambiar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detalles de Productos -->
                <div class="drawer-section">
                    <h3>Detalle de Productos</h3>
                    <div class="items-list">
                        @foreach($selectedPedido->detalles as $det)
                            <div class="item-card">
                                <div class="item-header">
                                    <span class="item-name">{{ $det->cantidad }}x {{ $det->nombre_producto }}</span>
                                    <span class="item-subtotal">${{ number_format($det->subtotal, 2) }}</span>
                                </div>
                                
                                @if(!empty($det->variantes_elegidas) && is_array($det->variantes_elegidas))
                                    <div class="item-meta">
                                        <strong>Variantes:</strong>
                                        @foreach($det->variantes_elegidas as $k => $v)
                                            <span class="meta-badge">{{ is_array($v) ? ($v['nombre'] ?? '').': '.($v['opcion'] ?? '') : $k.': '.$v }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                @if(!empty($det->adiciones_elegidas) && is_array($det->adiciones_elegidas))
                                    <div class="item-meta">
                                        <strong>Adiciones:</strong>
                                        @foreach($det->adiciones_elegidas as $ad)
                                            <span class="meta-badge">+ {{ is_array($ad) ? ($ad['nombre'] ?? '') : $ad }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($det->notas)
                                    <div class="item-notes">
                                        <span>Nota: {{ $det->notas }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Desglose de Totales -->
                <div class="drawer-section totals-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>${{ number_format($selectedPedido->subtotal, 2) }}</span>
                    </div>
                    @if($selectedPedido->tipo === 'domicilio')
                        <div class="total-row">
                            <span>Costo de Envío:</span>
                            <span>${{ number_format($selectedPedido->costo_envio, 2) }}</span>
                        </div>
                    @endif
                    <div class="total-row grand-total">
                        <span>Total:</span>
                        <span>${{ number_format($selectedPedido->total, 2) }}</span>
                    </div>
                </div>

                <!-- Línea de Tiempo del Pedido -->
                <div class="drawer-section">
                    <h3>Línea de Tiempo</h3>
                    <div class="timeline">
                        @foreach($selectedPedido->historial as $hist)
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-state">{{ $hist->estado }}</div>
                                    <div class="timeline-meta">
                                        <span>Por: {{ $hist->usuario?->nombre ?? 'Sistema' }}</span>
                                        <span>{{ $hist->cambiado_en?->format('d/m H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Acciones del Pedido -->
                @if($selectedPedido->estado !== 'ENTREGADO' && $selectedPedido->estado !== 'CANCELADO')
                    <div class="drawer-section actions-section">
                        <h3>Cambiar Estado</h3>
                        <div class="actions-buttons">
                            @if($selectedPedido->estado === 'PENDIENTE_PAGO')
                                <button type="button" class="btn-action-primary" wire:click="cambiarEstado('{{ $selectedPedido->id }}', 'CREADO')">
                                    Confirmar Pago / Activar
                                </button>
                            @elseif($selectedPedido->estado === 'CREADO')
                                <button type="button" class="btn-action-primary" wire:click="cambiarEstado('{{ $selectedPedido->id }}', 'EN_PREPARACION')">
                                    Iniciar Preparación
                                </button>
                            @elseif($selectedPedido->estado === 'EN_PREPARACION')
                                <button type="button" class="btn-action-primary" wire:click="cambiarEstado('{{ $selectedPedido->id }}', 'LISTO')">
                                    Marcar como Listo
                                </button>
                            @elseif($selectedPedido->estado === 'LISTO')
                                <button type="button" class="btn-action-primary" wire:click="cambiarEstado('{{ $selectedPedido->id }}', 'ENTREGADO')">
                                    Confirmar Entrega
                                </button>
                            @endif
                        </div>

                        <!-- Formulario de Cancelación -->
                        <div class="cancel-form">
                            <h4>Cancelar Pedido</h4>
                            <div class="cancel-input-group">
                                <input type="text" wire:model="motivoCancelacion" placeholder="Escribe el motivo del rechazo/cancelación...">
                                <button type="button" class="btn-cancel" wire:click="cancelarPedido('{{ $selectedPedido->id }}')">
                                    Cancelar Pedido
                                </button>
                            </div>
                            @error('motivoCancelacion') <span class="error-msg">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @elseif($selectedPedido->estado === 'CANCELADO')
                    <div class="drawer-section cancel-reason-section">
                        <h3>Motivo de Cancelación</h3>
                        <p class="cancel-reason-text">"{{ $selectedPedido->motivo_cancelacion ?? 'Sin motivo especificado.' }}"</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- MODAL DE ASIGNACIÓN DE DOMICILIARIO --}}
<div class="modal-overlay {{ $showAsignarModal ? 'show' : '' }}" wire:click.self="closeAsignarModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Asignar Domiciliario</h3>
            <button type="button" class="btn-close-modal" wire:click="closeAsignarModal">✕</button>
        </div>
        <div class="modal-body">
            @if($domiciliarios && (is_array($domiciliarios) || is_object($domiciliarios)) && (is_object($domiciliarios) ? $domiciliarios->isEmpty() : empty($domiciliarios)))
                <p class="texto-gris text-center py-4">No hay domiciliarios disponibles registrados en esta sucursal.</p>
            @else
                <div class="drivers-list">
                    @dump('Modal gettype:', gettype($domiciliarios))
                    @foreach($domiciliarios as $dom)
                        @if(is_object($dom))
                            @php
                                $claseEstadoDriver = match($dom->estado) {
                                    'disponible' => 'driver-disponible',
                                    'en_ruta' => 'driver-en-ruta',
                                    'ocupado' => 'driver-ocupado',
                                    default => 'driver-no-disponible',
                                };
                            @endphp
                            <div class="driver-card">
                                <div class="driver-info">
                                    <div class="driver-avatar">{{ $dom->iniciales }}</div>
                                    <div>
                                        <div class="driver-name">{{ $dom->nombre }}</div>
                                        <div class="driver-meta">
                                            <span>Vehículo: {{ ucfirst($dom->tipo_vehiculo) }} ({{ $dom->placa ?? 'N/A' }})</span> • 
                                            <span class="driver-status {{ $claseEstadoDriver }}">{{ ucfirst($dom->estado) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-assign-driver" wire:click="asignarDomiciliario('{{ $dom->id }}')">
                                    Asignar
                                </button>
                            </div>
                        @else
                            @dump('Error: Element is not an object', $dom)
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

</div>
