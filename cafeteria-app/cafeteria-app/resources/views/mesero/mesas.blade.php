@extends('mesero.layout')

@section('titulo', 'Gestión de Mesas')

@section('contenido')

{{-- ── Encabezado ── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Gestión de Mesas</h1>
        <p class="page-sub">Panel de control y despacho por mesa</p>
    </div>
    
    <div class="header-stats">
        <div class="stat-pill">
            <span class="stat-value">{{ $resumenMesas['total_mesas'] }}</span>
            <span class="stat-label">Mesas Activas</span>
        </div>
        <div class="stat-pill stat-pill--gold">
            <span class="stat-value">{{ $resumenMesas['pedidos_activos'] }}</span>
            <span class="stat-label">Pedidos en Proceso</span>
        </div>
        @if($resumenMesas['listos_entregar'] > 0)
        <div class="stat-pill stat-pill--green">
            <span class="stat-value">{{ $resumenMesas['listos_entregar'] }}</span>
            <span class="stat-label">Listos para Entrega</span>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     Estado Vacío
══════════════════════════════════════════════════════════════════════════ --}}
@if($mesasConPedidos->isEmpty())
    <div class="estado-vacio">
        <div class="icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <rect x="3" y="8" width="18" height="4" rx="1"/>
                <line x1="7"  y1="12" x2="7"  y2="19"/>
                <line x1="17" y1="12" x2="17" y2="19"/>
                <line x1="5"  y1="5"  x2="19" y2="5"/>
            </svg>
        </div>
        <p>No tienes mesas con sesiones activas asignadas en este momento.</p>
        <a href="{{ route('mesero.dashboard') }}" class="btn-link">Ver pedidos recientes →</a>
    </div>
@else

{{-- ══════════════════════════════════════════════════════════════════════════
     Grid de mesas
══════════════════════════════════════════════════════════════════════════ --}}
<div class="mesas-grid">
    @foreach($mesasConPedidos as $mesa)
    @php
        $sesiones    = $mesa->sesionesActivasMesero;
        $tieneListos = $sesiones->flatMap->pedidos->where('estado', 'LISTO')->isNotEmpty();
    @endphp
    <div class="mesa-card {{ $tieneListos ? 'mesa-card--alerta' : '' }}">

        {{-- Cabecera de mesa --}}
        <div class="mesa-card-header">
            <div style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div class="mesa-numero">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="8" width="18" height="4" rx="1"/>
                            <line x1="7"  y1="12" x2="7"  y2="19"/>
                            <line x1="17" y1="12" x2="17" y2="19"/>
                            <line x1="5"  y1="5"  x2="19" y2="5"/>
                        </svg>
                        Mesa {{ $mesa->numero }}
                    </div>
                    <span class="badge-estado badge-estado--{{ strtolower($mesa->estado) }}">
                        {{ $mesa->estado }}
                    </span>
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    {{-- Este botón ahora tiene un propósito preventivo/reseteo --}}
                    <button type="button" 
                            class="btn-liberar-mesa btn-abrir-modal-confirm" 
                            data-tipo="mesa"
                            data-mesa-num="{{ $mesa->numero }}"
                            data-url="{{ route('mesero.mesas.liberar', $mesa->id) }}">
                        ✕ Liberar mesa
                    </button>
                </div>
            </div>
        </div>

        {{-- Sesiones y sus pedidos --}}
        <div class="mesa-pedidos">
            @foreach($sesiones as $sesion)
            <div class="sesion-container" style="margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 1rem; background: rgba(255,255,255,0.02); position: relative; z-index: 1;">
                <div class="sesion-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Cliente / Grupo</span>
                        <code style="background: rgba(255,255,255,0.05); padding: 0.2rem 0.5rem; border-radius: 6px; font-family: monospace; font-size: 0.85rem; color: var(--primary); border: 1px solid rgba(255,255,255,0.05);">{{ substr($sesion->token, 0, 8) }}</code>
                    </div>
                    <button type="button" 
                            class="btn-cerrar-sesion btn-abrir-modal-confirm" 
                            data-tipo="sesion"
                            data-mesa-num="{{ $mesa->numero }}"
                            data-url="{{ route('mesero.sesiones.cerrar', $sesion->id) }}"
                            style="background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); font-size: 0.7rem; padding: 0.35rem 0.75rem; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s;">
                        Finalizar Sesión
                    </button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                    @foreach($sesion->pedidos as $pedido)
                    <div class="pedido-row" style="background: var(--bg-main); border: 1px solid var(--border); border-radius: 8px; padding: 0.85rem; transition: all 0.2s ease;">
                        <div class="pedido-row-top" style="display: flex; justify-content: space-between; margin-bottom: 0.6rem;">
                            <span class="pedido-id" style="font-size: 0.8rem; font-weight: 700; color: #8bb1ff;">#{{ $pedido->id }}</span>
                            <span class="pedido-badge pedido-badge--{{ strtolower(str_replace('_','-',$pedido->estado)) }}" style="font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 999px;">
                                {{ str_replace('_',' ', $pedido->estado) }}
                            </span>
                        </div>
                        <ul class="pedido-items" style="list-style: none; padding: 0; margin: 0 0 0.75rem 0; font-size: 0.85rem; color: var(--text-main); font-weight: 500;">
                            @foreach($pedido->detalles->take(3) as $detalle)
                            <li style="margin-bottom: 0.25rem;">{{ $detalle->cantidad }}× <span style="color: var(--text-muted);">{{ $detalle->producto->nombre }}</span></li>
                            @endforeach
                        </ul>
                        <div class="pedido-row-footer" style="display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 0.75rem;">
                            <span class="pedido-total" style="font-weight: 800; font-size: 0.95rem;">${{ number_format($pedido->total, 0, ',', '.') }}</span>
                            @if($pedido->estado === 'LISTO')
                            <form method="POST" action="{{ route('mesero.pedidos.entregar', $pedido->id) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" style="background: linear-gradient(135deg, #10B981, #059669); color: white; border: none; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(16,185,129,0.3); transition: all 0.2s;">
                                    Entregar
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

    </div>
    @endforeach
