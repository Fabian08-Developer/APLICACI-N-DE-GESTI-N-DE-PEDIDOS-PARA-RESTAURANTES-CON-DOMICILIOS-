<div x-data="{ 
        isOpen: @entangle('isOpen').live, 
        showDetail: false,
        showBarrioEditor: false,
        showModalEliminar: false,
        deleteId: '',
        deleteName: ''
    }"
    x-effect="document.body.style.overflow = (isOpen || showDetail || showBarrioEditor || showModalEliminar) ? 'hidden' : ''"
    @open-sidebar.window="isOpen = true"
    @close-sidebar.window="isOpen = false"
    @open-detail-modal.window="showDetail = true"
    @close-detail-modal.window="showDetail = false"
    @open-barrio-editor.window="showBarrioEditor = true; showDetail = false; $nextTick(() => { window.initBarrioMapaNow && window.initBarrioMapaNow(); })"
    @close-barrio-editor.window="showBarrioEditor = false">

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
            background: #FFFFFF;
            border: 1px solid rgba(196, 139, 87, 0.15);
            border-radius: 1.25rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .card-z:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .btn-acciones {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
        }

        .btn-editar {
            color: var(--z-primary);
        }

        .btn-editar:hover {
            background: rgba(201, 168, 76, 0.1);
            border-color: rgba(201, 168, 76, 0.2);
        }

        .btn-eliminar {
            color: var(--status-error);
        }

        .btn-eliminar:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
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
        .table-wrapper {
            overflow-x: auto;
        }

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

        .badge-success {
            background: rgba(127, 183, 126, 0.15);
            color: var(--status-success);
        }

        .badge-error {
            background: rgba(201, 124, 124, 0.15);
            color: var(--status-error);
        }

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
            background-color: #FFFFFF;
            z-index: 1001;
            transition: right 0.3s;
            border-left: 1px solid var(--border);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 25px rgba(0, 0, 0, 0.05);
            overflow-y: auto;
        }

        .sidebar-z.open {
            right: 0;
        }

        .overlay-z {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }

        .overlay-z.show {
            display: block;
        }

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

        .btn-z-primary {
            background-color: #E07A5F;
            color: #FFFFFF;
        }

        .btn-z-primary:hover {
            background-color: #D4694D;
            box-shadow: 0 4px 12px rgba(224, 122, 95, 0.3);
        }

        .btn-z-ghost {
            background: transparent;
            color: var(--text-sec);
            border: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            color: var(--text-sec);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            color: var(--text-main);
        }

        .form-help {
            font-size: 0.7rem;
            color: var(--text-sec);
            margin-top: 0.4rem;
        }

        /* ── ZONAS CARDS GRID ── */
        .zonas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .zona-card-premium {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 1.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .zona-card-premium:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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

        .status-activa {
            background: rgba(127, 183, 126, 0.15);
            color: var(--status-success);
        }

        .status-inactiva {
            background: rgba(201, 124, 124, 0.15);
            color: var(--status-error);
        }

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
            background: rgba(44, 36, 27, 0.02);
            border: 1px solid rgba(44, 36, 27, 0.05);
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

        .team-avatar-sm:first-child {
            margin-left: 0;
        }

        .team-label {
            font-size: 0.75rem;
            color: var(--text-sec);
        }

        .btn-ver-detalle {
            width: 100%;
            margin-top: 1.25rem;
            padding: 0.8rem;
            background: rgba(44, 36, 27, 0.02);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-ver-detalle:hover {
            background: rgba(44, 36, 27, 0.05);
            border-color: var(--primary);
        }

        /* ── DETAIL MODAL ── */
        .modal-overlay-detail {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .modal-overlay-detail.show {
            display: flex;
        }

        .modal-zona-detail {
            background: #FFFFFF;
            width: 100%;
            max-width: 480px;
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .modal-detail-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(to bottom, rgba(201, 168, 76, 0.05), transparent);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .modal-detail-body {
            padding: 0 1.5rem 1.25rem;
        }

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
            background: rgba(44, 36, 27, 0.02);
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .domiciliario-row:hover {
            background: rgba(44, 36, 27, 0.05);
            border-color: var(--primary);
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

        .dom-info h4 {
            font-size: 0.9rem;
            color: var(--text-main);
            margin-bottom: 0.1rem;
        }

        .dom-info p {
            font-size: 0.75rem;
            color: var(--text-sec);
            text-transform: capitalize;
        }

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
        <button class="btn-z btn-z-primary" @click="isOpen = true" wire:click="openCreate">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nueva Zona
        </button>
    </div>

    @if(session()->has('success'))
    <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; color: #4ade80; font-size: 0.85rem;">
        {{ session('success') }}
    </div>
    @endif
    @if(session()->has('error'))
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; color: #f87171; font-size: 0.85rem;">
        {{ session('error') }}
    </div>
    @endif

    <div class="stats-grid">
        <div class="card-z">
            <p class="stat-value">{{ $stats['total'] }}</p>
            <p class="stat-label">Total Zonas</p>
        </div>
        <div class="card-z">
            <p class="stat-value" style="color: var(--status-success)">{{ $stats['activas'] }}</p>
            <p class="stat-label">Zonas Activas</p>
        </div>
        <div class="card-z">
            <p class="stat-value" style="color: var(--status-info)">${{ number_format($stats['costo_promedio'], 0) }}</p>
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
                    @forelse($zonas as $zona)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $zona->nombre }}
                            <div style="font-weight: 400; font-size: 0.75rem; color: var(--text-sec);">{{ $zona->descripcion }}</div>
                        </td>
                        <td style="max-width: 300px;">
                            @forelse($zona->barrios as $barrio)
                            <span class="barrio-tag">{{ $barrio->nombre }}</span>
                            @empty
                            <span style="color: var(--text-sec); font-style: italic; font-size: 0.75rem;">Sin barrios asignados</span>
                            @endforelse
                        </td>
                        <td style="font-weight: 600; color: var(--status-info);">${{ number_format($zona->costo_envio, 0) }}</td>
                        <td>{{ $zona->tiempo_estimado }} min</td>
                        <td style="text-align: center;">
                            <span title="Domiciliarios en esta zona">{{ $zona->domiciliarios_count }} </span>
                        </td>
                        <td>
                            <button type="button" wire:click="toggleActivo('{{ $zona->id }}')" class="badge {{ $zona->activo ? 'badge-success' : 'badge-error' }}" style="cursor: pointer; border: none; font-family: inherit;">
                                {{ $zona->activo ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <button type="button" class="btn-acciones btn-editar" @click="isOpen = true" wire:click="openEdit('{{ $zona->id }}')" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="btn-acciones btn-eliminar"
                                    @click.prevent.stop="deleteId = '{{ $zona->id }}'; deleteName = '{{ addslashes($zona->nombre) }}'; showModalEliminar = true;"
                                    title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem;">No hay zonas definidas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="zonas-grid">
        @foreach($zonas as $zona)
        <div class="zona-card-premium">
            <div class="zona-card-header">
                <div class="zona-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <button type="button" wire:click="toggleActivo('{{ $zona->id }}')" class="zona-status-badge {{ $zona->activo ? 'status-activa' : 'status-inactiva' }}" style="cursor: pointer; border: none; font-family: inherit;">
                    {{ $zona->activo ? 'Activa' : 'Inactiva' }}
                </button>
            </div>

            <h3 class="zona-card-title">{{ $zona->nombre }}</h3>
            <p class="zona-card-barrios">
                {{ $zona->barrios->pluck('nombre')->implode(', ') ?: 'Sin barrios asignados' }}
            </p>

            <div class="zona-card-stats">
                <div class="zona-stat-box">
                    <span class="zona-stat-value">${{ number_format($zona->costo_envio, 0) }}</span>
                    <span class="zona-stat-label">Costo Envío</span>
                </div>
                <div class="zona-stat-box">
                    <span class="zona-stat-value">{{ $zona->tiempo_estimado }} min</span>
                    <span class="zona-stat-label">Tiempo Est.</span>
                </div>
            </div>

            <div class="zona-card-team">
                <div class="team-avatars">
                    @foreach($zona->domiciliarios->take(3) as $dom)
                    <div class="team-avatar-sm" title="{{ $dom->nombre }}">{{ $dom->iniciales }}</div>
                    @endforeach
                    @if($zona->domiciliarios_count > 3)
                    <div class="team-avatar-sm" style="background: rgba(255,255,255,0.1)">+{{ $zona->domiciliarios_count - 3 }}</div>
                    @endif
                </div>
                <span class="team-label">
                    Domiciliarios ({{ $zona->domiciliarios_count }})
                </span>
            </div>

            <button class="btn-ver-detalle" @click="showDetail = true" wire:click="viewDetail('{{ $zona->id }}')">
                Ver detalle
            </button>
        </div>
        @endforeach
    </div>

    <div class="modal-overlay-detail" :class="{ 'show': showDetail }" wire:ignore.self>
        @if($selectedZona)
        <div class="modal-zona-detail">
            <div class="modal-detail-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="zona-icon-wrapper" style="width: 48px; height: 48px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div>
                        <h2 class="zona-card-title" style="margin: 0; font-size: 1.3rem;">{{ $selectedZona['nombre'] }}</h2>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.2rem;">
                            <span class="detail-section-label" style="margin: 0;">Estado:</span>
                            <span class="zona-status-badge {{ $selectedZona['activo'] ? 'status-activa' : 'status-inactiva' }}">{{ $selectedZona['activo'] ? 'Activa' : 'Inactiva' }}</span>
                        </div>
                    </div>
                </div>
                <button @click="showDetail = false" style="background: none; border: none; color: var(--text-sec); cursor: pointer; padding: 0.25rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-detail-body">
                <div style="margin-bottom: 1.25rem;">
                    <span class="detail-section-label">Descripción:</span>
                    <p style="color: var(--text-main); font-size: 0.9rem; line-height: 1.5; margin-bottom: 0;">{{ $selectedZona['descripcion'] ?: 'Sin descripción registrada.' }}</p>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span class="detail-section-label" style="margin: 0;">Barrios Cubiertos:</span>
                        <span style="font-size: 0.7rem; color: var(--text-sec);">
                            {{ count(array_filter($selectedZona['barrios'], fn($b) => $b['tiene_ubicacion'])) }}
                            / {{ count($selectedZona['barrios']) }} ubicados en mapa
                        </span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem; max-height: 160px; overflow-y: auto; padding-right: 0.25rem;">
                        @forelse($selectedZona['barrios'] as $barrioData)
                        <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(44,36,27,0.02); border: 1px solid var(--border); border-radius: 0.75rem; padding: 0.6rem 0.9rem; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(44,36,27,0.05)'" onmouseout="this.style.background='rgba(44,36,27,0.02)'">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                @if($barrioData['tiene_ubicacion'])
                                <span style="font-size: 0.75rem;" title="Barrio ubicado en mapa">📍</span>
                                @else
                                <span style="font-size: 0.75rem; opacity: 0.4;" title="Sin coordenadas">📍</span>
                                @endif
                                <span style="font-size: 0.875rem; color: var(--text-main); font-weight: 500;">{{ $barrioData['nombre'] }}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                @if($barrioData['tiene_ubicacion'])
                                <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; background: rgba(127,183,126,0.15); color: var(--status-success); padding: 0.15rem 0.5rem; border-radius: 0.4rem;">Ubicado</span>
                                @else
                                <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; background: rgba(230,181,102,0.15); color: #c9a84c; padding: 0.15rem 0.5rem; border-radius: 0.4rem;">Sin ubicación</span>
                                @endif
                                <button type="button"
                                    wire:click="editarBarrioUbicacion('{{ $barrioData['id'] }}')"
                                    style="background: none; border: 1px solid var(--border); color: var(--text-sec); border-radius: 0.5rem; padding: 0.25rem 0.6rem; font-size: 0.7rem; cursor: pointer; font-weight: 600; transition: all 0.2s;"
                                    onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'"
                                    onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-sec)'">
                                    Editar
                                </button>
                            </div>
                        </div>
                        @empty
                        <span style="color: var(--text-sec); font-style: italic; font-size: 0.85rem;">Ningún barrio registrado</span>
                        @endforelse
                    </div>
                </div>

                <div class="zona-card-stats" style="margin-bottom: 1.25rem; display: grid; gap: 0.5rem; grid-template-columns: 1fr 1fr;">
                    <div class="zona-stat-box" style="background: rgba(201, 168, 76, 0.05); border: 1px solid rgba(201, 168, 76, 0.1); padding: 0.75rem;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; margin-bottom: 0.25rem; color: var(--z-primary);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <span class="zona-stat-value" style="margin: 0; font-size: 1.1rem;">${{ number_format($selectedZona['costo_envio'], 0) }}</span>
                        </div>
                        <span class="zona-stat-label" style="font-size: 0.65rem;">Costo de Envío</span>
                    </div>
                    <div class="zona-stat-box" style="background: rgba(122, 156, 198, 0.05); border: 1px solid rgba(122, 156, 198, 0.1); padding: 0.75rem;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; margin-bottom: 0.25rem; color: var(--status-info);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span class="zona-stat-value" style="margin: 0; color: var(--status-info); font-size: 1.1rem;">{{ $selectedZona['tiempo_estimado'] }} min</span>
                        </div>
                        <span class="zona-stat-label" style="font-size: 0.65rem;">Tiempo Estimado</span>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <span class="detail-section-label" style="margin-bottom: 0.5rem;">Domiciliarios asignados ({{ count($selectedZona['domiciliarios']) }}):</span>
                    <div style="max-height: 140px; overflow-y: auto; padding-right: 0.25rem; display: flex; flex-direction: column; gap: 0.4rem;">
                        @forelse($selectedZona['domiciliarios'] as $dom)
                        <div class="domiciliario-row">
                            <div class="dom-avatar">{{ $dom['iniciales'] }}</div>
                            <div class="dom-info">
                                <h4>{{ $dom['nombre'] }}</h4>
                                <p>{{ $dom['vehiculo'] }}</p>
                            </div>
                            <span class="dom-status" style="background: rgba(44,36,27,0.05); color: {{ $dom['estado_color'] === 'success' ? '#7FB77E' : ($dom['estado_color'] === 'warning' ? '#E6B566' : ($dom['estado_color'] === 'info' ? '#7A9CC6' : '#C97C7C')) }};">
                                {{ str_replace('_', ' ', $dom['estado']) }}
                            </span>
                        </div>
                        @empty
                        <p style="color: var(--text-sec); font-style: italic; font-size: 0.85rem; text-align: center; padding: 1rem;">No hay domiciliarios asignados a esta zona.</p>
                        @endforelse
                    </div>
                </div>

                <div style="margin-top: 2.5rem; display: flex; gap: 1rem;">
                    <button class="btn-z btn-z-ghost" style="flex: 1; padding: 0.9rem;" @click="showDetail = false">Cerrar</button>
                    <button class="btn-z btn-z-primary" style="flex: 2; padding: 0.9rem;" @click="showDetail = false" wire:click="openEdit('{{ $selectedZona['id'] }}')">Editar Zona</button>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN CON ALPINE JS (SÚPER RÁPIDO) --}}
    <div class="modal-eliminar-overlay" x-cloak x-show="showModalEliminar">
        <div class="modal-eliminar-caja" @click.away="showModalEliminar = false">
            <div class="modal-eliminar-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="modal-eliminar-titulo">¿Eliminar zona?</h3>
            <p class="modal-eliminar-mensaje">
                Estás a punto de eliminar la zona <strong x-text="deleteName"></strong>. Esta acción no se puede deshacer.
            </p>

            <div class="modal-eliminar-acciones">
                <button type="button" class="btn-modal-cancelar" @click="showModalEliminar = false">Cancelar</button>
                <button type="button" class="btn-modal-eliminar" @click="$wire.eliminarZona(deleteId); showModalEliminar = false">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <style>
        .modal-eliminar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-eliminar-caja {
            background: var(--surface);
            width: 90%;
            max-width: 400px;
            padding: 2.5rem 2rem;
            border-radius: 1.5rem;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-eliminar-icono {
            width: 60px;
            height: 60px;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
        }

        .modal-eliminar-icono svg {
            width: 28px;
            height: 28px;
        }

        .modal-eliminar-titulo {
            font-family: 'DM Serif Display', serif;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--text-main);
        }

        .modal-eliminar-mensaje {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .modal-eliminar-mensaje strong {
            color: var(--text-main);
        }

        .modal-eliminar-acciones {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-modal-cancelar {
            flex: 1;
            padding: 0.875rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid var(--border);
            font-size: 0.95rem;
            background: transparent;
            color: var(--text-main);
        }

        .btn-modal-cancelar:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .btn-modal-eliminar {
            flex: 1;
            padding: 0.875rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.95rem;
            background: #ef4444;
            color: white;
        }

        .btn-modal-eliminar:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>

    <!-- Sidebar Form -->
    <div class="overlay-z" :class="{ 'show': isOpen }" @click="closeSidebar" wire:ignore.self></div>

    <div class="sidebar-z" :class="{ 'open': isOpen }" wire:ignore.self>
        <!-- Overlay de Carga -->
        <div wire:loading.flex wire:target="openEdit" style="position: absolute; inset: 0; background: rgba(253, 251, 247, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center;">
            <style>
                @keyframes spin {
                    to {
                        transform: rotate(360deg);
                    }
                }
            </style>
            <div style="width: 30px; height: 30px; border: 3px solid rgba(224, 122, 95, 0.2); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <span style="margin-top: 0.8rem; font-size: 0.85rem; color: #2C241B; font-weight: 500;">Cargando información...</span>
        </div>

        <h2 style="color: var(--text-main); font-family: 'DM Serif Display', serif; margin-bottom: 1.5rem;">
            {{ $isEdit ? 'Editar Zona' : 'Nueva Zona' }}
        </h2>
        <form wire:submit.prevent="save" style="display: flex; flex-direction: column; height: 100%;">

            <div class="form-group">
                <label class="form-label">Nombre de la Zona</label>
                <input type="text" wire:model="nombre" class="form-input" required placeholder="Ej: Zona Norte">
                @error('nombre') <span style="color: var(--status-error); font-size: 0.75rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Barrios (Separados por comas)</label>
                <textarea wire:model="barrios" class="form-input" rows="4" placeholder="Los Álamos, El Vergel, Mirador..."></textarea>
                <p class="form-help">Escribe los nombres de los barrios que pertenecen a esta zona, separados por comas.</p>
                @error('barrios') <span style="color: var(--status-error); font-size: 0.75rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Costo Envío ($)</label>
                    <input type="number" step="any" wire:model="costo_envio" class="form-input" required>
                    @error('costo_envio') <span style="color: var(--status-error); font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Tiempo (min)</label>
                    <input type="number" wire:model="tiempo_estimado" class="form-input" required>
                    @error('tiempo_estimado') <span style="color: var(--status-error); font-size: 0.75rem;">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción Opcional</label>
                <input type="text" wire:model="descripcion" class="form-input" placeholder="Referencia visual...">
                @error('descripcion') <span style="color: var(--status-error); font-size: 0.75rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Estado</label>
                <select wire:model="activo" class="form-input">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
                @error('activo') <span style="color: var(--status-error); font-size: 0.75rem;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top: auto; display: flex; gap: 1rem; padding-top: 2rem;">
                <button type="button" class="btn-z btn-z-ghost" style="flex: 1;" wire:click="closeSidebar">Cancelar</button>
                <button type="submit" class="btn-z btn-z-primary" style="flex: 2;">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <div class="modal-overlay-detail" :class="{ 'show': showBarrioEditor }" wire:ignore.self
        style="z-index: 3000; align-items: center; padding: 1rem;">
        <div class="modal-zona-detail" style="max-width: 500px; width: 100%; max-height: 92vh; overflow-y: auto; border-radius: 1.25rem;">

            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: #fff; z-index: 1; border-radius: 1.25rem 1.25rem 0 0;">
                <div>
                    <h2 style="font-family: 'DM Serif Display', serif; font-size: 1.15rem; color: var(--text-main); margin: 0; line-height: 1.2;">Ubicar Barrio</h2>
                    <p style="font-size: 0.78rem; color: var(--text-sec); margin: 0.15rem 0 0; font-weight: 600;">{{ $barrioNombre }}</p>
                </div>
                <button wire:click="cerrarBarrioEditor" style="background: rgba(44,36,27,0.06); border: none; color: var(--text-sec); cursor: pointer; width: 32px; height: 32px; border-radius: 8px; display:flex; align-items:center; justify-content:center; flex-shrink: 0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div style="padding: 1.25rem 1.5rem;">

                <p style="font-size: 0.75rem; color: var(--text-sec); margin: 0 0 0.75rem; background: rgba(122,156,198,0.08); border: 1px solid rgba(122,156,198,0.2); border-radius: 0.6rem; padding: 0.5rem 0.75rem;">
                    Haz clic en el mapa para fijar la ubicación del barrio. También puedes escribir las coordenadas.
                </p>

                <div wire:ignore id="mapa-barrio-editor"
                    style="height: 220px; border-radius: 0.75rem; overflow: hidden; border: 1px solid var(--border); margin-bottom: 0.75rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.68rem; margin-bottom: 0.3rem;">Latitud</label>
                        <input type="number" step="any" wire:model.live="barrioLat" id="input-barrio-lat"
                            class="form-input" style="padding: 0.6rem 0.75rem; font-size: 0.85rem;"
                            placeholder="4.7110">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.68rem; margin-bottom: 0.3rem;">Longitud</label>
                        <input type="number" step="any" wire:model.live="barrioLon" id="input-barrio-lon"
                            class="form-input" style="padding: 0.6rem 0.75rem; font-size: 0.85rem;"
                            placeholder="-74.0721">
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border); margin: 0.75rem 0;"></div>

                <p style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-sec); font-weight: 700; margin: 0 0 0.5rem;">Tarifa para este barrio</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div>
                        <label class="form-label" style="font-size: 0.68rem; margin-bottom: 0.3rem;">Costo envío ($)</label>
                        <input type="number" step="any" wire:model="barrioCosto"
                            class="form-input" style="padding: 0.6rem 0.75rem; font-size: 0.85rem;" min="0">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.68rem; margin-bottom: 0.3rem;">Tiempo (min)</label>
                        <input type="number" wire:model="barrioTiempo"
                            class="form-input" style="padding: 0.6rem 0.75rem; font-size: 0.85rem;" min="0">
                    </div>
                </div>

                <label style="display: flex; align-items: center; gap: 0.6rem; background: rgba(44,36,27,0.02); border: 1px solid var(--border); border-radius: 0.6rem; padding: 0.65rem 0.9rem; cursor: pointer; margin-bottom: 1.25rem;">
                    <input type="checkbox" wire:model="barrioActivo" style="width: 16px; height: 16px; accent-color: var(--primary); flex-shrink: 0;">
                    <div>
                        <p style="font-size: 0.82rem; font-weight: 600; color: var(--text-main); margin: 0;">Cobertura activa</p>
                        <p style="font-size: 0.7rem; color: var(--text-sec); margin: 0;">Desmarca para suspender sin eliminar</p>
                    </div>
                </label>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="button" class="btn-z btn-z-ghost" style="flex: 1; padding: 0.75rem; font-size: 0.85rem;"
                        wire:click="cerrarBarrioEditor">Cancelar</button>
                    <button type="button" class="btn-z btn-z-primary" style="flex: 2; padding: 0.75rem; font-size: 0.85rem;"
                        wire:click="guardarBarrioUbicacion">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        Guardar Barrio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            let mapaBarrio = null;
            let markerBarrio = null;

            function cargarLeaflet(callback) {
                // CSS
                if (!document.getElementById('leaflet-css-dyn')) {
                    const css = document.createElement('link');
                    css.id = 'leaflet-css-dyn';
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(css);
                }
                // JS — si ya está disponible, llama directo
                if (window.L) {
                    callback();
                    return;
                }
                const s = document.createElement('script');
                s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                s.crossOrigin = '';
                s.onload = callback;
                document.head.appendChild(s);
            }

            /* ── Espera hasta que el contenedor tenga tamaño real ── */
            function esperarContenedor(callback, intentos) {
                intentos = intentos || 0;
                if (intentos > 40) return; // máx 4 s
                const el = document.getElementById('mapa-barrio-editor');
                if (el && el.offsetWidth > 0 && el.offsetHeight > 0) {
                    callback(el);
                } else {
                    setTimeout(() => esperarContenedor(callback, intentos + 1), 100);
                }
            }

            /* ── Inicializar mapa ── */
            function initMapa(lat, lon) {
                const el = document.getElementById('mapa-barrio-editor');
                if (!el || !window.L) return;

                // Destruir mapa anterior si existe
                if (mapaBarrio) {
                    try {
                        mapaBarrio.remove();
                    } catch (e) {}
                    mapaBarrio = null;
                    markerBarrio = null;
                }

                const okLat = lat && !isNaN(lat);
                const okLon = lon && !isNaN(lon);
                const cLat = okLat ? lat : 4.7110;
                const cLon = okLon ? lon : -74.0721;
                const zoom = okLat ? 15 : 12;

                mapaBarrio = L.map(el, {
                    zoomControl: true
                }).setView([cLat, cLon], zoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(mapaBarrio);

                if (okLat && okLon) {
                    markerBarrio = L.marker([lat, lon], {
                        draggable: true
                    }).addTo(mapaBarrio);
                    markerBarrio.on('dragend', e => syncDesdeMarker(e.target.getLatLng()));
                }

                mapaBarrio.on('click', e => {
                    const {
                        lat,
                        lng
                    } = e.latlng;
                    if (markerBarrio) {
                        markerBarrio.setLatLng([lat, lng]);
                    } else {
                        markerBarrio = L.marker([lat, lng], {
                            draggable: true
                        }).addTo(mapaBarrio);
                        markerBarrio.on('dragend', ev => syncDesdeMarker(ev.target.getLatLng()));
                    }
                    syncDesdeMarker({
                        lat,
                        lng
                    });
                });

                // Forzar refresco de tamaño
                setTimeout(() => {
                    if (mapaBarrio) mapaBarrio.invalidateSize();
                }, 150);
                setTimeout(() => {
                    if (mapaBarrio) mapaBarrio.invalidateSize();
                }, 600);
            }

            /* ── Sincronizar marker → inputs ── */
            function syncDesdeMarker({
                lat,
                lng
            }) {
                const latEl = document.getElementById('input-barrio-lat');
                const lonEl = document.getElementById('input-barrio-lon');
                if (latEl) {
                    latEl.value = parseFloat(lat).toFixed(7);
                    latEl.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
                if (lonEl) {
                    lonEl.value = parseFloat(lng).toFixed(7);
                    lonEl.dispatchEvent(new Event('input', {
                        bubbles: true
                    }));
                }
            }

            /* ── Exponer función global que Alpine llama via $nextTick ── */
            /* Así el mapa se inicia DESPUÉS de que Alpine aplica display:flex */
            window.initBarrioMapaNow = function() {
                cargarLeaflet(() => {
                    const latVal = parseFloat(document.getElementById('input-barrio-lat')?.value) || null;
                    const lonVal = parseFloat(document.getElementById('input-barrio-lon')?.value) || null;
                    initMapa(latVal, lonVal);
                });
            };

            /* ── Sincronizar inputs manuales → marker ── */
            document.addEventListener('input', e => {
                if (e.target.id !== 'input-barrio-lat' && e.target.id !== 'input-barrio-lon') return;
                if (!mapaBarrio || !window.L) return;
                const lat = parseFloat(document.getElementById('input-barrio-lat')?.value);
                const lon = parseFloat(document.getElementById('input-barrio-lon')?.value);
                if (isNaN(lat) || isNaN(lon)) return;
                if (markerBarrio) {
                    markerBarrio.setLatLng([lat, lon]);
                } else {
                    markerBarrio = L.marker([lat, lon], {
                        draggable: true
                    }).addTo(mapaBarrio);
                    markerBarrio.on('dragend', ev => syncDesdeMarker(ev.target.getLatLng()));
                }
                mapaBarrio.setView([lat, lon], 15);
            });
        })();
    </script>

</div>