@extends('admin.Layout')

@section('contenido')
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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.2rem;
        margin-bottom: 2.5rem;
    }

    .card-d {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 1.25rem;
        overflow: hidden;
        transition: transform 0.2s, border-color 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

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

    .stat-value.success { color: var(--d-success); }
    .stat-value.accent { color: var(--status-info); }
    .stat-value.warning { color: var(--d-warning); }
    .stat-value.destructive { color: var(--d-destructive); }

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
        background-color: var(--d-primary);
        color: #0f0f0f;
    }

    .btn-d-ghost {
        background: transparent;
        color: var(--text-sec);
        border: 1px solid var(--border);
    }

    /* Dropdown */
    .dropdown { position: relative; }
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
    .dropdown-menu.show { display: block; }
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
    .dropdown-item:hover { background-color: var(--hover-soft); color: var(--text-main); }
    .dropdown-item.danger:hover { color: var(--status-error); }

    /* Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.7);
        backdrop-filter: blur(8px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.show { display: flex; }
    .modal-content-d {
        background-color: #0B1120;
        width: 100%;
        max-width: 500px;
        border-radius: 1.5rem;
        padding: 2rem;
        border: 1px solid rgba(255,255,255,0.05);
    }

    /* Sidebar */
    .sidebar-form-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: none;
    }
    .sidebar-form-overlay.show { display: block; }
    .sidebar-form {
        position: fixed;
        top: 0;
        right: -450px;
        width: 450px;
        height: 100vh;
        background-color: #0F172A;
        z-index: 1001;
        transition: right 0.3s;
        border-left: 1px solid var(--border);
        display: flex;
        flex-direction: column;
    }
    .sidebar-form.open { right: 0; }
    .sidebar-form-header { padding: 1.5rem; border-bottom: 1px solid var(--border); }
    .sidebar-form-content { flex: 1; overflow-y: auto; padding: 1.5rem; }
    .sidebar-form-footer { padding: 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 1rem; }

    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-sec); text-transform: uppercase; margin-bottom: 0.5rem; }
    .form-input { width: 100%; padding: 0.75rem 1rem; background-color: var(--surface); border: 1px solid var(--border); border-radius: 0.75rem; color: var(--text-main); }
    .select-d { padding: 0.75rem 2rem 0.75rem 1rem; background-color: var(--bg-main); border: 1px solid var(--border); border-radius: 0.75rem; color: var(--text-main); font-size: 0.875rem; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394A3B8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem; }

    /* Modal Detalle Premium Styles */
    .modal-content-d {
        background-color: #0F172A;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .modal-header-d {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .modal-close {
        background: rgba(255,255,255,0.05);
        border: none;
        color: var(--text-sec);
        padding: 0.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .modal-close:hover { background: rgba(255,255,255,0.1); color: #fff; }
    
    .detail-profile {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 2rem;
    }
    .avatar-lg {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--status-info), #3b82f6);
        border-radius: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 1rem;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }
    .detail-name {
        font-family: 'DM Serif Display', serif;
        font-size: 1.5rem;
        color: #fff;
        margin-bottom: 0.5rem;
    }
    .badge-status {
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(255,255,255,0.05);
    }

    .info-card {
        background: rgba(255,255,255,0.03);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 2rem;
    }
    .info-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        color: var(--text-sec);
        font-size: 0.9rem;
    }
    .info-row:not(:last-child) { border-bottom: 1px solid rgba(255,255,255,0.05); }
    .info-row svg { color: var(--status-info); opacity: 0.8; }

    .modal-footer-d {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .btn-close-modal {
        padding: 0.75rem;
        background: rgba(255,255,255,0.05);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 0.75rem;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-close-modal:hover { background: rgba(255,255,255,0.1); }
    .btn-edit-modal {
        padding: 0.75rem;
        background: var(--d-primary);
        color: #0f0f0f;
        border: none;
        border-radius: 0.75rem;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-edit-modal:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2); }

    /* Barrios Selector Styles */
    .barrios-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
        margin-top: 0.75rem;
        max-height: 200px;
        overflow-y: auto;
        background: rgba(255,255,255,0.02);
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
    .barrio-checkbox-item:hover { background: rgba(255,255,255,0.05); }
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
    .barrio-checkbox-item input:checked + span {
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
    <button class="btn-d btn-d-primary" onclick="openCreateForm()">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nuevo Domiciliario
    </button>
</div>


<!-- Stats Dinámicos -->
<div class="stats-grid">
    <div class="card-d stat-card"><div class="card-d-content"><p class="stat-value">{{ $stats['total'] }}</p><p class="stat-label">Total</p></div></div>
    <div class="card-d stat-card"><div class="card-d-content"><p class="stat-value success">{{ $stats['disponibles'] }}</p><p class="stat-label">Disponibles</p></div></div>
    <div class="card-d stat-card"><div class="card-d-content"><p class="stat-value accent">{{ $stats['en_ruta'] }}</p><p class="stat-label">En Ruta</p></div></div>
    <div class="card-d stat-card"><div class="card-d-content"><p class="stat-value warning">{{ $stats['ocupados'] }}</p><p class="stat-label">Ocupados</p></div></div>
    <div class="card-d stat-card"><div class="card-d-content"><p class="stat-value destructive">{{ $stats['fuera_servicio'] }}</p><p class="stat-label">Fuera de Servicio</p></div></div>
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
                    <th>Pedidos</th>
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
                                <p class="driver-date">Desde {{ $dom->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </td>
                    <td>{{ $dom->telefono }}</td>
                    <td>{{ ucfirst($dom->vehiculo_tipo) }} ({{ $dom->placa ?? 'N/A' }})</td>
                    <td>{{ $dom->zona->nombre ?? 'N/A' }}</td>
                    <td>{{ $dom->pedidos_hoy }}</td>
                    <td>
                        <form action="{{ route('admin.domiciliarios.update', $dom->id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="nombre" value="{{ $dom->nombre }}">
                            <input type="hidden" name="telefono" value="{{ $dom->telefono }}">
                            <input type="hidden" name="vehiculo_tipo" value="{{ $dom->vehiculo_tipo }}">
                            <input type="hidden" name="zona_id" value="{{ $dom->zona_id }}">
                            <select name="estado" class="select-d" onchange="this.form.submit()" style="padding: 0.4rem 1.5rem 0.4rem 0.7rem; font-size: 0.8rem;">
                                <option value="disponible" {{ $dom->estado == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                <option value="en_ruta" {{ $dom->estado == 'en_ruta' ? 'selected' : '' }}>En Ruta</option>
                                <option value="ocupado" {{ $dom->estado == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                <option value="fuera_servicio" {{ $dom->estado == 'fuera_servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn-d btn-d-ghost" onclick="toggleDropdown(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" onclick="viewDetail({{ $dom->id }})">Ver detalle</button>
                                <button class="dropdown-item" onclick="openEditForm({{ $dom->id }})">Editar</button>
                                <form action="{{ route('admin.domiciliarios.destroy', $dom->id) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item danger">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align: center; padding: 3rem;">No hay domiciliarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-content-d">
        <div class="modal-header-d">
            <h2 style="color: #fff; font-size: 1.25rem;">Detalle del Domiciliario</h2>
            <button class="modal-close" onclick="closeDetailModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="detail-profile">
            <div class="avatar-lg" id="detAvatar">--</div>
            <div>
                <h3 class="detail-name" id="detName">--</h3>
                <span class="badge-status" id="detBadge">--</span>
            </div>
        </div>
        <div class="info-card">
            <div class="info-row"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"></path></svg> <span id="detPhone">--</span></div>
            <div class="info-row" id="detVehicle">--</div>
            <div class="info-row" id="detZone">--</div>
            <div class="info-row" style="flex-direction: column; align-items: flex-start; border-bottom: none;">
                <span class="form-label" style="margin-bottom: 0.5rem; font-size: 0.65rem;">Barrios Asignados</span>
                <div class="detail-barrios" id="detBarrios">
                    <span class="texto-gris" style="font-size: 0.75rem;">Ningún barrio seleccionado</span>
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
<div class="sidebar-form-overlay" id="formOverlay" onclick="closeSidebarForm()"></div>
<div class="sidebar-form" id="sidebarForm">
    <div class="sidebar-form-header">
        <h2 id="formTitle" style="color: #fff; font-family: 'DM Serif Display', serif;">Nuevo Domiciliario</h2>
    </div>
    <div class="sidebar-form-content">
        <form id="mainForm" method="POST">
            @csrf
            <div id="methodField"></div>
            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="nombre" id="inName" class="form-input" required placeholder="Ej: Juan Pérez">
            </div>
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="inPhone" class="form-input" required placeholder="300 000 0000">
            </div>
            <div class="form-group">
                <label class="form-label">Zona de Trabajo</label>
                <select name="zona_id" id="inZoneId" class="select-d" style="width:100%" onchange="loadBarrios(this.value)">
                    <option value="">Seleccione una zona</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" id="barriosSection" style="display: none;">
                <label class="form-label">Barrios Específicos</label>
                <div class="barrios-grid" id="barriosContainer">
                    <!-- Dynamic Barrios -->
                </div>
                <p class="driver-date" style="margin-top: 0.5rem;">Selecciona los barrios que cubrirá este domiciliario.</p>
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
        <button class="btn-d btn-d-ghost" style="flex: 1" onclick="closeSidebarForm()">Cancelar</button>
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
                    
                    // Render Barrios in detail
                    const detBarrios = document.getElementById('detBarrios');
                    detBarrios.innerHTML = '';
                    if (dom.barrios && dom.barrios.length > 0) {
                        dom.barrios.forEach(b => {
                            detBarrios.innerHTML += `<span class="barrio-tag">${b.nombre}</span>`;
                        });
                    } else {
                        detBarrios.innerHTML = '<span class="texto-gris" style="font-size: 0.75rem;">Todos los barrios de la zona</span>';
                    }

                    document.getElementById('btnEditFromDetail').onclick = () => openEditForm(id);
                    document.getElementById('detailModal').classList.add('show');
                }
            });
    }

    function closeDetailModal() { document.getElementById('detailModal').classList.remove('show'); }

    function loadBarrios(zonaId, selectedIds = []) {
        const section = document.getElementById('barriosSection');
        const container = document.getElementById('barriosContainer');
        
        if (!zonaId) {
            section.style.display = 'none';
            return;
        }

        fetch(`/admin/zonas-cobertura/${zonaId}/barrios`)
            .then(res => res.json())
            .then(barrios => {
                if (barrios.length > 0) {
                    section.style.display = 'block';
                    container.innerHTML = barrios.map(b => `
                        <label class="barrio-checkbox-item">
                            <input type="checkbox" name="barrios[]" value="${b.id}" ${selectedIds.includes(b.id) ? 'checked' : ''}>
                            <span>${b.nombre}</span>
                        </label>
                    `).join('');
                } else {
                    section.style.display = 'none';
                    container.innerHTML = '';
                }
            });
    }

    function openCreateForm() {
        document.getElementById('formTitle').innerText = "Nuevo Domiciliario";
        document.getElementById('mainForm').action = "{{ route('admin.domiciliarios.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('mainForm').reset();
        document.getElementById('barriosSection').style.display = 'none';
        document.getElementById('sidebarForm').classList.add('open');
        document.getElementById('formOverlay').classList.add('show');
    }

    function openEditForm(id) {
        closeDetailModal();
        fetch(`/admin/domiciliarios/${id}`)
            .then(res => res.json())
            .then(res => {
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
                    
                    // Load barrios and pre-select
                    loadBarrios(dom.zona_id, res.barrios_ids);

                    document.getElementById('sidebarForm').classList.add('open');
                    document.getElementById('formOverlay').classList.add('show');
                }
            });
    }

    function closeSidebarForm() {
        document.getElementById('sidebarForm').classList.remove('open');
        document.getElementById('formOverlay').classList.remove('show');
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
        }
    });
</script>
@endsection
