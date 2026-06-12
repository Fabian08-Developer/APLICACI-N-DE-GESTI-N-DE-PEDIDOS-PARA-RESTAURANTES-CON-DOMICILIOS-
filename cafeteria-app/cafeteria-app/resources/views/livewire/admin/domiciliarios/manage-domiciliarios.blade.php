@section('titulo', 'Domiciliarios')
<div x-data="{ 
        isOpen: {{ $errors->any() ? 'true' : 'false' }},
        showModalEliminar: false,
        deleteId: '',
        deleteName: ''
     }"
     @open-sidebar.window="isOpen = true"
     @close-sidebar.window="isOpen = false">
    <style>
        :root {
            --d-primary: #c9a84c;
            --d-success: var(--status-success);
            --d-warning: var(--status-warning);
            --d-destructive: var(--status-error);
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
            margin-bottom: 0.3rem;
        }

        .page-subtitle {
            color: var(--text-sec);
            font-size: 0.875rem;
        }

        /* Tabs Nav (Estilo unificado con Pedidos) */
        .tabs-container {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(44, 36, 27, 0.07);
            flex-wrap: wrap;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: rgba(44, 36, 27, 0.6);
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: #2C241B;
            background: rgba(255, 255, 255, 1);
        }

        .tab-btn.active {
            color: #E07A5F;
            background: rgba(201, 168, 76, 0.08);
            border-bottom: 2px solid #E07A5F;
            border-radius: 8px 8px 0 0;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.2rem;
            margin-bottom: 2.5rem;
        }

        .card-d {
            background: #FFFFFF;
            border: 1px solid rgba(196, 139, 87, 0.15);
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
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

        .btn-ver { color: var(--status-info); }
        .btn-ver:hover { background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.2); }

        .btn-editar { color: var(--d-primary); }
        .btn-editar:hover { background: rgba(201, 168, 76, 0.1); border-color: rgba(201, 168, 76, 0.2); }

        .btn-liquidar { background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2); }
        .btn-liquidar:hover { background: rgba(245, 158, 11, 0.2); transform: scale(1.05); }

        .btn-eliminar { color: var(--status-error); }
        .btn-eliminar:hover { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); }

        .card-d-content {
            padding: 1.5rem;
        }

        .stat-value {
            font-family: 'DM Serif Display', serif;
            font-size: 2.2rem;
            line-height: 1;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .stat-value.success {
            color: var(--d-success);
        }

        .stat-value.accent {
            color: var(--status-info);
        }

        .stat-value.warning {
            color: var(--d-warning);
        }

        .stat-value.destructive {
            color: var(--d-destructive);
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-sec);
            font-weight: 600;
        }

        /* Table Styles */
        .table-wrapper {
            overflow-x: auto;
        }

        .table-d {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .table-d th {
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-sec);
            border-bottom: 1px solid var(--border);
            font-weight: 700;
        }

        .table-d td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .driver-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: var(--bg-main);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--d-primary);
            border: 1px solid var(--border);
        }

        .driver-name {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .driver-date {
            font-size: 0.75rem;
            color: var(--text-sec);
        }

        .btn-d {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-d-primary {
            background-color: #E07A5F;
            color: #FFFFFF;
        }
        .btn-d-primary:hover {
            background-color: #D4694D;
            box-shadow: 0 4px 12px rgba(224, 122, 95, 0.3);
        }

        .btn-d-ghost {
            background: transparent;
            color: var(--text-sec);
            border: 1px solid var(--border);
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 180px;
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            z-index: 100;
            display: none;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.8rem;
            border-radius: 0.5rem;
            color: var(--text-sec);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
            text-decoration: none;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background-color: var(--hover-soft);
            color: var(--text-main);
        }

        .dropdown-item.danger:hover {
            color: var(--status-error);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.show {
            display: flex;
        }



        /* Sidebar */
        .sidebar-form-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }

        .sidebar-form-overlay.show {
            display: block;
        }

        .sidebar-form {
            position: fixed;
            top: 0;
            right: -450px;
            width: 450px;
            height: 100vh;
            background-color: #FFFFFF;
            z-index: 1001;
            transition: right 0.3s;
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 25px rgba(0,0,0,0.05);
        }

        .sidebar-form.open {
            right: 0;
        }

        .sidebar-form-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-form-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .sidebar-form-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-sec);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            color: var(--text-main);
        }

        .select-d {
            padding: 0.75rem 2rem 0.75rem 1rem;
            background-color: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            color: var(--text-main);
            font-size: 0.875rem;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394A3B8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        /* Modal Detalle Premium Styles */
        .modal-content-d {
            background-color: #FFFFFF;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 460px;
            border-radius: 1.25rem;
            padding: 1.25rem;
        }

        .modal-header-d {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-close {
            background: rgba(0, 0, 0, 0.05);
            border: none;
            color: var(--text-sec);
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: var(--text-main);
        }

        .detail-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .avatar-lg {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--d-primary), #d97706);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            box-shadow: 0 10px 15px -3px rgba(224, 122, 95, 0.3);
            flex-shrink: 0;
        }

        .detail-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.1rem;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .badge-status {
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-main);
        }

        .info-card {
            background: var(--surface);
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            color: var(--text-sec);
            font-size: 0.85rem;
        }

        .info-row:not(:last-child) {
            border-bottom: 1px solid var(--border);
        }

        .info-row svg {
            color: var(--d-primary);
            opacity: 0.8;
        }

        .modal-footer-d {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-close-modal {
            padding: 0.75rem;
            background: transparent;
            color: var(--text-sec);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-close-modal:hover {
            background: rgba(0, 0, 0, 0.05);
            color: var(--text-main);
        }

        .btn-edit-modal {
            padding: 0.75rem;
            background: var(--d-primary);
            color: #FFFFFF;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-edit-modal:hover {
            background: #D4694D;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(224, 122, 95, 0.2);
        }

        /* Barrios Selector Styles */
        .barrios-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
            margin-top: 0.75rem;
            max-height: 200px;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.02);
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
        }

        .barrio-checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .barrio-checkbox-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .barrio-checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--d-primary);
            cursor: pointer;
        }

        .barrio-checkbox-item span {
            font-size: 0.875rem;
            color: var(--text-sec);
        }

        .barrio-checkbox-item input:checked+span {
            color: var(--text-main);
            font-weight: 600;
        }

        .detail-barrios {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .barrio-tag {
            background: rgba(201, 168, 76, 0.1);
            color: var(--d-primary);
            border: 1px solid rgba(201, 168, 76, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
        }
    </style>

    <div class="page-header">
        <div>
            <h1 class="page-title">Domiciliarios</h1>
            <p class="page-subtitle">Gestión y monitoreo del equipo de entregas</p>
        </div>
        <button class="btn-d btn-d-primary" @click="isOpen = true; openCreateForm()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Nuevo Domiciliario
        </button>
    </div>

    <!-- Stats Dinámicos -->
    <div class="stats-grid">
        <div class="card-d stat-card">
            <div class="card-d-content">
                <p class="stat-value">{{ $stats['total'] }}</p>
                <p class="stat-label">Total</p>
            </div>
        </div>
        <div class="card-d stat-card">
            <div class="card-d-content">
                <p class="stat-value success">{{ $stats['disponibles'] }}</p>
                <p class="stat-label">Disponibles</p>
            </div>
        </div>
        <div class="card-d stat-card">
            <div class="card-d-content">
                <p class="stat-value accent">{{ $stats['en_ruta'] }}</p>
                <p class="stat-label">En Ruta</p>
            </div>
        </div>
        <div class="card-d stat-card">
            <div class="card-d-content">
                <p class="stat-value warning">{{ $stats['ocupados'] }}</p>
                <p class="stat-label">Ocupados</p>
            </div>
        </div>
        <div class="card-d stat-card">
            <div class="card-d-content">
                <p class="stat-value destructive">{{ $stats['fuera_servicio'] }}</p>
                <p class="stat-label">Fuera de Servicio</p>
            </div>
        </div>
    </div>

    <!-- Tabs Nav -->
    <div class="tabs-container">
        <button class="tab-btn {{ $activeTab === 'domiciliarios' ? 'active' : '' }}" wire:click="setTab('domiciliarios')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Domiciliarios
        </button>
        <button class="tab-btn {{ $activeTab === 'liquidaciones' ? 'active' : '' }}" wire:click="setTab('liquidaciones')">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            Liquidaciones
        </button>
    </div>

    @if($activeTab === 'domiciliarios')

    {{-- RF-135: Barra de búsqueda + RF-136: Filtros de estado --}}
    <div class="card-d" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px; position: relative;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-sec);">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input wire:model.live.debounce.300ms="busqueda" type="text"
                    placeholder="Buscar por nombre o teléfono..."
                    class="form-input" style="padding-left: 2.5rem;">
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                @foreach([''=>'Todos','disponible'=>'Disponible','en_ruta'=>'En Ruta','ocupado'=>'Ocupado','no_disponible'=>'Fuera de Servicio'] as $val => $label)
                <button wire:click="setFiltroEstado('{{ $val }}')"
                    style="padding: 0.5rem 1rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; border: 1px solid var(--border); transition: all 0.2s;
                    background: {{ $filtroEstado === $val ? 'var(--primary)' : 'var(--surface)' }};
                    color: {{ $filtroEstado === $val ? '#0f0f0f' : 'var(--text-sec)' }};">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card-d">
        <div class="table-wrapper">
            <table class="table-d">
                <thead>
                    <tr>
                        <th>Domiciliario</th>
                        <th>Contacto</th>
                        <th>Vehículo</th>
                        <th>Zona</th>
                        <th style="text-align: center;">Pedidos Hoy</th>
                        <th style="text-align: center;">Calificación</th>
                        <th>Efectivo Pend.</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domiciliarios as $dom)
                    <tr>
                        <td>
                            <div class="driver-info">
                                <div class="driver-avatar">{{ $dom->iniciales }}</div>
                                <div>
                                    <p class="driver-name">{{ $dom->nombre }}</p>
                                    <p class="driver-date">Desde {{ $dom->creado_en?->format('d/m/Y') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $dom->telefono }}</td>
                        <td>{{ ucfirst($dom->tipo_vehiculo) }} ({{ $dom->placa ?? 'N/A' }})</td>
                        <td>{{ $dom->zona->nombre ?? 'N/A' }}</td>
                        <td style="text-align: center;">{{ $dom->pedidos_hoy }}</td>
                        {{-- RF-133: Columna Calificación --}}
                        <td style="text-align: center;">
                            <span style="color: #f59e0b; font-weight: 700;">★</span>
                            <span style="color: var(--text-main); font-weight: 600;">{{ number_format($dom->calificacion, 1) }}</span>
                        </td>
                        {{-- RF-137: Efectivo pendiente con alerta si supera límite --}}
                        <td>
                            @if($dom->efectivo_pendiente > 0)
                                <span style="color: {{ $dom->tiene_bloqueo ? 'var(--status-error)' : 'var(--status-warning)' }}; font-weight: 700;">
                                    ${{ number_format($dom->efectivo_pendiente, 0, ',', '.') }}
                                </span>
                                @if($dom->tiene_bloqueo)
                                    <span style="font-size: 0.7rem; color: var(--status-error); display: block;">⚠ Límite superado</span>
                                @endif
                            @else
                                <span style="color: var(--text-sec); font-size: 0.8rem;">$0</span>
                            @endif
                        </td>
                        <td>
                            <select class="select-d" wire:change="updateEstado('{{ $dom->id }}', $event.target.value)" style="padding: 0.4rem 1.5rem 0.4rem 0.7rem; font-size: 0.8rem;">
                                <option value="disponible" {{ $dom->estado == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                <option value="en_ruta" {{ $dom->estado == 'en_ruta' ? 'selected' : '' }}>En Ruta</option>
                                <option value="ocupado" {{ $dom->estado == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                <option value="no_disponible" {{ $dom->estado == 'no_disponible' ? 'selected' : '' }}>Fuera de Servicio</option>
                            </select>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <button type="button" class="btn-acciones btn-ver" onclick="viewDetail('{{ $dom->id }}')" title="Ver detalle">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                                <button type="button" class="btn-acciones btn-editar" @click="isOpen = true; openEditForm('{{ $dom->id }}')" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                @if($dom->efectivo_pendiente > 0)
                                <button type="button" class="btn-acciones btn-liquidar" wire:click="iniciarLiquidacion('{{ $dom->id }}')" title="Liquidar (${{ number_format($dom->efectivo_pendiente, 0, ',', '.') }})">
                                    💰
                                </button>
                                @endif
                                <button type="button" class="btn-acciones btn-eliminar" 
                                        @click.prevent.stop="deleteId = '{{ $dom->id }}'; deleteName = '{{ addslashes($dom->nombre) }}'; showModalEliminar = true;" 
                                        title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-sec);">No se encontraron domiciliarios.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @elseif($activeTab === 'liquidaciones')
    <div class="card-d">
        <div style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
            <h3 style="font-family: 'DM Serif Display', serif; font-size: 1.4rem; color: var(--text-main); margin: 0;">Historial General de Liquidaciones</h3>
            <p style="color: var(--text-sec); font-size: 0.85rem; margin-top: 4px;">Todas las liquidaciones de efectivo realizadas a los domiciliarios.</p>
        </div>
        <div class="table-wrapper">
            <table class="table-d">
                <thead>
                    <tr>
                        <th>ID / Fecha</th>
                        <th>Domiciliario</th>
                        <th>Monto Liquidado</th>
                        <th>Aprobador</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todasLiquidaciones as $liq)
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);">#{{ substr($liq->id, 0, 8) }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-sec);">{{ \Carbon\Carbon::parse($liq->liquidado_en)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="driver-avatar" style="width: 32px; height: 32px; font-size: 0.8rem;">{{ $liq->perfil->iniciales ?? '?' }}</div>
                                <span style="font-weight: 600; color: var(--text-main);">{{ $liq->perfil->nombre ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            <span style="color: #22c55e; font-weight: 700;">${{ number_format($liq->monto, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <div style="font-size: 0.9rem; color: var(--text-main);">{{ $liq->aprobador->nombre ?? 'Administrador' }}</div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--text-sec); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $liq->notas }}">
                                {{ $liq->notas ?: '--' }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-sec);">No hay liquidaciones registradas en esta sucursal.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- RF-138: Modal de confirmación de Liquidación --}}
    @if($liquidandoDom)
    <div class="modal-overlay show" wire:ignore.self>
        <div class="modal-content-d" style="max-width: 360px;">
            <div class="modal-header-d">
                <h2 style="color: var(--text-main); font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; font-family: 'DM Serif Display', serif;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--d-primary);"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                    Liquidación de Caja
                </h2>
                <button class="modal-close" wire:click="cancelarLiquidacion">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="width: 60px; height: 60px; background: rgba(224, 122, 95, 0.1); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--d-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <p style="color: var(--text-sec); font-size: 0.9rem;">Registrar entrega de efectivo de</p>
                <p style="color: var(--text-main); font-weight: 700; font-size: 1.1rem; margin-top: 4px;">{{ $liquidandoDom->nombre }}</p>
            </div>

            <div style="background: rgba(224, 122, 95, 0.05); border: 1px solid rgba(224, 122, 95, 0.2); border-radius: 1rem; padding: 1rem; text-align: center; margin-bottom: 1rem;">
                <p style="font-size: 0.7rem; color: var(--d-primary); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Monto a liquidar</p>
                <p style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 2px;">
                    ${{ number_format($montoLiquidacion, 0, ',', '.') }}
                </p>
                <p style="font-size: 0.7rem; color: var(--text-sec); margin-top: 2px;">COP · Efectivo pdte.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Notas (opcional)</label>
                <textarea wire:model="notasLiquidacion" class="form-input" rows="2"
                    placeholder="Ej: Entrega recibida en sede. Todo correcto."
                    style="resize: none; font-size: 0.875rem;"></textarea>
            </div>

            <p style="font-size: 0.8rem; color: var(--text-sec); margin-bottom: 1.25rem; text-align: center;">
                Al confirmar, el saldo quedará en <strong style="color: var(--d-success);">$0</strong> y se enviará un comprobante por email.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button class="btn-close-modal" wire:click="cancelarLiquidacion">Cancelar</button>
                <button class="btn-edit-modal" wire:click="confirmarLiquidacion" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Confirmar Liquidación
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN CON ALPINE JS (SÚPER RÁPIDO) --}}
    <div class="modal-eliminar-overlay" x-cloak x-show="showModalEliminar">
        <div class="modal-eliminar-caja" @click.away="showModalEliminar = false">
            <div class="modal-eliminar-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="modal-eliminar-titulo">¿Eliminar domiciliario?</h3>
            <p class="modal-eliminar-mensaje">
                Estás a punto de eliminar a <strong x-text="deleteName"></strong> y su cuenta de usuario. Esta acción no se puede deshacer.
            </p>
            
            <div class="modal-eliminar-acciones">
                <button type="button" class="btn-modal-cancelar" @click="showModalEliminar = false">Cancelar</button>
                <button type="button" class="btn-modal-eliminar" @click="$wire.eliminarDomiciliario(deleteId); showModalEliminar = false">Sí, eliminar</button>
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
            background: rgba(0,0,0,0.05);
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
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>

    <!-- Modal Detalle -->
    <div class="modal-overlay" id="detailModal" wire:ignore>
        <div class="modal-content-d">
            <div class="modal-header-d">
                <h2 style="color: var(--text-main); font-size: 1.1rem; font-family: 'DM Serif Display', serif;">Detalle del Domiciliario</h2>
                <button class="modal-close" onclick="closeDetailModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="detail-profile">
                <div class="avatar-lg" id="detAvatar">--</div>
                <div>
                    <h3 class="detail-name" id="detName">--</h3>
                    <span class="badge-status" id="detBadge">--</span>
                </div>
            </div>
            <!-- Nuevos datos añadidos -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                <div style="background: var(--surface); padding: 0.75rem 0.5rem; border-radius: 0.75rem; text-align: center; border: 1px solid var(--border);">
                    <div style="font-size: 0.65rem; color: var(--text-sec); margin-bottom: 0.25rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Pedidos Hoy</div>
                    <div style="font-size: 1.1rem; color: var(--text-main); font-weight: 700;" id="detOrders">--</div>
                </div>
                <div style="background: var(--surface); padding: 0.75rem 0.5rem; border-radius: 0.75rem; text-align: center; border: 1px solid var(--border);">
                    <div style="font-size: 0.65rem; color: var(--text-sec); margin-bottom: 0.25rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Calificación</div>
                    <div style="font-size: 1.1rem; color: #f59e0b; font-weight: 700;"><span style="font-size: 0.9rem; margin-right: 2px;">★</span><span id="detRating">--</span></div>
                </div>
                <div style="background: var(--surface); padding: 0.75rem 0.5rem; border-radius: 0.75rem; text-align: center; border: 1px solid var(--border);">
                    <div style="font-size: 0.65rem; color: var(--text-sec); margin-bottom: 0.25rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Efectivo Pend.</div>
                    <div style="font-size: 1.1rem; color: var(--d-success); font-weight: 700;" id="detCash">--</div>
                </div>
            </div>

            <div class="info-card">
                <div class="info-row"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"></path>
                    </svg> <span id="detPhone">--</span></div>
                <div class="info-row" id="detVehicle">--</div>
                <div class="info-row" id="detZone">--</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; margin-top: 1rem;">
                <!-- Historial de Liquidaciones -->
                <div>
                    <h3 style="color: var(--text-main); font-family: 'DM Serif Display', serif; font-size: 0.95rem; margin-bottom: 0.5rem; padding-bottom: 0.25rem; border-bottom: 1px solid var(--border);">Historial Liquidaciones</h3>
                    <div id="detLiquidaciones" style="max-height: 110px; overflow-y: auto; padding-right: 0.25rem; font-size: 0.85rem;">
                        <!-- Se llenará vía JS -->
                    </div>
                </div>

                <!-- Últimas Calificaciones -->
                <div>
                    <h3 style="color: var(--text-main); font-family: 'DM Serif Display', serif; font-size: 0.95rem; margin-bottom: 0.5rem; padding-bottom: 0.25rem; border-bottom: 1px solid var(--border);">Últimas Calificaciones</h3>
                    <div id="detCalificaciones" style="max-height: 110px; overflow-y: auto; padding-right: 0.25rem; font-size: 0.85rem;">
                        <!-- Se llenará vía JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer-d">
                <button class="btn-close-modal" onclick="closeDetailModal()">Cerrar</button>
                <button class="btn-edit-modal" id="btnEditFromDetail">Editar</button>
            </div>
        </div>
    </div>

    <!-- Sidebar Form -->
    <div class="sidebar-form-overlay" id="formOverlay" :class="{ 'show': isOpen }" @click="isOpen = false" wire:ignore></div>
    <div class="sidebar-form" id="sidebarForm" :class="{ 'open': isOpen }" wire:ignore>
        <div class="sidebar-form-header">
            <h2 id="formTitle" style="color: var(--text-main); font-family: 'DM Serif Display', serif;">Nuevo Domiciliario</h2>
        </div>
        <div class="sidebar-form-content" style="position: relative;">
            <div id="formLoadingOverlay" style="display: none; position: absolute; inset: 0; background: rgba(253, 251, 247, 0.7); backdrop-filter: blur(2px); z-index: 10; flex-direction: column; align-items: center; justify-content: center;">
                <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                <div style="width: 30px; height: 30px; border: 3px solid rgba(224, 122, 95, 0.2); border-top-color: #E07A5F; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <span style="margin-top: 0.8rem; font-size: 0.85rem; color: #2C241B; font-weight: 500;">Cargando información...</span>
            </div>
            <form id="mainForm" method="POST">
                @csrf
                <div id="methodField"></div>
                
                @if($errors->any())
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; color: #f87171; font-size: 0.85rem;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre" id="inName" class="form-input" required placeholder="Ej: Juan Pérez"
                           oninput="this.value = this.value.replace(/[\u{1F300}-\u{1FAFF}]|[\u{2600}-\u{27BF}]/gu, '')">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" id="inPhone" class="form-input" required placeholder="300 000 0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Zona de Trabajo</label>
                    <select name="zona_id" id="inZoneId" class="select-d" style="width:100%">
                        <option value="">Seleccione una zona</option>
                        @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo de Vehículo</label>
                    <select name="vehiculo_tipo" id="inVehicleType" class="select-d" style="width:100%">
                        <option value="moto">Moto</option>
                        <option value="bicicleta">Bicicleta</option>
                        <option value="carro">Carro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Placa (Opcional)</label>
                    <input type="text" name="placa" id="inPlate" class="form-input" placeholder="ABC-123">
                </div>
            </form>
        </div>
        <div class="sidebar-form-footer">
            <button class="btn-d btn-d-ghost" style="flex: 1" @click="isOpen = false">Cancelar</button>
            <button type="submit" form="mainForm" class="btn-d btn-d-primary" style="flex: 2">Guardar Cambios</button>
        </div>
    </div>

    <script>
        function toggleDropdown(btn) {
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== btn.nextElementSibling) m.classList.remove('show');
            });
            btn.nextElementSibling.classList.toggle('show');
        }

        function viewDetail(id) {
            fetch(`/admin/domiciliarios/${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const dom = res.data;
                        document.getElementById('detName').innerText = dom.nombre;
                        document.getElementById('detAvatar').innerText = res.iniciales;
                        document.getElementById('detPhone').innerText = dom.telefono;
                        document.getElementById('detBadge').innerText = dom.estado;
                        document.getElementById('detBadge').style.color = `var(--status-${res.estado_color})`;
                        document.getElementById('detVehicle').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg> ${dom.vehiculo_tipo.charAt(0).toUpperCase() + dom.vehiculo_tipo.slice(1)} ${dom.placa ? '(' + dom.placa + ')' : ''}`;
                        document.getElementById('detZone').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> ${dom.zona ? dom.zona.nombre : 'Sin zona'}`;

                        document.getElementById('detOrders').innerText = dom.pedidos_hoy || '0';
                        document.getElementById('detRating').innerText = dom.calificacion ? Number(dom.calificacion).toFixed(1) : '0.0';
                        
                        const formatCurrency = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format;
                        document.getElementById('detCash').innerText = formatCurrency(dom.efectivo_pendiente || 0);
                        
                        if (dom.efectivo_pendiente > 0) {
                            document.getElementById('detCash').style.color = dom.tiene_bloqueo ? '#ef4444' : '#f59e0b';
                        } else {
                            document.getElementById('detCash').style.color = '#10b981';
                        }

                        // Poblar historial de liquidaciones
                        const liqContainer = document.getElementById('detLiquidaciones');
                        if (dom.liquidaciones && dom.liquidaciones.length > 0) {
                            let liqHtml = '';
                            dom.liquidaciones.forEach(liq => {
                                liqHtml += `
                                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.5rem; padding: 0.75rem; margin-bottom: 0.5rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="color: #22c55e; font-weight: 600;">${formatCurrency(liq.monto)}</span>
                                        <span style="color: #94A3B8; font-size: 0.8rem;">${liq.fecha}</span>
                                    </div>
                                    <div style="color: #cbd5e1; font-size: 0.8rem; margin-bottom: 0.25rem;">Aprobado por: ${liq.aprobador}</div>
                                    ${liq.notas ? `<div style="color: #94A3B8; font-size: 0.75rem; font-style: italic;">Notas: ${liq.notas}</div>` : ''}
                                </div>`;
                            });
                            liqContainer.innerHTML = liqHtml;
                        } else {
                            liqContainer.innerHTML = '<div style="color: #94A3B8; font-size: 0.85rem; text-align: center; padding: 1rem 0;">No hay liquidaciones registradas.</div>';
                        }

                        // Poblar historial de calificaciones
                        const califContainer = document.getElementById('detCalificaciones');
                        if (dom.calificaciones && dom.calificaciones.length > 0) {
                            let califHtml = '';
                            dom.calificaciones.forEach(cal => {
                                let starsHtml = '';
                                for (let i = 1; i <= 5; i++) {
                                    if (i <= cal.puntuacion) {
                                        starsHtml += '<span style="color: #f59e0b; font-size: 1rem; margin-right: 2px;">★</span>';
                                    } else {
                                        starsHtml += '<span style="color: #e2e8f0; font-size: 1rem; margin-right: 2px;">★</span>';
                                    }
                                }

                                califHtml += `
                                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem; margin-bottom: 0.5rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; align-items: center;">
                                        <div style="color: var(--text-main); font-weight: 600; font-size: 0.85rem;">${cal.cliente}</div>
                                        <div style="color: var(--text-sec); font-size: 0.75rem;">${cal.fecha}</div>
                                    </div>
                                    <div style="margin-bottom: 0.25rem;">
                                        ${starsHtml}
                                    </div>
                                    ${cal.comentario ? `<div style="color: var(--text-sec); font-size: 0.8rem; font-style: italic; margin-top: 0.25rem;">"${cal.comentario}"</div>` : ''}
                                </div>`;
                            });
                            califContainer.innerHTML = califHtml;
                        } else {
                            califContainer.innerHTML = '<div style="color: #94A3B8; font-size: 0.85rem; text-align: center; padding: 1rem 0;">Aún no hay calificaciones.</div>';
                        }

                        document.getElementById('btnEditFromDetail').onclick = () => {
                            window.dispatchEvent(new CustomEvent('open-sidebar'));
                            openEditForm(id);
                        };
                        document.getElementById('detailModal').classList.add('show');
                    }
                });
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('show');
        }



        function openCreateForm() {
            document.getElementById('formTitle').innerText = "Nuevo Domiciliario";
            document.getElementById('mainForm').action = "{{ '#' }}";
            document.getElementById('methodField').innerHTML = '';
            document.getElementById('mainForm').reset();
            if(document.getElementById('formLoadingOverlay')) document.getElementById('formLoadingOverlay').style.display = 'none';
            window.dispatchEvent(new CustomEvent('open-sidebar'));
        }

        function openEditForm(id) {
            closeDetailModal();
            document.getElementById('formTitle').innerText = "Editar Domiciliario";
            if(document.getElementById('formLoadingOverlay')) document.getElementById('formLoadingOverlay').style.display = 'flex';
            window.dispatchEvent(new CustomEvent('open-sidebar'));

            fetch(`/admin/domiciliarios/${id}`)
                .then(res => res.json())
                .then(res => {
                    if(document.getElementById('formLoadingOverlay')) document.getElementById('formLoadingOverlay').style.display = 'none';
                    if (res.success) {
                        const dom = res.data;
                        document.getElementById('formTitle').innerText = "Editar Domiciliario";
                        document.getElementById('mainForm').action = `/admin/domiciliarios/${id}`;
                        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
                        document.getElementById('inName').value = dom.nombre;
                        document.getElementById('inPhone').value = dom.telefono;
                        document.getElementById('inZoneId').value = dom.zona_id;
                        document.getElementById('inVehicleType').value = dom.vehiculo_tipo;
                        document.getElementById('inPlate').value = dom.placa || '';
                    }
                })
                .catch(() => {
                    if(document.getElementById('formLoadingOverlay')) document.getElementById('formLoadingOverlay').style.display = 'none';
                    document.getElementById('formTitle').innerText = "Error al cargar";
                });
        }

        function closeSidebarForm() {
            window.dispatchEvent(new CustomEvent('close-sidebar'));
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
            }
        });
    </script>
</div>