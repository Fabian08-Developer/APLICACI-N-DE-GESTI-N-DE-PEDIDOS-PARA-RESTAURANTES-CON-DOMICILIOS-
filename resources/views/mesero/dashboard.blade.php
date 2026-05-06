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

    {{-- ── SECCIÓN: EN CURSO ── --}}
    <div class="seccion-label">
        <span>En curso</span>
        <span class="count">{{ count($pedidosActivos) }}</span>
    </div>

    <div class="pedidos-lista" id="lista-activos">
        @forelse($pedidosActivos as $p)
            <div class="dashboard-row" id="row-{{ $p->id }}" onclick="abrirDrawer({{ $p->id }})">
                <div class="pedido-num">#{{ $p->id }}</div>
                
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                        <line x1="6" y1="1" x2="6" y2="4"></line>
                        <line x1="10" y1="1" x2="10" y2="4"></line>
                        <line x1="14" y1="1" x2="14" y2="4"></line>
                    </svg>
                </div>
                <p>No hay pedidos activos en este momento. Los nuevos pedidos aparecerán aquí automáticamente.</p>
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
                        'id'      => $p->id,
                        'estado'  => $p->estado,
                        'total'   => $p->total,
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