</div>

@endif

{{-- ── Estilos locales ── --}}
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .page-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; margin: 0; color: var(--text-main); }
    .page-sub { font-size: 0.9rem; color: var(--text-muted); margin: 0; }
    .header-stats { display: flex; gap: 1rem; }
    .stat-pill { background: var(--surface); border: 1px solid var(--border-light); padding: 0.6rem 1.2rem; border-radius: var(--radius-sm); text-align: center; box-shadow: var(--shadow-sm); }
    .stat-value { display: block; font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: var(--text-main); }
    .stat-label { font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }

    .mesas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem; }
    
    .mesa-card { 
        background: var(--surface);
        border: 1px solid var(--border-light); 
        border-radius: var(--radius); 
        display: flex; 
        flex-direction: column; 
        overflow: hidden; 
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        box-shadow: var(--shadow-sm);
    }
    
    .mesa-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 50% 0%, var(--surface-2), transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .mesa-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--border);
    }
    
    .mesa-card:hover::before { opacity: 1; }
    
    .mesa-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-light); position: relative; z-index: 1; background: var(--surface-2); }
    .mesa-numero { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 0.6rem; color: var(--text-main); }
    .mesa-numero svg { width: 22px; height: 22px; color: var(--primary); }
    
    .badge-estado { font-size: 0.7rem; padding: 0.35rem 0.8rem; border-radius: 100px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .badge-estado--ocupada { background: rgba(224, 122, 95, 0.1); color: var(--primary); }
    .badge-estado--disponible { background: rgba(129, 178, 154, 0.1); color: var(--status-success); }

    .btn-liberar-mesa { background: var(--surface); border: 1px solid var(--border-light); color: var(--text-dim); padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-sm); }
    .btn-liberar-mesa:hover { background: rgba(224, 122, 95, 0.1); color: var(--primary); border-color: rgba(224, 122, 95, 0.3); }

    .mesa-pedidos { padding: 1.5rem; flex-grow: 1; position: relative; z-index: 1; background: var(--surface); }
    .pedido-row { transition: all 0.2s ease; }
    .pedido-row:hover { border-color: var(--border); transform: translateX(2px); box-shadow: var(--shadow-sm); }
    
    .pedido-badge--creado { background: rgba(242, 204, 143, 0.2); color: #B48A36; }
    .pedido-badge--en-cocina { background: rgba(224, 122, 95, 0.1); color: var(--primary); }
    .pedido-badge--en-preparacion { background: rgba(224, 122, 95, 0.1); color: var(--primary); }
    .pedido-badge--listo { background: rgba(129, 178, 154, 0.2); color: #3A7A5D; }
</style>

{{-- ── MODAL DE CONFIRMACIÓN ÚNICO ── --}}
<div class="modal-overlay" id="modalConfirmacion">
    <div class="modal-confirm">
        <div class="modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h3 class="modal-title" id="confirmTitle">¿Confirmar acción?</h3>
        <p class="modal-desc" id="confirmDesc">¿Estás seguro de realizar esta operación?</p>
        
        <form id="formConfirm" method="POST" action="">
            @csrf
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" id="btnCancelarConfirm">Cancelar</button>
                <button type="submit" class="btn-modal btn-modal-confirm" id="btnConfirmarAccion">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalConfirmacion');
    const form = document.getElementById('formConfirm');
    const title = document.getElementById('confirmTitle');
    const desc = document.getElementById('confirmDesc');
    const btnConfirm = document.getElementById('btnConfirmarAccion');

    document.querySelectorAll('.btn-abrir-modal-confirm').forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.getAttribute('data-tipo');
            const mesaNum = this.getAttribute('data-mesa-num');
            const url = this.getAttribute('data-url');

            if (tipo === 'mesa') {
                title.textContent = `¿Liberar Mesa ${mesaNum}?`;
                desc.textContent = 'Esta acción marcará la mesa como DISPONIBLE. Solo úsalo si la mesa está físicamente vacía.';
                btnConfirm.textContent = 'Sí, liberar mesa';
                btnConfirm.style.background = 'var(--accent)';
            } else {
                title.textContent = `¿Finalizar sesión en Mesa ${mesaNum}?`;
                desc.textContent = 'Se cerrará la sesión de este cliente y se cancelarán sus pedidos activos en cocina.';
                btnConfirm.textContent = 'Finalizar sesión';
                btnConfirm.style.background = '#f87171';
            }

            form.action = url;
            modal.classList.add('active');
        });
    });

    const cerrarModal = () => modal.classList.remove('active');
    document.getElementById('btnCancelarConfirm').addEventListener('click', cerrarModal);
    modal.addEventListener('click', (e) => { if(e.target === modal) cerrarModal(); });
});
</script>

@endsection