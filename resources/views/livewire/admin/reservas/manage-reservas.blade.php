@section('titulo', 'Gestión de Reservas')
<div x-data="{ showDetail: false, showCancel: false, activeTab: 'proximas' }">
@vite(['resources/css/pedidos.css'])
<style>
    .drawer-overlay:not(.show),
    .modal-overlay:not(.show) {
        display: none !important;
    }
    .badge-pago-pendiente { background-color: #fef08a; color: #854d0e; }
    .badge-pendiente { background-color: #fed7aa; color: #9a3412; }
    .badge-confirmada { background-color: #bbf7d0; color: #166534; }
    .badge-llegada { background-color: #bfdbfe; color: #1e3a8a; }
    .badge-completada { background-color: #e5e7eb; color: #374151; }
    .badge-cancelada { background-color: #fecaca; color: #991b1b; }
    .badge-no-show { background-color: #fca5a5; color: #7f1d1d; }
    .badge {
        padding: 4px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center;
    }
</style>
<div class="pagina-header">
    <h1>Gestión de Reservas</h1>
    <p>Visualiza y administra las reservas de mesa de tu sucursal.</p>
</div>

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

{{-- NAVEGACIÓN DE PESTAÑAS --}}
<div class="tabs-container">
    <button @click="activeTab = 'proximas'" type="button" class="tab-btn" :class="{ 'activo': activeTab === 'proximas' }">
        Próximas y Activas
    </button>
    <button @click="activeTab = 'pendientes_pago'" type="button" class="tab-btn" :class="{ 'activo': activeTab === 'pendientes_pago' }">
        Pendientes de Pago
    </button>
    <button @click="activeTab = 'historial'" type="button" class="tab-btn" :class="{ 'activo': activeTab === 'historial' }">
        Historial (Cerradas)
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
            <label>FECHA DESDE</label>
            <input type="date" wire:model.live="filtroFechaInicio">
        </div>

        <div class="elegant-group">
            <label>FECHA HASTA</label>
            <input type="date" wire:model.live="filtroFechaFin">
        </div>
        
        <div class="elegant-group">
            <label>ESTADO</label>
            <select wire:model.live="filtroEstado">
                <option value="">Todos los estados</option>
                @foreach($estados as $est)
                    <option value="{{ $est->value }}">{{ $est->etiqueta() }}</option>
                @endforeach
            </select>
        </div>

        <div class="elegant-group">
            <label>MESA</label>
            <select wire:model.live="filtroMesa">
                <option value="">Todas las mesas</option>
                @foreach($mesas as $mesa)
                    <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero }}</option>
                @endforeach
            </select>
        </div>

        <div class="elegant-actions">
            <button type="button" wire:click="limpiarFiltros" class="btn-limpiar" title="Limpiar filtros">✕</button>
        </div>
    </div>
</div>

{{-- TABLAS DE RESULTADOS (CLIENT-SIDE TABS) --}}
@php
    $tabSets = [
        ['id' => 'proximas', 'titulo' => 'Próximas y Activas', 'coleccion' => $reservasProximas],
        ['id' => 'pendientes_pago', 'titulo' => 'Pendientes de Pago', 'coleccion' => $reservasPendientesPago],
        ['id' => 'historial', 'titulo' => 'Historial (Cerradas)', 'coleccion' => $reservasHistorial],
    ];
@endphp

@foreach($tabSets as $tabData)
<div x-show="activeTab === '{{ $tabData['id'] }}'" style="display: none;" x-transition.opacity>
    <div class="tarjeta">
        <div class="tarjeta-header">
            {{ $tabData['titulo'] }} - Resultados: {{ $tabData['coleccion']->count() }} reservas encontradas
        </div>

        @if($tabData['coleccion']->isEmpty())
            <div class="vacio">No se encontraron reservas con los criterios indicados.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Cliente</th>
                        <th>Mesas</th>
                        <th>Personas</th>
                        <th>Depósito</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tabData['coleccion'] as $reserva)
                    <tr>
                        <td style="white-space: nowrap;">
                            <strong>{{ \Carbon\Carbon::parse($reserva->fecha_reserva)->format('d/m/Y') }}</strong><br>
                            <span class="texto-gris">{{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fin, 0, 5) }}</span>
                        </td>
                        <td>
                            <div class="cliente-info-col">
                                <span class="cliente-nombre">{{ $reserva->nombre_cliente }}</span>
                                <span class="cliente-tel">{{ $reserva->telefono_cliente }}</span>
                            </div>
                        </td>
                        <td>
                            @if($reserva->mesas->count() > 0)
                                <span class="meta-badge">Mesa(s): {{ $reserva->mesas->pluck('numero')->join(', ') }}</span>
                            @else
                                <span class="texto-gris">—</span>
                            @endif
                        </td>
                        <td class="texto-gris">{{ $reserva->numero_personas }} pers.</td>
                        <td>
                            @if($reserva->monto_deposito > 0)
                                ${{ number_format($reserva->monto_deposito, 0) }}
                                <br>
                                <small class="texto-gris">{{ $reserva->deposito_pagado ? 'Pagado' : 'Pendiente' }}</small>
                            @else
                                <span class="texto-gris">Sin depósito</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $reserva->estado->colorClase() }}">{{ $reserva->estado->etiqueta() }}</span>
                        </td>
                        <td>
                            <div class="acciones">
                                <button type="button" class="btn-ver" wire:click="openDetailModal('{{ $reserva->id }}')" @click="showDetail = true">
                                    Ver / Gestionar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endforeach

{{-- CAJÓN DE DETALLE LATERAL (DRAWER) --}}
<div class="drawer-overlay" :class="{ 'show': showDetail }" @click="showDetail = false; $wire.closeDetailModal()" wire:ignore.self>
    <div class="drawer-content" :class="{ 'show': showDetail }" @click.stop wire:ignore.self style="background: #ffffff;">
        @if($selectedReserva)
            <div class="drawer-header" style="background: #fdfbf7; border-bottom: 1px solid rgba(44, 36, 27, 0.08);">
                <div>
                    <h2 style="color: #2c241b; font-size: 1.4rem; font-family: 'DM Serif Display', serif;">Reserva {{ $selectedReserva->codigo_reserva }}</h2>
                    <span class="texto-gris" style="font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Creada: {{ $selectedReserva->creado_en?->format('d/m/Y H:i') }}
                    </span>
                </div>
                <button type="button" class="btn-close-drawer" @click="showDetail = false; $wire.closeDetailModal()" style="background: rgba(44,36,27,0.05); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: #2c241b;">✕</button>
            </div>

            <div class="drawer-body" style="padding: 1.5rem 2rem;">
                <!-- Información General -->
                <div class="drawer-section" style="border: 1px solid rgba(44,36,27,0.08); border-radius: 12px; padding: 1.25rem; background: #faf9f6; margin-bottom: 1.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 8px; color: #2c241b; margin-top: 0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Información de la Reserva
                    </h3>
                    <div class="info-grid" style="gap: 1.25rem;">
                        <div>
                            <span class="info-label">Cliente:</span>
                            <span style="font-weight: 600; color: #2c241b; font-size: 0.9rem;">{{ $selectedReserva->nombre_cliente }}</span>
                        </div>
                        <div>
                            <span class="info-label">Teléfono:</span>
                            <span style="color: #2c241b; font-size: 0.9rem;">{{ $selectedReserva->telefono_cliente }}</span>
                        </div>
                        <div>
                            <span class="info-label">Fecha:</span>
                            <span style="color: #2c241b; font-size: 0.9rem;">{{ \Carbon\Carbon::parse($selectedReserva->fecha_reserva)->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <span class="info-label">Horario:</span>
                            <span style="color: #2c241b; font-size: 0.9rem;">{{ substr($selectedReserva->hora_inicio, 0, 5) }} - {{ substr($selectedReserva->hora_fin, 0, 5) }}</span>
                        </div>
                        <div>
                            <span class="info-label">Mesas Asignadas:</span>
                            <span style="color: #2c241b; font-size: 0.9rem; font-weight: 600;">
                                {{ $selectedReserva->mesas->count() > 0 ? $selectedReserva->mesas->pluck('numero')->join(', ') : '—' }}
                            </span>
                        </div>
                        <div>
                            <span class="info-label">Personas:</span>
                            <span style="color: #2c241b; font-size: 0.9rem;">{{ $selectedReserva->numero_personas }} personas</span>
                        </div>
                        <div class="full-width">
                            <span class="info-label">Notas del Cliente:</span>
                            <span style="background: rgba(201, 168, 76, 0.1); padding: 0.75rem; border-radius: 8px; border-left: 3px solid #E07A5F; font-size: 0.85rem; display: block; margin-top: 4px; color: #5a4b3c;">
                                {{ $selectedReserva->notas_cliente ?: 'Ninguna nota adicional proporcionada.' }}
                            </span>
                        </div>
                        <div class="full-width" style="margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px dashed rgba(44,36,27,0.1);">
                            <span class="info-label" style="margin-bottom: 0;">Estado Actual:</span>
                            <span class="badge {{ $selectedReserva->estado->colorClase() }}" style="font-size: 0.8rem; padding: 6px 12px;">{{ $selectedReserva->estado->etiqueta() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Detalle Depósito -->
                <div class="drawer-section" style="border: 1px solid rgba(44,36,27,0.08); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 8px; color: #2c241b; margin-top: 0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Información de Depósito
                    </h3>
                    <div class="info-grid">
                        <div style="background: rgba(44,36,27,0.03); padding: 1rem; border-radius: 8px;">
                            <span class="info-label">Requerido:</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: #2c241b;">${{ number_format($selectedReserva->monto_deposito, 0) }}</span>
                        </div>
                        <div style="background: {{ $selectedReserva->deposito_pagado ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; padding: 1rem; border-radius: 8px;">
                            <span class="info-label" style="color: {{ $selectedReserva->deposito_pagado ? '#059669' : '#d97706' }}">Estado de Pago:</span>
                            <span style="font-size: 1.1rem; font-weight: 600; color: {{ $selectedReserva->deposito_pagado ? '#10b981' : '#f59e0b' }}">
                                {{ $selectedReserva->deposito_pagado ? '✓ Pagado' : '⚠ Pendiente' }}
                            </span>
                        </div>
                    </div>
                    @if($selectedReserva->pagosDeposito && $selectedReserva->pagosDeposito->count() > 0)
                        <div style="margin-top: 1.5rem;">
                            <strong style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Historial de Transacciones:</strong>
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 0.75rem;">
                                @foreach($selectedReserva->pagosDeposito as $pago)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border: 1px solid rgba(44,36,27,0.06); border-radius: 8px; background: #faf9f6;">
                                        <div>
                                            <div style="font-weight: 600; color: #2c241b;">${{ number_format($pago->monto, 0) }} <span style="font-weight: 400; color: #64748b; font-size: 0.8rem;">via {{ ucfirst($pago->metodo) }}</span></div>
                                            @if($pago->referencia)
                                                <div style="font-size: 0.75rem; color: #94a3b8; font-family: monospace;">Ref: {{ $pago->referencia }}</div>
                                            @endif
                                        </div>
                                        <div style="text-align: right;">
                                            <span class="badge {{ $pago->estado === 'aprobado' ? 'badge-confirmada' : 'badge-pendiente' }}">{{ $pago->estado }}</span>
                                            <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 4px;">{{ $pago->creado_en->format('d/m H:i') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Acciones (Admin overrides) -->
                @if(!$selectedReserva->estado->esFinal())
                    <div class="drawer-section actions-section" style="border: none;">
                        <h3 style="display: flex; align-items: center; gap: 8px; color: #2c241b; margin-top: 0; margin-bottom: 1rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Acciones de Administrador
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @if($selectedReserva->estado->value === 'pendiente_pago')
                                <button type="button" wire:click="cambiarEstado('{{ $selectedReserva->id }}', 'confirmada')" wire:confirm="¿Estás seguro de forzar la confirmación de esta reserva sin recibir el depósito vía sistema?" style="background: #10b981; color: white; border: none; padding: 1rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: background 0.2s; font-size: 0.9rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Aprobar Reserva Manualmente
                                </button>
                                <p style="font-size: 0.75rem; color: #94a3b8; text-align: center; margin: 0;">Usa esto si el cliente pagó por fuera del sistema (ej: efectivo).</p>
                            @endif

                            @if(in_array($selectedReserva->estado->value, ['pendiente_pago', 'pendiente', 'confirmada']))
                                <button type="button" @click="showCancel = true" style="background: transparent; color: #ef4444; border: 1px solid #ef4444; padding: 1rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: all 0.2s; font-size: 0.9rem; margin-top: 0.5rem;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancelar Reserva
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($selectedReserva->estado->value === 'cancelada')
                    <div class="drawer-section cancel-reason-section" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239,68,68,0.2); border-left: 4px solid #ef4444; padding: 1.25rem; border-radius: 8px;">
                        <h3 style="color: #b91c1c; margin-top: 0; display: flex; align-items: center; gap: 6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Motivo de Cancelación
                        </h3>
                        <p class="cancel-reason-text" style="color: #991b1b; margin-bottom: 0; font-size: 0.9rem; line-height: 1.5;">"{{ $selectedReserva->motivo_cancelacion ?? 'Sin motivo especificado.' }}"</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- MODAL DE CANCELACIÓN --}}
<div class="modal-overlay" :class="{ 'show': showCancel }" @click="showCancel = false; $wire.closeCancelModal()" wire:ignore.self>
    <div class="modal-content" @click.stop wire:ignore.self>
        <div class="modal-header">
            <h3>Cancelar Reserva</h3>
            <button type="button" class="btn-close-modal" @click="showCancel = false; $wire.closeCancelModal()">✕</button>
        </div>
        <div class="modal-body">
            <p>Por favor, ingresa el motivo de la cancelación. Este motivo se enviará por correo al cliente.</p>
            <textarea wire:model="motivoCancelacion" class="form-control" rows="3" placeholder="Ej: No hay disponibilidad, mantenimiento, etc." style="width:100%; margin-top: 10px;"></textarea>
            @error('motivoCancelacion') <span class="error-msg" style="color:red; font-size:0.8rem;">{{ $message }}</span> @enderror
            <div style="margin-top: 1rem; text-align: right;">
                <button type="button" class="btn-cancel" wire:click="cancelarReserva" wire:loading.attr="disabled">
                    Confirmar Cancelación
                </button>
            </div>
        </div>
    </div>
</div>

</div>
