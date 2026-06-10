@extends('mesero.layout')

@section('titulo', 'Historial de pedidos')

@section('contenido')

<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@300;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">

<style>
    :root {
        --surface:    #FFFFFF;
        --surface2:   #FAFAFA;
        --border:     #E5E7EB;
        --muted:      #6B7280;
        --text:       #111827;
        --amber:      #D97706;
        --green:      #059669;
        --blue:       #2563EB;
        --red:        #DC2626;
        --purple:     #7C3AED;
        --radius:     12px;
    }

    .page { width: 100%; padding-bottom: 4rem; }

    /* ── Header ── */
    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 2.5rem;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .page-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.1;
        color: var(--text);
        letter-spacing: -0.02em;
    }
    .page-header h1 span { color: var(--amber); }

    /* ── Resumen chips ── */
    .resumen-chips {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }
    .res-chip {
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        background: var(--surface);
    }
    .chip-total    { color: var(--amber);  border-color: rgba(217,119,6,0.3); }
    .chip-vendido  { color: var(--green);  border-color: rgba(5,150,105,0.3); }
    .chip-entregado{ color: var(--blue);   border-color: rgba(37,99,235,0.3); }
    .chip-cancelado{ color: var(--red);    border-color: rgba(220,38,38,0.3); }

    /* ── Panel de filtros ── */
    .filtros-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    .filtros-titulo {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filtros-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    @media (max-width: 900px) {
        .filtros-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 500px) {
        .filtros-grid { grid-template-columns: 1fr; }
    }

    .filtro-grupo label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 0.5rem;
    }

    .filtro-input,
    .filtro-select {
        width: 100%;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.6rem 0.8rem;
        font-size: 0.9rem;
        font-family: 'Geist', sans-serif;
        color: var(--text);
        outline: none;
        transition: all 0.2s;
        appearance: none;
        -webkit-appearance: none;
    }

    .filtro-input:focus,
    .filtro-select:focus { 
        border-color: var(--amber); 
        box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.1);
        background: var(--surface);
    }

    .filtro-select option { background: #FFFFFF; color: var(--text); }

    .btn-filtrar {
        padding: 0.6rem 1.5rem;
        background: var(--amber);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Geist', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
        height: 40px;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.2);
    }
    .btn-filtrar:hover { 
        transform: translateY(-1px);
        background: #b45309;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
    }
    .btn-filtrar:active {
        transform: translateY(0);
    }

    .btn-limpiar {
        font-size: 0.8rem;
        color: var(--muted);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 1rem;
        transition: color 0.2s;
        font-weight: 500;
    }
    .btn-limpiar:hover { color: var(--red); }

    /* ── Tabla de historial ── */
    .seccion-label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .seccion-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .seccion-label .count {
        background: rgba(217, 119, 6, 0.1);
        color: var(--amber);
        border: 1px solid rgba(217, 119, 6, 0.2);
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .tabla-wrap {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    thead th {
        padding: 1rem 1.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        text-align: left;
        border-bottom: 1px solid var(--border);
        background: var(--surface2);
    }
    thead th:last-child { text-align: right; }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--surface2); }

    tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: var(--text);
    }
    tbody td:last-child { text-align: right; }

    .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: var(--amber); font-weight: 600; background: rgba(217, 119, 6, 0.05); padding: 0.2rem 0.5rem; border-radius: 4px; }
    .dim  { color: var(--muted); font-size: 0.85rem; }

    /* Estado badges */
    .estado-badge {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
        border: 1px solid;
    }
    .estado-CREADO         { background: rgba(217,119,6,.1);  color: var(--amber); border-color: rgba(217,119,6,.2); }
    .estado-EN_PREPARACION { background: rgba(124,58,237,.1); color: var(--purple); border-color: rgba(124,58,237,.2); }
    .estado-LISTO          { background: rgba(5,150,105,.1);  color: var(--green); border-color: rgba(5,150,105,.2); }
    .estado-ENTREGADO      { background: rgba(37,99,235,.1);  color: var(--blue); border-color: rgba(37,99,235,.2); }
    .estado-CANCELADO      { background: rgba(220,38,38,.1);  color: var(--red); border-color: rgba(220,38,38,.2); }

    /* Total cell */
    .total-cell { font-weight: 700; color: var(--text); font-size: 0.95rem; }
    .total-cancelado { color: var(--red); text-decoration: line-through; opacity: 0.7; }

    /* ── Paginación ── */
    .paginacion {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem;
        background: var(--surface);
        border-radius: var(--radius);
        border: 1px solid var(--border);
    }
    .pag-info { font-size: 0.85rem; color: var(--muted); font-weight: 500; }

    .pag-links {
        display: flex;
        gap: 6px;
    }
    .pag-links a,
    .pag-links span {
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        border: 1px solid var(--border);
        color: var(--muted);
        text-decoration: none;
        transition: all 0.2s;
        background: var(--surface2);
        font-weight: 600;
    }
    .pag-links a:hover { border-color: var(--amber); color: var(--amber); background: rgba(217, 119, 6, 0.05); }
    .pag-links span.active { background: var(--amber); color: #fff; border-color: var(--amber); font-weight: 700; box-shadow: 0 2px 5px rgba(217, 119, 6, 0.3); }
    .pag-links span.disabled { opacity: 0.3; cursor: not-allowed; }

    /* ── Filtro activo tag ── */
    .filtro-activo-wrap {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .filtro-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(217, 119, 6, 0.1);
        color: var(--amber);
        border: 1px solid rgba(217, 119, 6, 0.2);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .filtro-tag a {
        color: inherit;
        opacity: 0.6;
        text-decoration: none;
        font-weight: 800;
        transition: opacity 0.2s;
        margin-left: 4px;
    }
    .filtro-tag a:hover { opacity: 1; }
</style>

<div class="page">

    {{-- Header + chips de resumen ─────────────────────────────────────── --}}
    <div class="page-header">
        <h1>Mis <span>pedidos</span></h1>
        <div class="resumen-chips">
            <span class="res-chip chip-total">{{ $resumen['total_pedidos'] }} pedidos</span>
            <span class="res-chip chip-vendido">${{ number_format($resumen['total_vendido'], 0, ',', '.') }}</span>
            <span class="res-chip chip-entregado">{{ $resumen['entregados'] }} entregados</span>
            @if($resumen['cancelados'] > 0)
            <span class="res-chip chip-cancelado">{{ $resumen['cancelados'] }} cancelados</span>
            @endif
        </div>
    </div>

    {{-- Panel de filtros ─────────────────────────────────────────────── --}}
    <div class="filtros-panel">
        <div class="filtros-titulo">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>
            Filtros
        </div>

        <form method="GET" action="{{ route('mesero.historial') }}">
            <div class="filtros-grid">

                <div class="filtro-grupo">
                    <label for="desde">Desde</label>
                    <input
                        type="date"
                        id="desde"
                        name="desde"
                        class="filtro-input"
                        value="{{ $desde ?? now()->subDays(30)->toDateString() }}"
                        max="{{ now()->toDateString() }}"
                    >
                </div>

                <div class="filtro-grupo">
                    <label for="hasta">Hasta</label>
                    <input
                        type="date"
                        id="hasta"
                        name="hasta"
                        class="filtro-input"
                        value="{{ $hasta ?? now()->toDateString() }}"
                        max="{{ now()->toDateString() }}"
                    >
                </div>

                <div class="filtro-grupo">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" class="filtro-select">
                        <option value="">Todos los estados</option>
                        <option value="CREADO"         {{ $estadoFiltro === 'CREADO'         ? 'selected' : '' }}>Nuevo</option>
                        <option value="EN_PREPARACION" {{ $estadoFiltro === 'EN_PREPARACION' ? 'selected' : '' }}>Preparando</option>
                        <option value="LISTO"          {{ $estadoFiltro === 'LISTO'          ? 'selected' : '' }}>Listo</option>
                        <option value="ENTREGADO"      {{ $estadoFiltro === 'ENTREGADO'      ? 'selected' : '' }}>Entregado</option>
                        <option value="CANCELADO"      {{ $estadoFiltro === 'CANCELADO'      ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label for="mesa">Mesa</label>
                    <select id="mesa" name="mesa" class="filtro-select">
                        <option value="">Todas las mesas</option>
                        @foreach($mesas as $m)
                        <option value="{{ $m->numero }}" {{ $mesaFiltro == $m->numero ? 'selected' : '' }}>
                            Mesa {{ $m->numero }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                </div>

            </div>

            {{-- Limpiar filtros ──────────────────────────────────────── --}}
            @if($estadoFiltro || $mesaFiltro || $desde || $hasta)
            <a href="{{ route('mesero.historial') }}" class="btn-limpiar">
                ✕ Limpiar filtros
            </a>
            @endif
        </form>
    </div>

    {{-- Tags de filtros activos ─────────────────────────────────────── --}}
    @php
        $filtrosActivos = array_filter([
            'desde'  => $desde,
            'hasta'  => $hasta,
            'estado' => $estadoFiltro,
            'mesa'   => $mesaFiltro ? 'Mesa ' . $mesaFiltro : null,
        ]);
    @endphp

    @if(count($filtrosActivos))
    <div class="filtro-activo-wrap">
        @foreach($filtrosActivos as $key => $val)
        <span class="filtro-tag">
            {{ ucfirst($key) }}: {{ $val }}
            <a href="{{ route('mesero.historial', array_filter(request()->except($key === 'mesa' ? 'mesa' : $key))) }}"
               title="Quitar filtro">✕</a>
        </span>
        @endforeach
    </div>
    @endif

    {{-- Tabla ────────────────────────────────────────────────────────── --}}
    <div class="seccion-label">
        Resultados
        <span class="count">{{ $pedidos->total() }}</span>
    </div>

    <div class="tabla-wrap">
        @if($pedidos->isEmpty())
            <div class="estado-vacio">
                <div class="icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <p>No hay pedidos que coincidan con los filtros aplicados en este momento.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Mesa</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos as $p)
                    <tr>
                        <td><span class="mono">#{{ $p->short_id }}</span></td>
                        <td class="dim">{{ $p->created_at?->format('d/m/Y') }}</td>
                        <td class="dim">{{ $p->created_at?->format('g:i A') }}</td>
                        <td>Mesa {{ $p->sesionMesa?->mesa?->numero ?? '—' }}</td>
                        <td class="dim">{{ $p->detalles->count() }} ítem{{ $p->detalles->count() !== 1 ? 's' : '' }}</td>
                        <td>
                            <span class="estado-badge estado-{{ $p->estado }}">
                                {{ match($p->estado) {
                                    'CREADO'         => 'Nuevo',
                                    'EN_PREPARACION' => 'Preparando',
                                    'LISTO'          => 'Listo',
                                    'ENTREGADO'      => 'Entregado',
                                    'CANCELADO'      => 'Cancelado',
                                    default          => $p->estado,
                                } }}
                            </span>
                        </td>
                        <td>
                            <span class="total-cell {{ $p->estado === 'CANCELADO' ? 'total-cancelado' : '' }}">
                                ${{ number_format($p->total, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Paginación ───────────────────────────────────────────────────── --}}
    @if($pedidos->hasPages())
    <div class="paginacion">
        <span class="pag-info">
            Mostrando {{ $pedidos->firstItem() }}–{{ $pedidos->lastItem() }} de {{ $pedidos->total() }} pedidos
        </span>

        <div class="pag-links">
            {{-- Anterior --}}
            @if($pedidos->onFirstPage())
                <span class="disabled">‹</span>
            @else
                <a href="{{ $pedidos->previousPageUrl() }}">‹</a>
            @endif

            {{-- Páginas --}}
            @foreach($pedidos->getUrlRange(1, $pedidos->lastPage()) as $page => $url)
                @if($page == $pedidos->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if($pedidos->hasMorePages())
                <a href="{{ $pedidos->nextPageUrl() }}">›</a>
            @else
                <span class="disabled">›</span>
            @endif
        </div>
    </div>
    @endif

</div>

@endsection