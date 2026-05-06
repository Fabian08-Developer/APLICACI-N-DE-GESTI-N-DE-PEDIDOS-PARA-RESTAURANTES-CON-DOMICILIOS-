

<?php $__env->startSection('titulo', 'Historial de pedidos'); ?>

<?php $__env->startSection('contenido'); ?>

<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@300;700&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">

<style>
    :root {
        --amber:      #f0a328;
        --amber-dim:  rgba(240,163,40,0.12);
        --green:      #4caf7d;
        --blue:       #60a5fa;
        --orange:     #fb923c;
        --red:        #f87171;
        --purple:     #c084fc;
        --surface:    rgba(255,255,255,0.03);
        --surface2:   rgba(255,255,255,0.055);
        --border:     rgba(255,255,255,0.07);
        --muted:      rgba(232,225,212,0.45);
        --text:       #e8e1d4;
        --radius:     14px;
    }

    .page { max-width: 960px; margin: 0 auto; padding: 2rem 0 4rem; }

    /* ── Header ── */
    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 2rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .page-header h1 {
        font-family: 'Fraunces', serif;
        font-size: 2rem;
        font-weight: 300;
        line-height: 1;
        color: var(--text);
        letter-spacing: -0.02em;
    }
    .page-header h1 span { color: var(--amber); }

    /* ── Resumen chips ── */
    .resumen-chips {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .res-chip {
        padding: 0.3rem 0.85rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
    }
    .chip-total    { background: var(--amber-dim);          color: var(--amber);  border-color: rgba(240,163,40,0.3); }
    .chip-vendido  { background: rgba(76,175,125,0.12);     color: var(--green);  border-color: rgba(76,175,125,0.3); }
    .chip-entregado{ background: rgba(96,165,250,0.12);     color: var(--blue);   border-color: rgba(96,165,250,0.3); }
    .chip-cancelado{ background: rgba(248,113,113,0.12);    color: var(--red);    border-color: rgba(248,113,113,0.3); }

    /* ── Panel de filtros ── */
    .filtros-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem 1.4rem;
        margin-bottom: 1.5rem;
    }

    .filtros-titulo {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filtros-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    @media (max-width: 768px) {
        .filtros-grid { grid-template-columns: 1fr 1fr; }
    }

    .filtro-grupo label {
        display: block;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 0.4rem;
    }

    .filtro-input,
    .filtro-select {
        width: 100%;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0.55rem 0.75rem;
        font-size: 0.85rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text);
        outline: none;
        transition: border-color 0.15s;
        appearance: none;
        -webkit-appearance: none;
    }

    .filtro-input:focus,
    .filtro-select:focus { border-color: var(--amber); }

    .filtro-input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0.6);
        cursor: pointer;
    }

    .filtro-select option { background: #1c1a15; color: var(--text); }

    .btn-filtrar {
        padding: 0.55rem 1.2rem;
        background: var(--amber);
        color: #111009;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: opacity 0.15s;
        height: 37px;
    }
    .btn-filtrar:hover { opacity: 0.87; }

    .btn-limpiar {
        font-size: 0.78rem;
        color: var(--muted);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 0.75rem;
        transition: color 0.15s;
    }
    .btn-limpiar:hover { color: var(--text); }

    /* ── Tabla de historial ── */
    .seccion-label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .seccion-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .seccion-label .count {
        background: var(--amber-dim);
        color: var(--amber);
        border: 1px solid rgba(240,163,40,0.25);
        border-radius: 999px;
        padding: 1px 8px;
        font-size: 0.65rem;
    }

    .tabla-wrap {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.84rem;
    }

    thead th {
        padding: 0.75rem 1.2rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    thead th:last-child { text-align: right; }

    tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.03);
        transition: background 0.1s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--surface2); }

    tbody td {
        padding: 0.8rem 1.2rem;
        vertical-align: middle;
        color: var(--text);
    }
    tbody td:last-child { text-align: right; }

    .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: var(--amber); }
    .dim  { color: var(--muted); font-size: 0.8rem; }

    /* Estado badges */
    .estado-badge {
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        padding: 2px 8px;
        border-radius: 999px;
        white-space: nowrap;
        border: 1px solid;
    }
    .estado-CREADO         { background: rgba(251,146,60,.15);  color: #fb923c; border-color: rgba(251,146,60,.3); }
    .estado-EN_COCINA      { background: rgba(96,165,250,.15);  color: #60a5fa; border-color: rgba(96,165,250,.3); }
    .estado-EN_PREPARACION { background: rgba(192,132,252,.15); color: #c084fc; border-color: rgba(192,132,252,.3); }
    .estado-LISTO          { background: rgba(76,175,125,.15);  color: #4caf7d; border-color: rgba(76,175,125,.3); }
    .estado-ENTREGADO      { background: rgba(96,165,250,.08);  color: #93c5fd; border-color: rgba(96,165,250,.2); }
    .estado-CANCELADO      { background: rgba(248,113,113,.1);  color: #f87171; border-color: rgba(248,113,113,.2); }

    /* Total cell */
    .total-cell { font-weight: 600; color: var(--text); }
    .total-cancelado { color: var(--muted); text-decoration: line-through; }


    /* ── Paginación ── */
    .paginacion {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.25rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .pag-info { font-size: 0.78rem; color: var(--muted); }

    .pag-links {
        display: flex;
        gap: 4px;
    }
    .pag-links a,
    .pag-links span {
        padding: 0.35rem 0.7rem;
        border-radius: 7px;
        font-size: 0.8rem;
        border: 1px solid var(--border);
        color: var(--muted);
        text-decoration: none;
        transition: all 0.15s;
        background: var(--surface);
    }
    .pag-links a:hover { border-color: var(--amber); color: var(--amber); }
    .pag-links span.active { background: var(--amber); color: #111009; border-color: var(--amber); font-weight: 700; }
    .pag-links span.disabled { opacity: 0.3; cursor: not-allowed; }

    /* ── Filtro activo tag ── */
    .filtro-activo-wrap {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .filtro-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--amber-dim);
        color: var(--amber);
        border: 1px solid rgba(240,163,40,0.25);
        border-radius: 999px;
        padding: 2px 10px 2px 8px;
        font-size: 0.72rem;
        font-weight: 500;
    }
    .filtro-tag a {
        color: inherit;
        opacity: 0.7;
        text-decoration: none;
        font-weight: 700;
        transition: opacity 0.15s;
    }
    .filtro-tag a:hover { opacity: 1; }
</style>

<div class="page">

    
    <div class="page-header">
        <h1>Mis <span>pedidos</span></h1>
        <div class="resumen-chips">
            <span class="res-chip chip-total"><?php echo e($resumen['total_pedidos']); ?> pedidos</span>
            <span class="res-chip chip-vendido">$<?php echo e(number_format($resumen['total_vendido'], 0, ',', '.')); ?></span>
            <span class="res-chip chip-entregado"><?php echo e($resumen['entregados']); ?> entregados</span>
            <?php if($resumen['cancelados'] > 0): ?>
            <span class="res-chip chip-cancelado"><?php echo e($resumen['cancelados']); ?> cancelados</span>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="filtros-panel">
        <div class="filtros-titulo">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>
            Filtros
        </div>

        <form method="GET" action="<?php echo e(route('mesero.historial')); ?>">
            <div class="filtros-grid">

                <div class="filtro-grupo">
                    <label for="desde">Desde</label>
                    <input
                        type="date"
                        id="desde"
                        name="desde"
                        class="filtro-input"
                        value="<?php echo e($desde ?? now()->subDays(30)->toDateString()); ?>"
                        max="<?php echo e(now()->toDateString()); ?>"
                    >
                </div>

                <div class="filtro-grupo">
                    <label for="hasta">Hasta</label>
                    <input
                        type="date"
                        id="hasta"
                        name="hasta"
                        class="filtro-input"
                        value="<?php echo e($hasta ?? now()->toDateString()); ?>"
                        max="<?php echo e(now()->toDateString()); ?>"
                    >
                </div>

                <div class="filtro-grupo">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" class="filtro-select">
                        <option value="">Todos los estados</option>
                        <option value="CREADO"         <?php echo e($estadoFiltro === 'CREADO'         ? 'selected' : ''); ?>>Creado</option>
                        <option value="EN_COCINA"      <?php echo e($estadoFiltro === 'EN_COCINA'      ? 'selected' : ''); ?>>En cocina</option>
                        <option value="EN_PREPARACION" <?php echo e($estadoFiltro === 'EN_PREPARACION' ? 'selected' : ''); ?>>Preparando</option>
                        <option value="LISTO"          <?php echo e($estadoFiltro === 'LISTO'          ? 'selected' : ''); ?>>Listo</option>
                        <option value="ENTREGADO"      <?php echo e($estadoFiltro === 'ENTREGADO'      ? 'selected' : ''); ?>>Entregado</option>
                        <option value="CANCELADO"      <?php echo e($estadoFiltro === 'CANCELADO'      ? 'selected' : ''); ?>>Cancelado</option>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label for="mesa">Mesa</label>
                    <select id="mesa" name="mesa" class="filtro-select">
                        <option value="">Todas las mesas</option>
                        <?php $__currentLoopData = $mesas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->numero); ?>" <?php echo e($mesaFiltro == $m->numero ? 'selected' : ''); ?>>
                            Mesa <?php echo e($m->numero); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                </div>

            </div>

            
            <?php if($estadoFiltro || $mesaFiltro || $desde || $hasta): ?>
            <a href="<?php echo e(route('mesero.historial')); ?>" class="btn-limpiar">
                ✕ Limpiar filtros
            </a>
            <?php endif; ?>
        </form>
    </div>

    
    <?php
        $filtrosActivos = array_filter([
            'desde'  => $desde,
            'hasta'  => $hasta,
            'estado' => $estadoFiltro,
            'mesa'   => $mesaFiltro ? 'Mesa ' . $mesaFiltro : null,
        ]);
    ?>

    <?php if(count($filtrosActivos)): ?>
    <div class="filtro-activo-wrap">
        <?php $__currentLoopData = $filtrosActivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span class="filtro-tag">
            <?php echo e(ucfirst($key)); ?>: <?php echo e($val); ?>

            <a href="<?php echo e(route('mesero.historial', array_filter(request()->except($key === 'mesa' ? 'mesa' : $key)))); ?>"
               title="Quitar filtro">✕</a>
        </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div class="seccion-label">
        Resultados
        <span class="count"><?php echo e($pedidos->total()); ?></span>
    </div>

    <div class="tabla-wrap">
        <?php if($pedidos->isEmpty()): ?>
            <div class="estado-vacio">
                <div class="icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <p>No hay pedidos que coincidan con los filtros aplicados en este momento.</p>
            </div>
        <?php else: ?>
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
                    <?php $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><span class="mono">#<?php echo e($p->id); ?></span></td>
                        <td class="dim"><?php echo e($p->created_at?->format('d/m/Y')); ?></td>
                        <td class="dim"><?php echo e($p->created_at?->format('g:i A')); ?></td>
                        <td>Mesa <?php echo e($p->sesionMesa?->mesa?->numero ?? '—'); ?></td>
                        <td class="dim"><?php echo e($p->detalles->count()); ?> ítem<?php echo e($p->detalles->count() !== 1 ? 's' : ''); ?></td>
                        <td>
                            <span class="estado-badge estado-<?php echo e($p->estado); ?>">
                                <?php echo e(match($p->estado) {
                                    'CREADO'         => 'Creado',
                                    'EN_COCINA'      => 'En cocina',
                                    'EN_PREPARACION' => 'Preparando',
                                    'LISTO'          => 'Listo',
                                    'ENTREGADO'      => 'Entregado',
                                    'CANCELADO'      => 'Cancelado',
                                    default          => $p->estado,
                                }); ?>

                            </span>
                        </td>
                        <td>
                            <span class="total-cell <?php echo e($p->estado === 'CANCELADO' ? 'total-cancelado' : ''); ?>">
                                $<?php echo e(number_format($p->total, 0, ',', '.')); ?>

                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    
    <?php if($pedidos->hasPages()): ?>
    <div class="paginacion">
        <span class="pag-info">
            Mostrando <?php echo e($pedidos->firstItem()); ?>–<?php echo e($pedidos->lastItem()); ?> de <?php echo e($pedidos->total()); ?> pedidos
        </span>

        <div class="pag-links">
            
            <?php if($pedidos->onFirstPage()): ?>
                <span class="disabled">‹</span>
            <?php else: ?>
                <a href="<?php echo e($pedidos->previousPageUrl()); ?>">‹</a>
            <?php endif; ?>

            
            <?php $__currentLoopData = $pedidos->getUrlRange(1, $pedidos->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($page == $pedidos->currentPage()): ?>
                    <span class="active"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($pedidos->hasMorePages()): ?>
                <a href="<?php echo e($pedidos->nextPageUrl()); ?>">›</a>
            <?php else: ?>
                <span class="disabled">›</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('mesero.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria\cafeteria-web\resources\views/mesero/historial.blade.php ENDPATH**/ ?>