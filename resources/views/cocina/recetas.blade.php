@extends('cocina.layout')

@section('titulo', 'Recetario')

@section('contenido')
<header class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1>Recetario <span>de Cocina</span></h1>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">Consulta y edita los ingredientes y pasos de preparación de los productos del menú.</p>
    </div>
</header>

<div class="filters-bar" style="flex-direction: column; align-items: stretch; gap: 1rem;">
    <input type="text" class="search-input" id="searchInput" placeholder="Buscar por producto o categoría..." style="width: 100%;">
    
    <div style="display: flex; gap: 0.5rem; align-items: center; overflow-x: auto; padding-bottom: 0.5rem; width: 100%;">
        <button class="filter-pill active" data-filter="all" style="white-space: nowrap;">Todas las Categorías</button>
        @foreach($categorias as $categoria)
            <button class="filter-pill" data-filter="{{ strtolower($categoria->nombre) }}" style="white-space: nowrap;">{{ $categoria->nombre }}</button>
        @endforeach
    </div>
</div>

@if($productos->isEmpty())
    <div class="col-empty">No hay productos registrados en el sistema.</div>
@else
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;" id="productos-grid">
        @foreach($productos as $producto)
            @php
                $tieneReceta = !empty($producto->receta);
            @endphp
            <div class="disp-card receta-card item-searchable" data-search="{{ strtolower($producto->nombre . ' ' . ($producto->categoria->nombre ?? '')) }}" data-cat="{{ strtolower($producto->categoria->nombre ?? '') }}" onclick="openRecetaModal('{{ $producto->id }}')">
                
                <div class="receta-card-bg">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="receta-card-img">
                    @else
                        <div class="receta-card-noimg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                    @endif
                    <div class="receta-card-overlay"></div>
                </div>

                <div class="receta-card-content">
                    <div class="receta-card-top">
                        @if($tieneReceta)
                            <span class="badge-receta has-receta" id="indicator-{{ $producto->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <span>Ver receta</span>
                            </span>
                        @else
                            <span class="badge-receta no-receta" id="indicator-{{ $producto->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <span>Agregar receta</span>
                            </span>
                        @endif
                    </div>
                    
                    <div class="receta-card-bottom">
                        <h3 class="receta-card-title">{{ $producto->nombre }}</h3>
                        <div class="receta-card-cat">{{ $producto->categoria ? $producto->categoria->nombre : 'Sin categoría' }}</div>
                    </div>
                </div>
                
                <!-- Datos ocultos para el modal -->
                <div class="hidden-receta-data" style="display: none;" 
                     data-id="{{ $producto->id }}"
                     data-nombre="{{ htmlspecialchars($producto->nombre) }}"
                     data-cat="{{ $producto->categoria ? htmlspecialchars($producto->categoria->nombre) : 'Sin categoría' }}"
                     data-img="{{ $producto->imagen ? asset('storage/' . $producto->imagen) : '' }}">
                     {{ $producto->receta }}
                </div>
            </div>
        @endforeach
    </div>
@endif

<!-- Modal Receta Completa -->
<div class="receta-modal-backdrop" id="recetaModal">
    <div class="receta-modal-content">
        <button class="btn-cerrar-receta" onclick="closeRecetaModal()">✕</button>
        
        <div class="receta-modal-header" id="modalHeaderBg">
            <div class="receta-modal-overlay"></div>
            <div class="receta-modal-titles">
                <div class="receta-modal-cat" id="modalCat">Categoría</div>
                <h2 class="receta-modal-title" id="modalTitle">Nombre del Producto</h2>
            </div>
        </div>
        
        <div class="receta-modal-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--border-light); padding-bottom: 0.5rem;">
                <h4 style="color: var(--primary); font-family: 'Playfair Display', serif; font-size: 1.25rem; margin: 0; font-weight: 700;">Preparación</h4>
                <button class="btn-editar-receta" id="btnEditReceta" onclick="toggleEditMode()" style="background: transparent; border: 1px solid var(--border-light); color: var(--text-muted); padding: 0.3rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.75rem; cursor: pointer; font-weight: 500;">Editar</button>
            </div>
            
            <!-- Vista de Lectura -->
            <div id="modalText">
                <!-- Contenido de la receta -->
            </div>

            <!-- Vista de Edición -->
            <div id="modalEditForm" style="display: none; flex-direction: column; gap: 1rem;">
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Añade los ingredientes y los pasos de preparación.</div>
                
                <!-- Ingredientes -->
                <div>
                    <h5 style="color: var(--text-main); font-size: 1rem; margin-bottom: 0.5rem; font-weight: 600;">Ingredientes</h5>
                    <div id="editIngredientesList" class="edit-list"></div>
                    <button type="button" class="btn-add-row" onclick="addEditRow('ingredientes')">+ Agregar ingrediente</button>
                </div>

                <!-- Pasos -->
                <div>
                    <h5 style="color: var(--text-main); font-size: 1rem; margin-bottom: 0.5rem; font-weight: 600;">Pasos de Preparación</h5>
                    <div id="editPasosList" class="edit-list"></div>
                    <button type="button" class="btn-add-row" onclick="addEditRow('pasos')">+ Agregar paso</button>
                </div>

                <!-- Generic/Legacy text -->
                <div id="editGenericDiv" style="display:none;">
                    <h5 style="color: var(--text-main); font-size: 1rem; margin-bottom: 0.5rem; font-weight: 600;">Texto adicional</h5>
                    <textarea id="editGenericInput" class="edit-textarea"></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                    <button onclick="toggleEditMode()" style="background: transparent; border: 1px solid var(--border-light); color: var(--text-main); padding: 0.6rem 1rem; border-radius: var(--radius-sm); cursor: pointer; font-weight: 500;">Cancelar</button>
                    <button id="btnSaveReceta" onclick="saveReceta()" style="background: var(--status-ready); border: none; color: white; padding: 0.6rem 1.2rem; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600;">Guardar Receta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="csrf-form" style="display:none;">@csrf</form>

