@extends('mesero.layout')

@section('titulo', 'Mis pedidos')

@section('contenido')

<div class="page">
    <header class="page-header">
        <div>
            <h1>Pedidos <span>Activos</span></h1>
            <div class="live-indicator">
                <span class="dot"></span>
                Actualización en vivo
            </div>
        </div>
    </header>

    {{-- ── METRICS BAR (NUEVO) ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: var(--surface); padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(224, 122, 95, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Órdenes Activas</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--text-main); font-weight: 700; line-height: 1;">{{ count($pedidosActivos) }}</div>
            </div>
        </div>
        
        @php
            $totalVentas = $pedidosActivos->sum('total');
            $tiempoPromedio = count($pedidosActivos) > 0 ? '12 min' : '—'; // Simulated
        @endphp

        <div style="background: var(--surface); padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(129, 178, 154, 0.1); color: var(--status-success); display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Ventas del Turno</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--text-main); font-weight: 700; line-height: 1;">${{ number_format($totalVentas, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="background: var(--surface); padding: 1.5rem; border-radius: var(--radius); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(61, 90, 128, 0.1); color: var(--status-info); display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Tiempo Promedio</div>
                <div style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--text-main); font-weight: 700; line-height: 1;">{{ $tiempoPromedio }}</div>
            </div>
        </div>
    </div>

    {{-- ── SECCIÓN: EN CURSO ── --}}
    <div class="seccion-label">
        <span>En curso</span>
        <span class="count">{{ count($pedidosActivos) }}</span>
    </div>

    <div class="pedidos-lista" id="lista-activos">
        @forelse($pedidosActivos as $p)
            <div class="dashboard-row" id="row-{{ $p->id }}" onclick="abrirDrawer('{{ $p->id }}')">
                <div class="pedido-num">#{{ $p->short_id }}</div>
                
                <div class="pedido-info">
                    <div class="pedido-mesa">Mesa {{ $p->sesionMesa?->mesa?->numero ?? '—' }}</div>
                    <div class="pedido-meta">
                        <span>{{ $p->created_at?->format('g:i A') }}</span>
                        <span>•</span>
                        <span>{{ count($p->detalles) }} items</span>
                    </div>
                </div>

                <div class="pedido-total">
                    ${{ number_format($p->total, 0, ',', '.') }}
                    <small>Total cobrado</small>
                </div>

                <div class="pedido-estado">
                    <span class="estado-badge estado-{{ $p->estado }}">
                        {{ str_replace('_', ' ', $p->estado) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="estado-vacio">
                <div class="icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                </div>
                <h3>La sala está tranquila</h3>
                <p>No hay pedidos activos en este momento. Es un buen momento para revisar las mesas o preparar estaciones.</p>
                <a href="{{ route('mesero.tomar-pedido.mesas') }}" class="btn-link">
                    Tomar un pedido
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        @endforelse
    </div>
</div>

{{-- ── OVERLAY Y DRAWER ── --}}
<div class="drawer-overlay" id="overlay" onclick="cerrarDrawer()"></div>

<div class="drawer" id="drawer">
    <div class="drawer-header">
        <div>
            <div class="drawer-num" id="drawer-num">#000 <small>Detalle del pedido</small></div>
            <div id="drawer-badge"></div>
        </div>
        <button class="btn-cerrar-drawer" onclick="cerrarDrawer()">
            ✕
        </button>
    </div>

    <div class="drawer-body">
        <div class="drawer-meta">
            <div class="meta-item">
                <label>Ubicación</label>
                <span id="drawer-mesa">—</span>
            </div>
            <div class="meta-item">
                <label>Atendido por</label>
                <span id="drawer-mesero">—</span>
            </div>
            <div class="meta-item">
                <label>Hora pedido</label>
                <span id="drawer-hora">—</span>
            </div>
            <div class="meta-item">
                <label>Estado actual</label>
                <span id="drawer-estado-label">—</span>
            </div>
        </div>

        <div class="productos-titulo">Productos</div>
        <div id="drawer-productos"></div>
        
        <div class="drawer-total">
            <span class="drawer-total-label">Total</span>
            <span class="drawer-total-monto" id="drawer-total">$0</span>
        </div>
    </div>

    <div class="drawer-footer" id="drawer-footer"></div>
</div>

{{-- ── MODAL CANCELACIÓN ── --}}
<div class="modal-overlay" id="modal-cancelar">
    <div class="modal-content">
        <h3 class="modal-title">¿Cancelar pedido?</h3>
        <p class="modal-desc">Confirma si deseas cancelar este pedido. Se notificará al cliente y se procesará el reembolso.</p>
        
        <div class="modal-form-group">
            <label>Motivo</label>
            <textarea id="motivo_cancelacion" rows="3" placeholder="Opcional..."></textarea>
        </div>

        <div class="modal-actions">
            <button class="btn-modal-secundario" onclick="cerrarModalCancelacion()">Volver</button>
            <button class="btn-modal-peligro" id="btn-confirmar-cancelacion" onclick="ejecutarCancelacion()">Confirmar</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initMesero === 'function') {
            initMesero({
                csrf: '{{ csrf_token() }}',
                pedidos: {!! json_encode(
                    $pedidosActivos->map(fn($p) => [
                        'id'       => $p->id,
                        'short_id' => $p->short_id,
                        'estado'   => $p->estado,
                        'total'    => $p->total,
                        'mesa'    => $p->sesionMesa?->mesa?->numero ?? '—',
                        'hora'    => $p->created_at?->format('g:i A'),
                        'mesero'  => $p->mesero?->nombre ?? '—',
                        'detalles'=> $p->detalles->map(fn($d) => [
                            'nombre'   => $d->producto?->nombre ?? '—',
                            'cantidad' => $d->cantidad,
                            'notas'    => $d->notas ?? null,
                            'subtotal' => number_format($d->subtotal, 0, ',', '.'),
                        ]),
                    ])
                ) !!}
            });
        }
    });
</script>

@endsection