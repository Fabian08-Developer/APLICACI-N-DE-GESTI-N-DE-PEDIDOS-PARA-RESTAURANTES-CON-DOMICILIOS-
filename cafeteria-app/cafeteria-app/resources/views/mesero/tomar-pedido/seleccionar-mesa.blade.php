@extends('mesero.layout')

@section('titulo', 'Tomar Pedido - Seleccionar Mesa')

@section('contenido')
<div class="page-header">
    <div>
        <h1 class="page-title">Tomar Pedido</h1>
        <p class="page-sub">Selecciona una mesa para continuar con el servicio</p>
    </div>
</div>

<div class="mesas-grid-pos">
    @foreach($mesas as $mesa)
        <a href="{{ route('mesero.tomar-pedido.menu', $mesa->id) }}" 
           class="mesa-card-pos {{ $mesa->tiene_sesion ? 'mesa-card-pos--activa' : '' }}"
           @if($mesa->tiene_sesion)
           onclick="confirmarMesaOcupada(event, this.href, '{{ $mesa->numero }}')"
           @endif
        >
            @if($mesa->tiene_sesion)
                <div class="mesa-status-pulse"></div>
            @endif
            <div class="mesa-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="8" width="18" height="8" rx="2" />
                    <path d="M5 8v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2" />
                    <path d="M6 16v4" />
                    <path d="M18 16v4" />
                </svg>
            </div>
            <div class="mesa-numero">Mesa {{ $mesa->numero }}</div>
            <div class="mesa-estado">
                @if($mesa->tiene_sesion)
                    <span class="badge-estado badge-estado--ocupada">En Uso</span>
                @else
                    <span class="badge-estado badge-estado--disponible">Disponible</span>
                @endif
            </div>
            @if($mesa->tiene_sesion)
                <div class="mesa-action-text">Añadir al pedido →</div>
            @else
                <div class="mesa-action-text mesa-action-text--new">Nuevo pedido →</div>
            @endif
        </a>
    @endforeach
</div>

<style>
    .page-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 2.5rem; 
        position: relative;
    }
    .page-header::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        width: 100%;
        height: 1px;
        background: linear-gradient(to right, var(--border), transparent);
    }
    .page-title { 
        font-size: 1.8rem; 
        font-weight: 800; 
        margin: 0; 
        background: linear-gradient(90deg, #fff, var(--text-muted));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }
    .page-sub { 
        font-size: 0.95rem; 
        color: var(--text-muted); 
        margin: 0.3rem 0 0 0; 
    }

    .mesas-grid-pos {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .mesa-card-pos {
        background: linear-gradient(145deg, var(--surface), rgba(255,255,255,0.02));
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--text-main);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .mesa-card-pos::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at 50% 0%, rgba(255,255,255,0.05), transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .mesa-card-pos:hover {
        transform: translateY(-6px);
        border-color: rgba(255,255,255,0.15);
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    }

    .mesa-card-pos:hover::before {
        opacity: 1;
    }

    .mesa-icon {
        background: rgba(255,255,255,0.04);
        padding: 1rem;
        border-radius: 50%;
        margin-bottom: 1rem;
        color: var(--text-muted);
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.02);
    }

    .mesa-card-pos:hover .mesa-icon {
        color: var(--text-main);
        background: rgba(255,255,255,0.08);
        transform: scale(1.05);
    }

    .mesa-numero {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: var(--text-main);
    }

    .badge-estado {
        font-size: 0.75rem;
        padding: 0.35rem 0.9rem;
        border-radius: 100px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
        letter-spacing: 0.5px;
    }

    .badge-estado--ocupada {
        background: rgba(91,141,238,0.15);
        color: #8bb1ff;
        border: 1px solid rgba(91,141,238,0.3);
    }

    .badge-estado--disponible {
        background: rgba(82,183,136,0.15);
        color: #74cfa4;
        border: 1px solid rgba(82,183,136,0.3);
    }

    .mesa-action-text {
        margin-top: 1.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    
    .mesa-card-pos:hover .mesa-action-text {
        opacity: 1;
        transform: translateY(0);
        color: #8bb1ff;
    }

    .mesa-card-pos:hover .mesa-action-text--new {
        color: #74cfa4;
    }

    /* Estilos Especiales para Mesa Activa (En Uso) */
    .mesa-card-pos--activa {
        border-color: rgba(91, 141, 238, 0.3);
        background: linear-gradient(145deg, rgba(91, 141, 238, 0.05), rgba(91, 141, 238, 0.01));
    }

    .mesa-card-pos--activa .mesa-icon {
        color: #8bb1ff;
        background: rgba(91, 141, 238, 0.1);
        border-color: rgba(91, 141, 238, 0.2);
    }

    .mesa-card-pos--activa:hover {
        border-color: rgba(91, 141, 238, 0.5);
        box-shadow: 0 10px 25px rgba(91, 141, 238, 0.15);
    }

    /* Efecto de Pulso */
    .mesa-status-pulse {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 10px;
        height: 10px;
        background-color: #8bb1ff;
        border-radius: 50%;
    }

    .mesa-status-pulse::after {
        content: '';
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        background-color: #8bb1ff;
        border-radius: 50%;
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        opacity: 0.5;
    }

    @keyframes pulse-ring {
        0% { transform: scale(0.5); opacity: 0.8; }
        100% { transform: scale(2.5); opacity: 0; }
    }
</style>

{{-- ── MODAL CONFIRMACIÓN MESA OCUPADA ── --}}
<div class="modal-overlay" id="modalMesaOcupada">
    <div class="modal-confirm">
        <div class="modal-icon" style="background: rgba(91,141,238,0.1); color: #8bb1ff;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <h3 class="modal-title">Mesa <span id="spanNumMesa"></span> en Uso</h3>
        <p class="modal-desc">Esta mesa ya tiene una sesión activa y/o pedidos en curso. ¿Deseas unirte a la sesión para añadir más productos?</p>
        
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-modal-cancel" onclick="cerrarModalMesaOcupada()">Cancelar</button>
            <a href="#" class="btn-modal btn-modal-confirm" id="btnContinuarMesa" style="background: #8bb1ff; border-color: #8bb1ff; color: #0F172A; text-decoration: none; display: flex; align-items: center; justify-content: center;">Sí, unirme</a>
        </div>
    </div>
</div>

<script>
    function confirmarMesaOcupada(event, url, numero) {
        event.preventDefault();
        document.getElementById('spanNumMesa').textContent = numero;
        document.getElementById('btnContinuarMesa').href = url;
        document.getElementById('modalMesaOcupada').classList.add('active');
    }

    function cerrarModalMesaOcupada() {
        document.getElementById('modalMesaOcupada').classList.remove('active');
    }

    // Cerrar modal al hacer clic fuera de él
    document.getElementById('modalMesaOcupada').addEventListener('click', function(e) {
        if(e.target === this) {
            cerrarModalMesaOcupada();
        }
    });
</script>

@endsection
