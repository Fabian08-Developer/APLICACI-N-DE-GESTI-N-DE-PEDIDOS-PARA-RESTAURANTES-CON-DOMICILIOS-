@extends('cocina.layout')

@section('titulo', 'Disponibilidad')

@section('contenido')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1 style="color: var(--text-main); font-family: 'DM Serif Display', serif; font-size: 1.6rem;">Disponibilidad de Productos y Adiciones</h1>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Controla en tiempo real qué elementos se pueden ordenar. Los cambios se reflejan instantáneamente.</p>
    </div>
</div>

<!-- Barra de Filtros y Búsqueda -->
<div class="filters-bar" style="flex-direction: column; align-items: stretch; gap: 1rem;">
    <input type="text" class="search-input" id="searchInput" placeholder="Buscar producto, adición o categoría..." style="width: 100%;">
    
    <div style="display: flex; gap: 0.5rem; align-items: center; overflow-x: auto; padding-bottom: 0.5rem; width: 100%;">
        <button class="filter-pill active" data-filter="all" style="white-space: nowrap;">Todos</button>
        <button class="filter-pill" data-filter="available" style="white-space: nowrap;">Solo Disponibles</button>
        <button class="filter-pill" data-filter="unavailable" style="white-space: nowrap;">Solo Agotados</button>
    </div>
</div>