@endsection

@section('scripts')
<script>
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-pill');
    let currentFilter = 'all';
    let currentProductId = null;
    let isEditing = false;
    
    const csrfToken = document.querySelector('#csrf-form input[name="_token"]').value;

    function filterItems() {
        const query = searchInput ? searchInput.value.toLowerCase() : '';
        document.querySelectorAll('.item-searchable').forEach(card => {
            const searchStr = card.getAttribute('data-search');
            const cat = card.getAttribute('data-cat');
            
            let matchesSearch = searchStr.includes(query);
            let matchesFilter = (currentFilter === 'all' || cat === currentFilter);
            
            card.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
        });
    }

    if(searchInput) {
        searchInput.addEventListener('input', filterItems);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.getAttribute('data-filter');
            filterItems();
        });
    });

    function showToast(msg, isError = false) {
        const t = document.getElementById('toast');
        if(!t) return;
        t.textContent = msg;
        t.className = isError ? 'error visible' : 'visible';
        setTimeout(() => { t.classList.remove('visible'); }, 3000);
    }

    // Parse and Build Logic
    function parseReceta(text) {
        let ingredientes = [];
        let pasos = [];
        let generic = [];
        
        let mode = 'generic';
        const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        
        if(!text.includes('[INGREDIENTES]') && !text.includes('[PASOS]')) {
            return { ingredientes: [], pasos: lines, generic: [] };
        }
        
        for(let line of lines) {
            if(line.toUpperCase() === '[INGREDIENTES]') { mode = 'ingredientes'; continue; }
            if(line.toUpperCase() === '[PASOS]') { mode = 'pasos'; continue; }
            
            if (mode === 'ingredientes') {
                ingredientes.push(line.replace(/^[-*]\s*/, ''));
            } else if (mode === 'pasos') {
                pasos.push(line.replace(/^\d+[\.\)]\s*/, ''));
            } else {
                generic.push(line);
            }
        }
        return { ingredientes, pasos, generic };
    }

    function buildRecetaString(ingredientes, pasos, generic) {
        let str = "";
        if (ingredientes.length > 0) {
            str += "[INGREDIENTES]\n";
            ingredientes.forEach(i => str += `- ${i}\n`);
            str += "\n";
        }
        if (pasos.length > 0) {
            str += "[PASOS]\n";
            pasos.forEach((p, idx) => str += `${idx + 1}. ${p}\n`);
            str += "\n";
        }
        if (generic.length > 0) {
            generic.forEach(g => str += `${g}\n`);
        }
        return str.trim();
    }

    function renderReceta(text) {
        const modalText = document.getElementById('modalText');
        if (!text) {
            modalText.innerHTML = '<span style="opacity:0.5;">Sin receta. Añade una para empezar.</span>';
            return;
        }

        const data = parseReceta(text);
        let html = '';

        if (data.ingredientes.length > 0) {
            html += '<div class="seccion-titulo">Ingredientes</div>';
            html += '<div class="ingredientes-grid">';
            data.ingredientes.forEach(ing => {
                html += `<div class="ingrediente-pill">${ing}</div>`;
            });
            html += '</div>';
        }

        if (data.pasos.length > 0) {
            html += '<div class="seccion-titulo">Preparación</div>';
            html += '<div class="pasos-container">';
            data.pasos.forEach((paso, idx) => {
                html += `
                <div class="paso-card">
                    <div class="paso-num">${idx + 1}</div>
                    <div class="paso-text">${paso}</div>
                </div>`;
            });
            html += '</div>';
        }

        if (data.generic.length > 0) {
            if(data.ingredientes.length > 0 || data.pasos.length > 0) {
                html += '<div class="seccion-titulo">Notas</div>';
            }
            html += '<div class="receta-modal-text">';
            data.generic.forEach(g => html += `${g}<br>`);
            html += '</div>';
        }

        modalText.innerHTML = html;
    }

    function addEditRow(type, val = '') {
        const list = document.getElementById(type === 'ingredientes' ? 'editIngredientesList' : 'editPasosList');
        const row = document.createElement('div');
        row.className = 'edit-row';
        
        if (type === 'ingredientes') {
            row.innerHTML = `<input type="text" class="edit-input" placeholder="Ej. 2 Tomates" value="${val.replace(/"/g, '&quot;')}">
                             <button type="button" class="btn-remove-row" onclick="this.parentElement.remove()">✕</button>`;
        } else {
            row.innerHTML = `<textarea class="edit-textarea" placeholder="Ej. Picar los tomates en cuadros pequeños...">${val}</textarea>
                             <button type="button" class="btn-remove-row" onclick="this.parentElement.remove()">✕</button>`;
        }
        list.appendChild(row);
    }

    function openRecetaModal(id) {
        const card = document.querySelector(`.receta-card .hidden-receta-data[data-id="${id}"]`);
        if(!card) return;
        
        currentProductId = id;
        isEditing = false;

        const nombre = card.getAttribute('data-nombre');
        const cat = card.getAttribute('data-cat');
        const img = card.getAttribute('data-img');
        let texto = card.textContent.trim();
        
        document.getElementById('modalTitle').textContent = nombre;
        document.getElementById('modalCat').textContent = cat;
        
        const headerBg = document.getElementById('modalHeaderBg');
        if(img) {
            headerBg.style.backgroundImage = `url('${img}')`;
        } else {
            headerBg.style.backgroundImage = 'none';
            headerBg.style.background = 'var(--surface)';
        }

        document.getElementById('editIngredientesList').innerHTML = '';
        document.getElementById('editPasosList').innerHTML = '';
        
        const data = parseReceta(texto);
        data.ingredientes.forEach(i => addEditRow('ingredientes', i));
        data.pasos.forEach(p => addEditRow('pasos', p));
        
        if(data.ingredientes.length === 0 && data.pasos.length === 0) {
            addEditRow('ingredientes');
            addEditRow('pasos');
        }
        
        const genericInput = document.getElementById('editGenericInput');
        const genericDiv = document.getElementById('editGenericDiv');
        if(data.generic.length > 0) {
            genericInput.value = data.generic.join('\n');
            genericDiv.style.display = 'block';
        } else {
            genericInput.value = '';
            genericDiv.style.display = 'none';
        }

        if (!texto) {
            toggleEditMode(true);
        } else {
            renderReceta(texto);
            document.getElementById('modalText').style.display = 'block';
            document.getElementById('modalEditForm').style.display = 'none';
            document.getElementById('btnEditReceta').style.display = 'block';
        }
        
        document.getElementById('recetaModal').classList.add('show');
    }

    function toggleEditMode(forceEdit = false) {
        isEditing = forceEdit ? true : !isEditing;
        
        const modalText = document.getElementById('modalText');
        const modalEditForm = document.getElementById('modalEditForm');
        const btnEditReceta = document.getElementById('btnEditReceta');

        if (isEditing) {
            modalText.style.display = 'none';
            modalEditForm.style.display = 'flex';
            btnEditReceta.style.display = 'none';
        } else {
            modalText.style.display = 'block';
            modalEditForm.style.display = 'none';
            btnEditReceta.style.display = 'block';
        }
    }

    async function saveReceta() {
        if (!currentProductId) return;
        
        const ings = Array.from(document.getElementById('editIngredientesList').querySelectorAll('input')).map(el => el.value.trim()).filter(v => v);
        const pss = Array.from(document.getElementById('editPasosList').querySelectorAll('textarea')).map(el => el.value.trim()).filter(v => v);
        const gen = document.getElementById('editGenericInput').value.split('\n').map(l => l.trim()).filter(l => l);
        
        const newReceta = buildRecetaString(ings, pss, gen);
        const btnSave = document.getElementById('btnSaveReceta');
        
        btnSave.textContent = 'Guardando...';
        btnSave.disabled = true;

        try {
            const res = await fetch(`/cocina/recetas/${currentProductId}/guardar`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ receta: newReceta })
            });
            const data = await res.json();
            
            if (data.ok) {
                const cardData = document.querySelector(`.receta-card .hidden-receta-data[data-id="${currentProductId}"]`);
                if(cardData) cardData.textContent = newReceta;
                
                const indicator = document.getElementById(`indicator-${currentProductId}`);
                if (indicator) {
                    if (newReceta) {
                        indicator.className = 'badge-receta has-receta';
                        indicator.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg> <span>Ver receta</span>`;
                    } else {
                        indicator.className = 'badge-receta no-receta';
                        indicator.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg> <span>Agregar receta</span>`;
                    }
                }

                renderReceta(newReceta);
                toggleEditMode();
                showToast(data.mensaje || 'Receta guardada');
            } else {
                showToast(data.mensaje || 'Error al guardar la receta', true);
            }
        } catch (e) {
            console.error(e);
            showToast('Error de conexión', true);
        } finally {
            btnSave.textContent = 'Guardar Receta';
            btnSave.disabled = false;
        }
    }

    function closeRecetaModal() {
        document.getElementById('recetaModal').classList.remove('show');
    }

    document.getElementById('recetaModal').addEventListener('click', (e) => {
        if(e.target.id === 'recetaModal') {
            closeRecetaModal();
        }
    });
</script>
@endsection
