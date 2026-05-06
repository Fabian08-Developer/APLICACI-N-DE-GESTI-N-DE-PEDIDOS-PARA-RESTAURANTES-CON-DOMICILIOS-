@extends('admin.layout')
@section('titulo', 'Mesas')
@section('contenido')
@vite(['resources/css/mesas.css'])

{{-- OVERLAY DRAWER --}}
<div class="drawer-overlay" id="drawerOverlay" onclick="cerrarDrawer()"></div>

{{-- DRAWER --}}
<div class="drawer" id="drawer">
    <div class="drawer-cabecera">
        <span class="drawer-titulo" id="drawerTitulo">Nueva mesa</span>
        <button class="btn-cerrar" onclick="cerrarDrawer()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

    <div class="drawer-cuerpo">
        <div id="drawerFormContent"></div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-principal" id="drawerSubmitBtn">
            + Crear mesa
        </button>
        <button type="button" class="btn-cancelar" onclick="cerrarDrawer()">Cancelar</button>
    </div>
</div>

{{-- MODAL CONFIRMAR ELIMINAR --}}
<div class="modal-overlay" id="modalEliminarOverlay">
    <div class="modal-caja">
        <div class="modal-icono">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21
                       c.342.052.682.107 1.022.166m-1.022-.165L18.16
                       19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25
                       2.25 0 0 1-2.244-2.077L4.772 5.79m14.456
                       0a48.108 48.108 0 0 0-3.478-.397m-12 .562
                       c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11
                       0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164
                       -2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18
                       .037-2.09 1.022-2.09 2.201v.916m7.5
                       0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
        </div>
        <h2 class="modal-titulo">¿Eliminar mesa?</h2>
        <p class="modal-mensaje" id="modalEliminarMensaje">
            Esta acción no se puede deshacer.
        </p>
        <div class="modal-acciones">
            <button type="button" class="btn-modal-eliminar" id="modalBtnConfirmar">
                Sí, eliminar
            </button>
            <button type="button" class="btn-modal-cancelar" onclick="cerrarModalEliminar()">
                Cancelar
            </button>
        </div>
    </div>
</div>

{{-- Template CREAR --}}
<template id="tplCrear">
    <form method="POST" action="{{ route('admin.mesas.store') }}" id="drawerForm">
        @csrf
        <div class="grupo">
            <label for="numero_crear">Número de mesa</label>
            <input type="number" id="numero_crear" name="numero"
                value="{{ old('numero') }}"
                placeholder="Ej: 1, 2, 3..."
                min="1" required autofocus>
            @error('numero')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <label for="capacidad_crear">Capacidad <small style="opacity:.5">(opcional)</small></label>
            <input type="number" id="capacidad_crear" name="capacidad"
                value="{{ old('capacidad') }}"
                placeholder="Ej: 4 personas" min="1">
            @error('capacidad')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <label for="estado_crear">Estado</label>
            <select id="estado_crear" name="estado" required>
                <option value="DISPONIBLE" {{ old('estado') == 'DISPONIBLE' ? 'selected' : '' }}>Disponible</option>
                <option value="OCUPADA"    {{ old('estado') == 'OCUPADA'    ? 'selected' : '' }}>Ocupada</option>
            </select>
            @error('estado')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
    </form>
</template>

{{-- PÁGINA --}}
<div class="pagina-header">
    <div class="pagina-header-texto">
        <h1>Mesas</h1>
        <p>Administra las mesas del restaurante</p>
    </div>
    <button class="btn-nuevo" onclick="abrirDrawerCrear()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Nueva mesa
    </button>
</div>

{{-- GRID DE MESAS --}}
<div class="tarjeta">
    <div class="tarjeta-header">{{ $mesas->count() }} mesas registradas</div>

    @if($mesas->isEmpty())
        <div class="vacio">No hay mesas todavía. ¡Crea la primera!</div>
    @else
        <div class="mesas-grid">
            @foreach($mesas as $mesa)
                @php
                    $claseCard   = strtolower($mesa->estado);
                    $claseEstado = 'badge-' . strtolower($mesa->estado);
                @endphp
                <div class="mesa-card {{ $claseCard }}">
                    <div class="mesa-numero">{{ $mesa->numero }}</div>
                    <div class="mesa-capacidad">
                        {{ $mesa->capacidad ? $mesa->capacidad . ' personas' : 'Sin capacidad' }}
                    </div>
                    <div>
                        <span class="badge-estado {{ $claseEstado }}">{{ $mesa->estado }}</span>
                    </div>
                    <div class="mesa-acciones">
                        <button type="button" class="btn-editar"
                            onclick="abrirDrawerEditar(
                                {{ $mesa->id }},
                                {{ $mesa->numero }},
                                {{ $mesa->capacidad ?? 'null' }},
                                '{{ $mesa->estado }}',
                                '{{ route('admin.mesas.actualizar', $mesa->id) }}'
                            )">Editar</button>

                        {{-- Form oculto; el modal lo dispara --}}
                        <form method="POST"
                              action="{{ route('admin.mesas.eliminar', $mesa->id) }}"
                              id="form-eliminar-{{ $mesa->id }}">
                            @csrf
                        </form>
                        <button type="button" class="btn-eliminar"
                            onclick="abrirModalEliminar({{ $mesa->numero }}, 'form-eliminar-{{ $mesa->id }}')">
                            Eliminar
                        </button>

                        <a href="{{ route('admin.mesas.qr', $mesa->id) }}" class="btn-editar">Ver QR</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- =====================================================================
     JS — siempre disponible, independiente de errores o $editar
     ===================================================================== --}}
