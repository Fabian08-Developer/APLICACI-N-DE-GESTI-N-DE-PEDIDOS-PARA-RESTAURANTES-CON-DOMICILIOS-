<?php $__env->startSection('contenido'); ?>
<style>
    :root {
        --z-primary: #c9a84c;
        --z-success: var(--status-success);
        --z-error: var(--status-error);
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.8rem;
        color: var(--text-main);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .card-z {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 1.25rem;
        padding: 1.5rem;
        transition: all 0.2s;
    }
    .card-z:hover {
        transform: translateY(-2px);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-value {
        font-family: 'DM Serif Display', serif;
        font-size: 2.2rem;
        color: var(--text-main);
        margin-bottom: 0.3rem;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--text-sec);
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.05em;
    }

    /* Table */
    .table-wrapper { overflow-x: auto; }
    .table-z {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .table-z th {
        padding: 1rem 1.5rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-sec);
        border-bottom: 1px solid var(--border);
    }
    .table-z td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.875rem;
    }

    /* Badge */
    .badge {
        padding: 0.3rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-success { background: rgba(127, 183, 126, 0.15); color: var(--status-success); }
    .badge-error { background: rgba(201, 124, 124, 0.15); color: var(--status-error); }

    /* Barrios Tags */
    .barrio-tag {
        display: inline-block;
        background: rgba(122, 156, 198, 0.1);
        color: var(--status-info);
        padding: 0.2rem 0.5rem;
        border-radius: 0.4rem;
        font-size: 0.75rem;
        margin-right: 0.3rem;
        margin-bottom: 0.3rem;
        border: 1px solid rgba(122, 156, 198, 0.2);
    }

    /* Sidebar */
    .sidebar-z {
        position: fixed;
        top: 0;
        right: -450px;
        width: 450px;
        height: 100vh;
        background-color: #0F172A;
        z-index: 1001;
        transition: right 0.3s;
        border-left: 1px solid var(--border);
        padding: 2rem;
        display: flex;
        flex-direction: column;
    }
    .sidebar-z.open { right: 0; }
    .overlay-z {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: none;
    }
    .overlay-z.show { display: block; }

    .btn-z {
        padding: 0.75rem 1.25rem;
        border-radius: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-z-primary { background: var(--z-primary); color: #0f0f0f; }
    .btn-z-ghost { background: transparent; color: var(--text-sec); border: 1px solid var(--border); }

    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.75rem; color: var(--text-sec); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 600; }
    .form-input { width: 100%; padding: 0.75rem 1rem; background: var(--surface); border: 1px solid var(--border); border-radius: 0.75rem; color: #fff; }
    .form-help { font-size: 0.7rem; color: var(--text-sec); margin-top: 0.4rem; }

    /* ── ZONAS CARDS GRID ── */
    .zonas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 3rem;
    }

    .zona-card-premium {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 1.5rem;
        padding: 1.75rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .zona-card-premium:hover {
        transform: translateY(-2px);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .zona-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.25rem;
    }

    .zona-icon-wrapper {
        width: 42px;
        height: 42px;
        background: rgba(201, 168, 76, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--z-primary);
    }

    .zona-status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-activa { background: rgba(127, 183, 126, 0.15); color: var(--status-success); }
    .status-inactiva { background: rgba(201, 124, 124, 0.15); color: var(--status-error); }

    .zona-card-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.35rem;
        color: var(--text-main);
        margin-bottom: 0.5rem;
    }

    .zona-card-barrios {
        font-size: 0.85rem;
        color: var(--text-sec);
        line-height: 1.5;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.5rem;
    }

    .zona-card-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .zona-stat-box {
        background: rgba(255,255,255,0.03);
        padding: 1rem;
        border-radius: 1rem;
        text-align: center;
    }

    .zona-stat-value {
        display: block;
        font-family: 'DM Serif Display', serif;
        font-size: 1.25rem;
        color: var(--z-primary);
        margin-bottom: 0.25rem;
    }
    .zona-stat-label {
        font-size: 0.65rem;
        color: var(--text-sec);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .zona-card-team {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .team-avatars {
        display: flex;
        margin-right: 0.5rem;
    }

    .team-avatar-sm {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--surface);
        border: 2px solid var(--bg-main);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--z-primary);
        margin-left: -8px;
    }
    .team-avatar-sm:first-child { margin-left: 0; }

    .team-label {
        font-size: 0.75rem;
        color: var(--text-sec);
    }

    .btn-ver-detalle {
        width: 100%;
        margin-top: 1.25rem;
        padding: 0.8rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        border-radius: 0.75rem;
        color: var(--text-main);
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-ver-detalle:hover {
        background: rgba(255,255,255,0.1);
        border-color: var(--text-sec);
    }

    /* ── DETAIL MODAL ── */
    .modal-overlay-detail {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(10px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .modal-overlay-detail.show { display: flex; }

    .modal-zona-detail {
        background: #0F172A;
        width: 100%;
        max-width: 550px;
        border-radius: 2rem;
        border: 1px solid rgba(255,255,255,0.1);
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .modal-detail-header {
        padding: 2rem;
        background: linear-gradient(to bottom, rgba(201, 168, 76, 0.05), transparent);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .modal-detail-body { padding: 0 2rem 2rem; }

    .detail-section-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-sec);
        margin-bottom: 0.75rem;
        display: block;
        font-weight: 700;
    }

    .domiciliario-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: rgba(255,255,255,0.03);
        padding: 0.75rem 1rem;
        border-radius: 1rem;
        margin-bottom: 0.75rem;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .domiciliario-row:hover {
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.05);
    }

    .dom-avatar {
        width: 40px;
        height: 40px;
        background: rgba(201, 168, 76, 0.15);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--z-primary);
        font-weight: 700;
        font-size: 0.9rem;
    }

    .dom-info h4 { font-size: 0.9rem; color: var(--text-main); margin-bottom: 0.1rem; }
    .dom-info p { font-size: 0.75rem; color: var(--text-sec); text-transform: capitalize; }

    .dom-status {
        margin-left: auto;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Zonas de Cobertura</h1>
        <p style="color: var(--text-sec); font-size: 0.875rem;">Jerarquía: Zona → Barrios asociados</p>
    </div>
    <button class="btn-z btn-z-primary" onclick="openCreateForm()">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nueva Zona
    </button>
</div>

<div class="stats-grid">
    <div class="card-z">
        <p class="stat-value"><?php echo e($stats['total']); ?></p>
        <p class="stat-label">Total Zonas</p>
    </div>
    <div class="card-z">
        <p class="stat-value" style="color: var(--status-success)"><?php echo e($stats['activas']); ?></p>
        <p class="stat-label">Zonas Activas</p>
    </div>
    <div class="card-z">
        <p class="stat-value" style="color: var(--status-info)">$<?php echo e(number_format($stats['costo_promedio'], 0)); ?></p>
        <p class="stat-label">Costo Promedio</p>
    </div>
</div>

<div class="card-z" style="padding: 0;">
    <div class="table-wrapper">
        <table class="table-z">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>Barrios Cubiertos</th>
                    <th>Costo Envío</th>
                    <th>Tiempo</th>
                    <th>Equipo</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $zonas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zona): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="font-weight: 600;">
                        <?php echo e($zona->nombre); ?>

                        <div style="font-weight: 400; font-size: 0.75rem; color: var(--text-sec);"><?php echo e($zona->descripcion); ?></div>
                    </td>
                    <td style="max-width: 300px;">
                        <?php $__empty_2 = true; $__currentLoopData = $zona->barrios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barrio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <span class="barrio-tag"><?php echo e($barrio->nombre); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <span style="color: var(--text-sec); font-style: italic; font-size: 0.75rem;">Sin barrios asignados</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: 600; color: var(--status-info);">$<?php echo e(number_format($zona->costo_envio, 0)); ?></td>
                    <td><?php echo e($zona->tiempo_estimado); ?> min</td>
                    <td style="text-align: center;">
                        <span title="Domiciliarios en esta zona"><?php echo e($zona->domiciliarios_count); ?> 🛵</span>
                    </td>
                    <td>
                        <span class="badge <?php echo e($zona->activo ? 'badge-success' : 'badge-error'); ?>">
                            <?php echo e($zona->activo ? 'Activa' : 'Inactiva'); ?>

                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <button class="btn-z btn-z-ghost" onclick="openEditForm(<?php echo e(json_encode($zona)); ?>, '<?php echo e($zona->barrios->pluck('nombre')->implode(', ')); ?>')" style="padding: 0.5rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <form action="<?php echo e(route('admin.zonas.destroy', $zona->id)); ?>" method="POST" onsubmit="return confirm('¿Eliminar esta zona?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn-z btn-z-ghost" style="padding: 0.5rem; color: var(--status-error);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" style="text-align: center; padding: 3rem;">No hay zonas definidas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="zonas-grid">
    <?php $__currentLoopData = $zonas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zona): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="zona-card-premium">
        <div class="zona-card-header">
            <div class="zona-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            </div>
            <span class="zona-status-badge <?php echo e($zona->activo ? 'status-activa' : 'status-inactiva'); ?>">
                <?php echo e($zona->activo ? 'Activa' : 'Inactiva'); ?>

            </span>
        </div>

        <h3 class="zona-card-title"><?php echo e($zona->nombre); ?></h3>
        <p class="zona-card-barrios">
            <?php echo e($zona->barrios->pluck('nombre')->implode(', ') ?: 'Sin barrios asignados'); ?>

        </p>

        <div class="zona-card-stats">
            <div class="zona-stat-box">
                <span class="zona-stat-value">$<?php echo e(number_format($zona->costo_envio, 0)); ?></span>
                <span class="zona-stat-label">Costo Envío</span>
            </div>
            <div class="zona-stat-box">
                <span class="zona-stat-value"><?php echo e($zona->tiempo_estimado); ?> min</span>
                <span class="zona-stat-label">Tiempo Est.</span>
            </div>
        </div>

        <div class="zona-card-team">
            <div class="team-avatars">
                <?php $__currentLoopData = $zona->domiciliarios->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="team-avatar-sm" title="<?php echo e($dom->nombre); ?>"><?php echo e($dom->iniciales); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($zona->domiciliarios_count > 3): ?>
                    <div class="team-avatar-sm" style="background: rgba(255,255,255,0.1)">+<?php echo e($zona->domiciliarios_count - 3); ?></div>
                <?php endif; ?>
            </div>
            <span class="team-label">
                Domiciliarios asignados (<?php echo e($zona->domiciliarios_count); ?>)
            </span>
        </div>

        <button class="btn-ver-detalle" onclick="viewZoneDetail(<?php echo e($zona->id); ?>)">
            Ver detalle
        </button>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Modal Detalle Zona -->
<div class="modal-overlay-detail" id="zoneDetailModal">
    <div class="modal-zona-detail">
        <div class="modal-detail-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="zona-icon-wrapper" style="width: 48px; height: 48px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <div>
                    <h2 class="zona-card-title" id="detZonaNombre" style="margin: 0; font-size: 1.6rem;">--</h2>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.3rem;">
                        <span class="detail-section-label" style="margin: 0;">Estado:</span>
                        <span class="zona-status-badge status-activa" id="detZonaStatus">Activa</span>
                    </div>
                </div>
            </div>
            <button onclick="closeZoneDetail()" style="background: none; border: none; color: var(--text-sec); cursor: pointer; padding: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div class="modal-detail-body">
            <div style="margin-bottom: 2rem;">
                <span class="detail-section-label">Descripción:</span>
                <p id="detZonaDesc" style="color: var(--text-main); font-size: 1rem; line-height: 1.6;">--</p>
            </div>

            <div class="zona-card-stats" style="margin-bottom: 2rem;">
                <div class="zona-stat-box" style="background: rgba(201, 168, 76, 0.05); border: 1px solid rgba(201, 168, 76, 0.1);">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; color: var(--z-primary);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        <span class="zona-stat-value" id="detZonaCosto" style="margin: 0;">--</span>
                    </div>
                    <span class="zona-stat-label">Costo de Envío</span>
                </div>
                <div class="zona-stat-box" style="background: rgba(122, 156, 198, 0.05); border: 1px solid rgba(122, 156, 198, 0.1);">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem; color: var(--status-info);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span class="zona-stat-value" id="detZonaTiempo" style="margin: 0; color: var(--status-info);">--</span>
                    </div>
                    <span class="zona-stat-label">Tiempo Estimado</span>
                </div>
            </div>

            <div>
                <span class="detail-section-label">Domiciliarios asignados:</span>
                <div id="detZonaTeam" style="max-height: 250px; overflow-y: auto; padding-right: 0.5rem;">
                    <!-- Domiciliarios rows -->
                </div>
            </div>

            <div style="margin-top: 2.5rem; display: flex; gap: 1rem;">
                <button class="btn-z btn-z-ghost" style="flex: 1; padding: 0.9rem;" onclick="closeZoneDetail()">Cerrar</button>
                <button class="btn-z btn-z-primary" id="btnEditFromDetail" style="flex: 2; padding: 0.9rem;">Editar Zona</button>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Form -->
<div class="overlay-z" id="overlayZ" onclick="closeSidebar()"></div>
<div class="sidebar-z" id="sidebarZ">
    <h2 id="formTitle" style="color: #fff; font-family: 'DM Serif Display', serif; margin-bottom: 1.5rem;">Nueva Zona</h2>
    <form id="mainForm" method="POST">
        <?php echo csrf_field(); ?>
        <div id="methodField"></div>
        <div class="form-group">
            <label class="form-label">Nombre de la Zona</label>
            <input type="text" name="nombre" id="inNombre" class="form-input" required placeholder="Ej: Zona Norte">
        </div>
        <div class="form-group">
            <label class="form-label">Barrios (Separados por comas)</label>
            <textarea name="barrios" id="inBarrios" class="form-input" rows="4" placeholder="Los Álamos, El Vergel, Mirador..."></textarea>
            <p class="form-help">Escribe los nombres de los barrios que pertenecen a esta zona, separados por comas.</p>
        </div>
        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Costo Envío ($)</label>
                <input type="number" name="costo_envio" id="inCosto" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tiempo (min)</label>
                <input type="number" name="tiempo_estimado" id="inTiempo" class="form-input" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción Opcional</label>
            <input type="text" name="descripcion" id="inDesc" class="form-input" placeholder="Referencia visual...">
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="activo" id="inActivo" class="form-input">
                <option value="1">Activa</option>
                <option value="0">Inactiva</option>
            </select>
        </div>
        
        <div style="margin-top: auto; display: flex; gap: 1rem; padding-top: 2rem;">
            <button type="button" class="btn-z btn-z-ghost" style="flex: 1;" onclick="closeSidebar()">Cancelar</button>
            <button type="submit" class="btn-z btn-z-primary" style="flex: 2;">Guardar Cambios</button>
        </div>
    </form>
</div>

<script>
    function openCreateForm() {
        document.getElementById('formTitle').innerText = "Nueva Zona";
        document.getElementById('mainForm').action = "<?php echo e(route('admin.zonas.store')); ?>";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('mainForm').reset();
        document.getElementById('sidebarZ').classList.add('open');
        document.getElementById('overlayZ').classList.add('show');
    }

    function openEditForm(zona, barriosStr) {
        document.getElementById('formTitle').innerText = "Editar Zona";
        document.getElementById('mainForm').action = `/admin/zonas-cobertura/${zona.id}`;
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('inNombre').value = zona.nombre;
        document.getElementById('inDesc').value = zona.descripcion || '';
        document.getElementById('inCosto').value = zona.costo_envio;
        document.getElementById('inTiempo').value = zona.tiempo_estimado;
        document.getElementById('inActivo').value = zona.activo ? "1" : "0";
        document.getElementById('inBarrios').value = barriosStr;
        
        document.getElementById('sidebarZ').classList.add('open');
        document.getElementById('overlayZ').classList.add('show');
    }

    function closeSidebar() {
        document.getElementById('sidebarZ').classList.remove('open');
        document.getElementById('overlayZ').classList.remove('show');
    }

    function viewZoneDetail(id) {
        fetch(`/admin/zonas-cobertura/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const zona = res.data;
                    document.getElementById('detZonaNombre').innerText = zona.nombre;
                    document.getElementById('detZonaStatus').innerText = zona.activo ? 'Activa' : 'Inactiva';
                    document.getElementById('detZonaStatus').className = `zona-status-badge ${zona.activo ? 'status-activa' : 'status-inactiva'}`;
                    document.getElementById('detZonaDesc').innerText = zona.descripcion || (zona.barrios.join(', ') || 'Sin descripción');
                    document.getElementById('detZonaCosto').innerText = `$${new Intl.NumberFormat().format(zona.costo_envio)}`;
                    document.getElementById('detZonaTiempo').innerText = `${zona.tiempo_estimado} min`;
                    
                    const teamContainer = document.getElementById('detZonaTeam');
                    teamContainer.innerHTML = '';
                    
                    if (zona.domiciliarios.length > 0) {
                        zona.domiciliarios.forEach(dom => {
                            const statusColor = dom.estado_color === 'success' ? '#7FB77E' : 
                                               (dom.estado_color === 'warning' ? '#E6B566' : 
                                               (dom.estado_color === 'destructive' ? '#C97C7C' : '#7A9CC6'));
                            
                            teamContainer.innerHTML += `
                                <div class="domiciliario-row">
                                    <div class="dom-avatar">${dom.iniciales}</div>
                                    <div class="dom-info">
                                        <h4>${dom.nombre}</h4>
                                        <p>${dom.vehiculo}</p>
                                    </div>
                                    <span class="dom-status" style="background: rgba(255,255,255,0.05); color: ${statusColor};">
                                        ${dom.estado.replace('_', ' ')}
                                    </span>
                                </div>
                            `;
                        });
                    } else {
                        teamContainer.innerHTML = '<p style="color: var(--text-sec); font-style: italic; font-size: 0.85rem; text-align: center; padding: 1rem;">No hay domiciliarios asignados a esta zona.</p>';
                    }

                    document.getElementById('btnEditFromDetail').onclick = () => {
                        closeZoneDetail();
                        openEditForm(zona, zona.barrios.join(', '));
                    };

                    document.getElementById('zoneDetailModal').classList.add('show');
                }
            });
    }

    function closeZoneDetail() {
        document.getElementById('zoneDetailModal').classList.remove('show');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.Layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\Cafeteria Vs.2 - PWA\cafeteria-web\resources\views/admin/zonas/index.blade.php ENDPATH**/ ?>