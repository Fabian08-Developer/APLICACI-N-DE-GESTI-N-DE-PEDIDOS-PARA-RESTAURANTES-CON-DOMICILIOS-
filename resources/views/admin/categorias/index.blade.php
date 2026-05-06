@extends('admin.layout')
@section('titulo', 'Categorías')
@section('contenido')
@vite(['resources/css/categorias.css', 'resources/js/categorias.js'])

{{-- OVERLAY --}}
<div class="drawer-overlay" id="drawerOverlay" onclick="cerrarDrawer()"></div>

{{-- DRAWER --}}
<div class="drawer" id="drawer">
    <div class="drawer-cabecera">
        <span class="drawer-titulo" id="drawerTitulo">
            Nueva categoría
        </span>
        <button class="btn-cerrar" onclick="cerrarDrawer()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="drawer-cuerpo">
        {{-- El form se inyecta aquí dinámicamente --}}
        <div id="drawerFormContent"></div>
    </div>

    <div class="drawer-footer">
        <button type="submit" form="drawerForm" class="btn-principal" id="drawerSubmitBtn">
            + Crear categoría
        </button>
        <button type="button" class="btn-cancelar" onclick="cerrarDrawer()">
            Cancelar
        </button>
    </div>
</div>

{{-- TEMPLATES DE FORMULARIO (ocultos) --}}

{{-- Template: CREAR --}}
<template id="tplCrear">
    <form method="POST" action="{{ route('admin.categorias.store') }}" id="drawerForm">
        @csrf
        <div class="grupo">
            <label for="nombre_crear">Nombre</label>
            <input type="text" id="nombre_crear" name="nombre"
                   value="{{ old('nombre') }}"
                   placeholder="Ej: Bebidas, Entradas..."
                   required autofocus>
            @error('nombre')
                <div class="error-campo">{{ $message }}</div>
            @enderror
        </div>
        <div class="grupo">
            <label for="descripcion_crear">Descripción <small style="opacity:.5">(opcional)</small></label>
            <textarea id="descripcion_crear" name="descripcion" rows="4"
                      placeholder="Descripción breve de la categoría...">{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <div class="error-campo">{{ $message }}</div>
            @enderror
        </div>
    </form>
</template>

{{-- CONTENIDO DE PÁGINA --}}
<div class="pagina-header">
    <div class="pagina-header-texto">
        <h1>Categorías</h1>
        <p>Administra las categorías del menú</p>
    </div>
    <button class="btn-nuevo" onclick="abrirDrawerCrear()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Nueva categoría
    </button>
</div>

{{-- TABLA --}}
<div class="tarjeta">
    <div class="tarjeta-header">{{ $categorias->count() }} categorías registradas</div>

    @if($categorias->isEmpty())
        <div class="vacio">No hay categorías todavía. ¡Crea la primera!</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorias as $categoria)
                <tr>
                    <td class="texto-gris">{{ $categoria->id }}</td>
                    <td>{{ $categoria->nombre }}</td>
                    <td class="texto-gris">{{ $categoria->descripcion ?? '—' }}</td>
                    <td>
                        <div class="acciones">
                            {{-- Botón editar: abre el drawer con los datos de la categoría --}}
                            <button type="button" class="btn-editar"
                                onclick="abrirDrawerEditar(
                                    {{ $categoria->id }},
                                    '{{ addslashes($categoria->nombre) }}',
                                    '{{ addslashes($categoria->descripcion ?? '') }}',
                                    '{{ route('admin.categorias.actualizar', $categoria->id) }}'
                                )">
                                Editar
                            </button>

                            {{-- Botón eliminar (abre modal) --}}
                            <button type="button" class="btn-eliminar btn-abrir-modal-eliminar"
                                    data-url="{{ route('admin.categorias.eliminar', $categoria->id) }}"
                                    data-nombre="{{ addslashes($categoria->nombre) }}">
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-confirm">
        <div class="modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <h3 class="modal-title">¿Eliminar categoría?</h3>
        <p class="modal-desc">
            Estás a punto de eliminar la categoría <strong id="categoriaNombreEl"></strong>. Esta acción no se puede deshacer.
        </p>
        
        <form id="formEliminar" method="POST" action="">
            @csrf
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-modal-cancel" id="btnCancelarEliminar">Cancelar</button>
                <button type="submit" class="btn-modal btn-modal-confirm">Sí, eliminar</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
        display: none; align-items: center; justify-content: center; z-index: 1000;
    }
    .modal-overlay.active { display: flex; }
    .modal-confirm {
        background: var(--surface); width: 90%; max-width: 400px; padding: 2rem;
        border-radius: 1.2rem; border: 1px solid var(--border); text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }
    .modal-icon {
        width: 50px; height: 50px; background: rgba(248,113,113,0.1); color: #f87171;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.5rem auto;
    }
    .modal-icon svg { width: 24px; height: 24px; }
    .modal-title { font-family: 'DM Serif Display', serif; font-size: 1.4rem; margin-bottom: 0.5rem; color: var(--text-main); }
    .modal-desc { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2rem; line-height: 1.5; }
    .modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .btn-modal { padding: 0.75rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; border: none; font-family: inherit; font-size: 0.9rem; transition: all 0.2s; }
    .btn-modal-cancel { background: transparent; border: 1px solid var(--border); color: var(--text-main); }
    .btn-modal-cancel:hover { background: rgba(255,255,255,0.05); }
    .btn-modal-confirm { background: #f87171; color: white; }
    .btn-modal-confirm:hover { background: #ef4444; }
</style>

{{-- Si hay errores de validación o $editar, reabrimos el drawer automáticamente --}}
@if($errors->any() || $editar)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($editar)
            abrirDrawerEditar(
                {{ $editar->id }},
                '{{ addslashes($editar->nombre) }}',
                '{{ addslashes($editar->descripcion ?? '') }}',
                '{{ route('admin.categorias.actualizar', $editar->id) }}'
            );
        @else
            abrirDrawerCrear();
        @endif
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalEliminar');
        const formEl = document.getElementById('formEliminar');
        const nombreCatEl = document.getElementById('categoriaNombreEl');
        
        document.querySelectorAll('.btn-abrir-modal-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                formEl.action = this.getAttribute('data-url');
                nombreCatEl.textContent = this.getAttribute('data-nombre');
                modalEl.classList.add('active');
            });
        });

        const cerrarModalEl = () => modalEl.classList.remove('active');
        document.getElementById('btnCancelarEliminar').addEventListener('click', cerrarModalEl);
        modalEl.addEventListener('click', (e) => { if(e.target === modalEl) cerrarModalEl(); });
    });
</script>
@endsection