<div style="display: flex; flex-direction: column; gap: 1.5rem; align-items: stretch;">
    
    <!-- COLUMNA 1: PRODUCTOS, VARIANTES Y ADICIONES -->
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <h2 style="font-size: 1rem; color: var(--accent); border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; font-weight: 600;">Productos del Menú</h2>
        
        @if($productos->isEmpty())
            <div class="col-empty">No hay productos registrados en esta sucursal.</div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;" id="productos-grid">
                @foreach($productos as $producto)
                    <div class="disp-card {{ $producto->disponible ? '' : 'agotado' }} item-searchable" data-type="producto" data-available="{{ $producto->disponible ? '1' : '0' }}" data-id="{{ $producto->id }}" data-search="{{ strtolower($producto->nombre . ' ' . ($producto->categoria->nombre ?? '')) }}" {{ !$producto->disponible && $producto->pausa_expira ? 'data-expires='.$producto->pausa_expira : '' }}>
                        
                        <!-- Header del producto -->
                        <div class="disp-header">
                            <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); overflow: hidden; background: rgba(255,255,255,0.05); flex-shrink: 0;">
                                @if($producto->imagen)
                                    <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; opacity: 0.3;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="disp-info">
                                <h3 class="disp-title">{{ $producto->nombre }}</h3>
                                <div class="disp-cat">{{ $producto->categoria ? $producto->categoria->nombre : 'Sin categoría' }}</div>
                            </div>
                        </div>

                        <!-- Toggle y Actions -->
                        <div class="disp-actions">
                            <label class="switch">
                                <input type="checkbox" class="toggle-switch" data-id="{{ $producto->id }}" data-type="producto" {{ $producto->disponible ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <span class="status-label" style="font-size: 0.75rem; font-weight: 600; color: {{ $producto->disponible ? '#34D399' : '#F87171' }}; flex: 1;" data-base="{{ $producto->disponible ? 'Disponible' : 'Agotado' }}">
                                {{ $producto->disponible ? 'Disponible' : 'Agotado' }}
                            </span>
                            
                            <button class="btn-clock" onclick="openPauseModal('producto', '{{ $producto->id }}', '{{ addslashes($producto->nombre) }}')" title="Pausar temporalmente" style="display: {{ $producto->disponible ? 'flex' : 'none' }};">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>
                        </div>

                        <!-- Variantes -->
                        @if($producto->variantes->isNotEmpty())
                            <div style="border-top: 1px solid var(--border); padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; margin-top: auto;">
                                <h4 style="font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Opciones</h4>
                                @foreach($producto->variantes as $variante)
                                    <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                                        @foreach($variante->opciones as $opcion)
                                            @php $opcDisponible = $opcion['disponible'] ?? true; @endphp
                                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                                                <span style="color: {{ $opcDisponible ? '#fff' : 'var(--text-dim)' }};">{{ $opcion['nombre'] }}</span>
                                                <label class="switch" style="transform: scale(0.7); transform-origin: right;">
                                                    <input type="checkbox" class="toggle-switch-variante" data-vid="{{ $variante->id }}" data-nombre="{{ $opcion['nombre'] }}" {{ $opcDisponible ? 'checked' : '' }}>
                                                    <span class="slider"></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="margin-top: auto;"></div>
                        @endif
                        
                        <!-- Adiciones -->
                        @if($producto->adiciones->isNotEmpty())
                            <div style="border-top: 1px solid var(--border); padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                                <h4 style="font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Adiciones</h4>
                                @foreach($producto->adiciones as $adicion)
                                    @php 
                                        $adicionPausada = \Illuminate\Support\Facades\Cache::has("adicion_{$adicion->id}_pausada");
                                        $adicionDisponible = $adicion->activo && !$adicionPausada;
                                        $expira = \Illuminate\Support\Facades\Cache::get("adicion_{$adicion->id}_pausada");
                                    @endphp
                                    <div class="disp-card {{ $adicionDisponible ? '' : 'agotado' }} item-searchable" data-type="adicion" data-available="{{ $adicionDisponible ? '1' : '0' }}" data-id="{{ $adicion->id }}" data-search="{{ strtolower($adicion->nombre) }}" {{ !$adicionDisponible && $expira ? 'data-expires='.$expira : '' }} style="display: flex; justify-content: space-between; align-items: center; padding: 0; background: transparent; border: none; box-shadow: none; margin-bottom: 0.25rem;">
                                        
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                                            <span style="font-size: 0.75rem; color: {{ $adicionDisponible ? '#fff' : 'var(--text-dim)' }};">{{ $adicion->nombre }} <small style="color: #c9a84c;">(+${{ number_format($adicion->precio, 2) }})</small></span>
                                            <span class="status-label" style="font-size: 0.6rem; font-weight: 600; color: {{ $adicionDisponible ? 'transparent' : '#F87171' }};" data-base="{{ $adicionDisponible ? 'Disponible' : 'Agotado' }}">
                                                {{ $adicionDisponible ? '' : 'Agotado' }}
                                            </span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <button class="btn-clock" onclick="openPauseModal('adicion', '{{ $adicion->id }}', '{{ addslashes($adicion->nombre) }}')" title="Pausar temporalmente" style="display: {{ $adicionDisponible ? 'flex' : 'none' }}; width: 20px; height: 20px; padding: 2px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </button>
                                            <label class="switch" style="transform: scale(0.7); transform-origin: right; margin-left: 0.5rem;">
                                                <input type="checkbox" class="toggle-switch" data-type="adicion" data-id="{{ $adicion->id }}" {{ $adicionDisponible ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                    </div>
                @endforeach
            </div>
        @endif
    </div>


</div>

<!-- Modal Pausa Temporal -->
<div class="pause-modal-backdrop" id="pauseModal">
    <div class="pause-modal">
        <div class="pause-title">Pausar Disponibilidad</div>
        <div class="pause-desc" id="pauseModalDesc">Selecciona por cuánto tiempo estará agotado.</div>
        
        <div class="pause-opts">
            <button class="pause-btn" onclick="submitPause('3s')" style="border-color: #34D399; color: #34D399;">Prueba: Pausar 3 Segundos</button>
            <button class="pause-btn" onclick="submitPause(30)">Pausar 30 Minutos</button>
            <button class="pause-btn" onclick="submitPause(60)">Pausar 1 Hora</button>
            <button class="pause-btn" onclick="submitPause('resto_dia')">Agotado por Hoy</button>
            <button class="pause-btn" onclick="submitPause('indefinido')" style="color: #F87171; border-color: rgba(248, 113, 113, 0.3);">Agotado Indefinido</button>
        </div>
        
        <div class="pause-cancel" onclick="closePauseModal()">Cancelar</div>
    </div>
</div>

<form id="csrf-form" style="display:none;">@csrf</form>

@endsection

@section('scripts')
<script>
    let currentPauseType = null;
    let currentPauseId = null;

    const csrfToken = document.querySelector('#csrf-form input[name="_token"]').value;

    function showToast(msg, isError = false) {
        const t = document.getElementById('toast');
        if(!t) return;
        t.textContent = msg;
        t.className = isError ? 'error visible' : 'visible';
        setTimeout(() => { t.classList.remove('visible'); }, 3000);
    }

    // Buscador y Filtros
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-pill');
    let currentFilter = 'all';

    function filterItems() {
        const query = searchInput.value.toLowerCase();
        document.querySelectorAll('.item-searchable').forEach(card => {
            const searchStr = card.getAttribute('data-search');
            const available = card.getAttribute('data-available');
            
            let matchesSearch = searchStr.includes(query);
            let matchesFilter = true;
            
            if (currentFilter === 'available' && available === '0') matchesFilter = false;
            if (currentFilter === 'unavailable' && available === '1') matchesFilter = false;
            
            card.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterItems);

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.getAttribute('data-filter');
            filterItems();
        });
    });

    // Peticiones AJAX
    async function toggleDisponibilidad(type, id, tiempo = null) {
        const url = `/cocina/disponibilidad/toggle-${type}/${id}`;
        
        const body = { _token: csrfToken };
        if(tiempo) body.tiempo = tiempo;
        
        // Bloquear UI temporalmente mientras se ejecuta la petición
        const card = document.querySelector(`.disp-card[data-type="${type}"][data-id="${id}"]`);
        let checkbox = null, label = null, clockBtn = null;
        let originalText = '';
        if (card) {
            checkbox = card.querySelector('.toggle-switch');
            label = card.querySelector('.status-label');
            clockBtn = card.querySelector('.btn-clock');
            
            if (checkbox) checkbox.disabled = true;
            if (clockBtn) {
                clockBtn.disabled = true;
                clockBtn.style.pointerEvents = 'none';
                clockBtn.style.opacity = '0.5';
            }
            if (label) {
                originalText = label.textContent;
                label.textContent = 'Actualizando...';
                label.style.opacity = '0.6';
            }
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            
            if (data.ok) {
                // Actualizar UI
                if (card) {
                    card.setAttribute('data-available', data.disponible ? '1' : '0');
                    if (checkbox) checkbox.checked = data.disponible;
                    
                    if (data.disponible) {
                        card.removeAttribute('data-expires');
                        card.classList.remove('agotado');
                        if (label) {
                            label.setAttribute('data-base', type === 'producto' ? 'Disponible' : 'Disp.');
                            label.textContent = label.getAttribute('data-base');
                            label.style.color = '#34D399';
                        }
                        if(clockBtn) clockBtn.style.display = 'flex';
                        showToast('Marcado como disponible');
                    } else {
                        if (data.expira) card.setAttribute('data-expires', data.expira);
                        else card.removeAttribute('data-expires');
                        
                        card.classList.add('agotado');
                        if (label) {
                            label.setAttribute('data-base', 'Agotado');
                            label.textContent = 'Agotado';
                            label.style.color = '#F87171';
                        }
                        if(clockBtn) clockBtn.style.display = 'none';
                        let txt = 'Agotado indefinidamente';
                        if (tiempo) {
                            if (tiempo === 'resto_dia') txt = 'Agotado por hoy';
                            else if (tiempo === '3s') txt = 'Pausado por 3 segundos';
                            else txt = `Pausado por ${tiempo} min`;
                        }
                        showToast(txt);
                    }
                }
                
                filterItems();
            } else {
                if (label) label.textContent = originalText;
                if (checkbox) checkbox.checked = !checkbox.checked; // revert local change if any
                showToast(data.mensaje || 'Error al cambiar estado', true);
            }
        } catch (e) {
            console.error(e);
            if (label) label.textContent = originalText;
            if (checkbox) checkbox.checked = !checkbox.checked; // revert
            showToast('Error de conexión', true);
        } finally {
            if (checkbox) checkbox.disabled = false;
            if (clockBtn) {
                clockBtn.disabled = false;
                clockBtn.style.pointerEvents = 'auto';
                clockBtn.style.opacity = '1';
            }
            if (label) label.style.opacity = '1';
        }
    }

    async function toggleVariante(varianteId, nombre, checkbox) {
        const url = `/cocina/disponibilidad/toggle-variante/${varianteId}/${encodeURIComponent(nombre)}`;
        checkbox.disabled = true; // Bloquear UI durante la petición
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (data.ok) {
                showToast(`Opción '${nombre}' actualizada`);
            } else {
                checkbox.checked = !checkbox.checked; // revert
                showToast('Error al actualizar opción', true);
            }
        } catch (e) {
            checkbox.checked = !checkbox.checked;
            showToast('Error de conexión', true);
        } finally {
            checkbox.disabled = false; // Restaurar UI
        }
    }

    // Event Listeners Switches
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
        toggle.addEventListener('change', (e) => {
            const type = e.target.getAttribute('data-type');
            const id = e.target.getAttribute('data-id');
            // If turning off via switch directly, it's indefinite
            // If turning on via switch directly, it cancels any pause
            toggleDisponibilidad(type, id);
        });
    });

    document.querySelectorAll('.toggle-switch-variante').forEach(toggle => {
        toggle.addEventListener('change', (e) => {
            const vid = e.target.getAttribute('data-vid');
            const nombre = e.target.getAttribute('data-nombre');
            toggleVariante(vid, nombre, e.target);
        });
    });

    // Modal Pausa Temporal
    function openPauseModal(type, id, name) {
        currentPauseType = type;
        currentPauseId = id;
        document.getElementById('pauseModalDesc').textContent = `¿Por cuánto tiempo pausar '${name}'?`;
        document.getElementById('pauseModal').classList.add('show');
    }

    function closePauseModal() {
        document.getElementById('pauseModal').classList.remove('show');
        currentPauseType = null;
        currentPauseId = null;
    }

    function submitPause(tiempo) {
        if (!currentPauseType || !currentPauseId) return;
        const timeVal = tiempo === 'indefinido' ? null : tiempo;
        toggleDisponibilidad(currentPauseType, currentPauseId, timeVal);
        closePauseModal();
    }

    // Temporizador JS
    setInterval(() => {
        document.querySelectorAll('.disp-card[data-expires]').forEach(card => {
            const expiraStr = card.getAttribute('data-expires');
            if (!expiraStr) return;
            
            const expiraMs = parseInt(expiraStr) * 1000;
            const now = Date.now();
            const diffSecs = Math.floor((expiraMs - now) / 1000);
            
            const label = card.querySelector('.status-label');
            
            if (diffSecs <= 0) {
                // El tiempo expiró. Restaurar estado de Disponible automáticamente
                card.removeAttribute('data-expires');
                card.setAttribute('data-available', '1');
                card.classList.remove('agotado');
                
                const checkbox = card.querySelector('.toggle-switch');
                if(checkbox) checkbox.checked = true;
                
                const type = card.getAttribute('data-type');
                if(label) {
                    label.setAttribute('data-base', type === 'producto' ? 'Disponible' : 'Disp.');
                    label.textContent = label.getAttribute('data-base');
                    label.style.color = '#34D399';
                }
                
                const clockBtn = card.querySelector('.btn-clock');
                if(clockBtn) clockBtn.style.display = 'flex';
                
                // Actualizar contador/filtros
                filterItems();
            } else {
                // Actualizar texto con el tiempo
                if(label) {
                    // Solo mostrar tiempo si es menor a 24 horas para no mostrar algo gigante
                    if (diffSecs < 86400) {
                        const h = Math.floor(diffSecs / 3600);
                        const m = Math.floor((diffSecs % 3600) / 60);
                        const s = diffSecs % 60;
                        let timeText = '';
                        if (h > 0) timeText += `${h}h `;
                        if (m > 0 || h > 0) timeText += `${m}m `;
                        timeText += `${s}s`;
                        
                        label.textContent = `Agotado (${timeText.trim()})`;
                    }
                }
            }
        });
    }, 1000);
</script>
@endsection