<script>
    // ── Drawer ───────────────────────────────────────────────────────────
    const overlay   = document.getElementById("drawerOverlay");
    const drawer    = document.getElementById("drawer");
    const titulo    = document.getElementById("drawerTitulo");
    const contenido = document.getElementById("drawerFormContent");
    const submitBtn = document.getElementById("drawerSubmitBtn");

    function abrirDrawer() {
        overlay.classList.add("activo");
        drawer.classList.add("activo");
        document.body.style.overflow = "hidden";
    }

    function cerrarDrawer() {
        overlay.classList.remove("activo");
        drawer.classList.remove("activo");
        document.body.style.overflow = "";
    }

    function abrirDrawerCrear() {
        const tpl = document.getElementById("tplCrear");
        contenido.innerHTML = "";
        contenido.appendChild(tpl.content.cloneNode(true));
        titulo.innerHTML      = "Nueva mesa";
        submitBtn.textContent = "+ Crear mesa";
        abrirDrawer();
        setTimeout(() => {
            const i = contenido.querySelector("input");
            if (i) i.focus();
        }, 350);
    }

    function abrirDrawerEditar(id, numero, capacidad, estado, actionUrl) {
        const estados = ["DISPONIBLE", "OCUPADA"];

        const opcionesEstado = estados
            .map(e => `<option value="${e}" ${e === estado ? "selected" : ""}>
                            ${e.charAt(0) + e.slice(1).toLowerCase()}
                       </option>`)
            .join("");

        contenido.innerHTML = `
            <form method="POST" action="${actionUrl}" id="drawerForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="grupo">
                    <label for="numero_editar">Número de mesa</label>
                    <input type="number" id="numero_editar" name="numero"
                           value="${numero}" placeholder="Ej: 1, 2, 3..." min="1" required autofocus>
                </div>
                <div class="grupo">
                    <label for="capacidad_editar">Capacidad <small style="opacity:.5">(opcional)</small></label>
                    <input type="number" id="capacidad_editar" name="capacidad"
                           value="${capacidad ?? ""}" placeholder="Ej: 4 personas" min="1">
                </div>
                <div class="grupo">
                    <label for="estado_editar">Estado</label>
                    <select id="estado_editar" name="estado" required>
                        ${opcionesEstado}
                    </select>
                </div>
            </form>
        `;

        titulo.innerHTML      = `Editar mesa <span class="badge-editar">Editando</span>`;
        submitBtn.textContent = "Guardar cambios";
        abrirDrawer();
        setTimeout(() => {
            const i = contenido.querySelector("input");
            if (i) i.focus();
        }, 350);
    }

    // ── Modal eliminar ────────────────────────────────────────────────────
    const modalOverlay = document.getElementById("modalEliminarOverlay");
    const modalMensaje = document.getElementById("modalEliminarMensaje");
    const modalBtnConf = document.getElementById("modalBtnConfirmar");

    function abrirModalEliminar(numero, formId) {
        modalMensaje.textContent = `¿Seguro que deseas eliminar la mesa ${numero}? Esta acción no se puede deshacer.`;
        modalBtnConf.onclick = () => {
            const form = document.getElementById(formId);
            if (!form) return;
            // Inyección manual del token para asegurar autenticación y evitar fallo a GET
            if (window.__STAFF_TOKEN && !form.querySelector('input[name="_st"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_st';
                input.value = window.__STAFF_TOKEN;
                form.appendChild(input);
            }
            form.submit();
        };
        modalOverlay.classList.add("activo");
        document.body.style.overflow = "hidden";
    }

    function cerrarModalEliminar() {
        modalOverlay.classList.remove("activo");
        document.body.style.overflow = "";
    }

    modalOverlay.addEventListener("click", (e) => {
        if (e.target === modalOverlay) cerrarModalEliminar();
    });

    // ── Teclado ───────────────────────────────────────────────────────────
    // ── Envío de formulario ──────────────────────────────────────────────
    submitBtn.addEventListener('click', function () {
        const form = document.getElementById('drawerForm');
        if (!form) return;
        
        if (form.checkValidity()) {
            // Inyección manual del token para asegurar autenticación
            if (window.__STAFF_TOKEN && !form.querySelector('input[name="_st"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_st';
                input.value = window.__STAFF_TOKEN;
                form.appendChild(input);
            }
            form.submit();
        } else {
            form.reportValidity();
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            cerrarDrawer();
            cerrarModalEliminar();
        }
    });
</script>

{{-- Auto-abrir drawer solo si hay errores de validación o viene de editar --}}
@if($errors->any() || $editar)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($editar)
            abrirDrawerEditar(
                {{ $editar->id }},
                {{ $editar->numero }},
                {{ $editar->capacidad ?? 'null' }},
                '{{ $editar->estado }}',
                '{{ route('admin.mesas.actualizar', $editar->id) }}'
            );
        @else
            abrirDrawerCrear();
        @endif
    });
</script>
@endif

@endsection