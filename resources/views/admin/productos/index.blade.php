@extends('admin.layout')
@section('titulo', 'Productos')
@section('contenido')
@vite(['resources/css/productos.css'])
{{-- OVERLAY --}}
<div class="drawer-overlay" id="drawerOverlay" onclick="cerrarTodosDrawers()"></div>

{{-- DRAWER: Importación masiva --}}
<div class="drawer" id="drawerImportar">
    <div class="drawer-cabecera">
        <span class="drawer-titulo">Importar productos</span>
        <button class="btn-cerrar" onclick="cerrarDrawerImportar()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    <div class="drawer-cuerpo">
        <form method="POST" action="{{ route('admin.productos.importar') }}" enctype="multipart/form-data" id="formImportar">
            @csrf
            {{-- Info --}}
            <div style="background: rgba(79,70,229,.08); border: 1px solid rgba(79,70,229,.2); border-radius: 10px; padding: 1rem 1.1rem; margin-bottom: 1.25rem; font-size: .83rem; line-height: 1.55; color: var(--text-main);">
                <strong>¿Cómo funciona?</strong><br>
                Sube un <code>.csv</code> solo o un <code>.zip</code> que contenga el <code>.csv</code> y las fotos.<br>
                La columna <code>imagen</code> puede ser un <strong>nombre de archivo</strong> (ej: <em>foto.jpg</em>)
                o una <strong>URL</strong> (ej: <em>https://…/foto.jpg</em>).
            </div>

            {{-- Input de archivo --}}
            <div class="grupo">
                <label for="archivo_importar">Archivo CSV o ZIP</label>
                <div class="input-archivo-diseno">
                    <input type="file" id="archivo_importar" name="archivo" accept=".csv,.xlsx,.xls,.zip" required>
                    <div class="label-archivo">
                        <span class="icono-archivo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <span class="texto-archivo" id="textoArchivoImportar">Seleccionar archivo (.csv, .xlsx o .zip)</span>
                    </div>
                </div>
                @error('archivo')<div class="error-campo">{{ $message }}</div>@enderror
            </div>

            {{-- Columnas necesarias --}}
            <div style="font-size: .78rem; color: var(--text-muted); margin-bottom: 1rem;">
                <strong>Columnas del CSV:</strong>
                <code style="display:block; background: rgba(0,0,0,.05); padding: .4rem .7rem; border-radius: 6px; margin-top: .35rem; letter-spacing: .02em;">
                    nombre, descripcion, precio, categoria_id, imagen
                </code>
            </div>

            {{-- Descarga plantilla --}}
            <a href="{{ route('admin.productos.plantilla') }}" style="font-size: .83rem; color: #4f46e5; text-decoration: none; display: inline-flex; align-items: center; gap: .3rem; margin-bottom: 1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar plantilla de ejemplo
            </a>
        </form>
    </div>
    <div class="drawer-footer">
        <button type="button" class="btn-principal" onclick="enviarFormImportar()">
            ⬆ Importar productos
        </button>
        <button type="button" class="btn-cancelar" onclick="cerrarDrawerImportar()">Cancelar</button>
    </div>
</div>

{{-- DRAWER: Crear / Editar (original) --}}
<div class="drawer" id="drawer">
    <div class="drawer-cabecera">
        <span class="drawer-titulo" id="drawerTitulo">Nuevo producto</span>
        <button class="btn-cerrar" onclick="cerrarDrawer()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="drawer-cuerpo">
        <div id="drawerFormContent"></div>
    </div>

    <div class="drawer-footer">
        <button type="button" class="btn-principal" id="drawerSubmitBtn">
            + Crear producto
        </button>
        <button type="button" class="btn-cancelar" onclick="cerrarDrawer()">Cancelar</button>
    </div>
</div>

{{-- Template CREAR --}}
<template id="tplCrear">
    <form method="POST" action="{{ route('admin.productos.store') }}" id="drawerForm" enctype="multipart/form-data">
        @csrf
        <div class="grupo">
            <label for="nombre_crear">Nombre</label>
            <input type="text" id="nombre_crear" name="nombre"
                   value="{{ old('nombre') }}"
                   placeholder="Ej: Hamburguesa clásica" required autofocus>
            @error('nombre')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <label for="descripcion_crear">Descripción <small style="opacity:.5">(opcional)</small></label>
            <textarea id="descripcion_crear" name="descripcion" rows="2"
                      placeholder="Descripción del producto...">{{ old('descripcion') }}</textarea>
            @error('descripcion')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <label for="precio_crear">Precio</label>
            <input type="number" id="precio_crear" name="precio"
                   value="{{ old('precio') }}"
                   placeholder="0.00" step="0.01" min="0" required>
            @error('precio')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <label for="categoria_id_crear">Categoría <small style="opacity:.5">(opcional)</small></label>
            <select id="categoria_id_crear" name="categoria_id">
                <option value="">— Sin categoría —</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}"
                        {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
            @error('categoria_id')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <label for="imagen_crear">Imagen del producto</label>
            <div class="input-archivo-diseno">
                <input type="file" id="imagen_crear" name="imagen" accept="image/*">
                <div class="label-archivo">
                    <span class="icono-archivo"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg></span>
                    <span class="texto-archivo">Haz clic para subir foto</span>
                </div>
            </div>
            @error('imagen')<div class="error-campo">{{ $message }}</div>@enderror
        </div>
        <div class="grupo">
            <div class="toggle-grupo">
                <span class="toggle-label">Producto activo</span>
                <label class="toggle">
                    <input type="checkbox" name="estado" value="1" {{ old('estado', true) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
    </form>
</template>

{{-- PÁGINA --}}
<div class="pagina-header">
    <div class="pagina-header-texto">
        <h1>Productos</h1>
        <p>Administra los productos del menú</p>
    </div>
    <div style="display:flex; gap: 0.75rem;">
        <a href="{{ route('admin.productos.exportar') }}" class="btn-nuevo" style="background: var(--surface); border: 1px solid var(--border); color: var(--text-main); text-decoration: none; display: flex; align-items: center;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; margin-right: 6px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Exportar
        </a>
        <button class="btn-nuevo" style="background: var(--surface); border: 1px solid var(--border); color: var(--text-main);" onclick="abrirDrawerImportar()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Importar
        </button>
        <button class="btn-nuevo" onclick="abrirDrawerCrear()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo producto
        </button>
    </div>
</div>

{{-- FILTROS --}}
<div class="tarjeta" style="margin-bottom: 1.5rem; padding: 1.2rem; background: var(--surface);">
    <form method="GET" action="{{ route('admin.productos.index') }}" class="filtros-form" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-grupo" style="flex: 1; min-width: 200px;">
            <label for="buscar" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Buscar</label>
            <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" placeholder="Nombre o descripción..." style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.5rem; border: 1px solid var(--border); background: rgba(0,0,0,0.02); color: var(--text-main); fill: var(--text-main);">
        </div>
        
        <div class="form-grupo" style="flex: 1; min-width: 150px;">
            <label for="categoria" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Categoría</label>
            <select name="categoria" id="categoria" style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.5rem; border: 1px solid var(--border); background: rgba(0,0,0,0.02); color: var(--text-main);">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $c)
                    <option value="{{ $c->id }}" {{ request('categoria') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-grupo" style="flex: 1; min-width: 150px;">
            <label for="estado" style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Estado</label>
            <select name="estado" id="estado" style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.5rem; border: 1px solid var(--border); background: rgba(0,0,0,0.02); color: var(--text-main);">
                <option value="">Todos los estados</option>
                <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivos</option>
            </select>
        </div>

        <div class="form-grupo" style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn-principal" style="padding: 0.6rem 1.2rem; border-radius: 0.5rem; border: none; font-weight: 600;">Filtrar</button>
            @if(request()->anyFilled(['buscar', 'categoria', 'estado']))
                <a href="{{ route('admin.productos.index') }}" class="btn-cancelar" style="padding: 0.6rem 1.2rem; border-radius: 0.5rem; text-decoration: none; display: flex; align-items: center; border: 1px solid var(--border); color: var(--text-muted);">Limpiar</a>
            @endif
        </div>
    </form>
</div>

{{-- TABLA --}}
<div class="tarjeta">
    <div class="tarjeta-header">{{ $productos->count() }} productos registrados</div>

    @if($productos->isEmpty())
        <div class="vacio">No hay productos todavía. ¡Crea el primero!</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 70px">Foto</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                <tr>
                    <td>
                        <div class="producto-miniatura">
                            @if($producto->imagen)
                                <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                            @else
                                <div class="producto-sin-foto" style="display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg></div>
                            @endif
                        </div>
                    </td>
                    <td>
                        {{ $producto->nombre }}
                        @if($producto->descripcion)
                            <div class="texto-gris">{{ Str::limit($producto->descripcion, 40) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($producto->categoria)
                            <span class="badge-categoria">{{ $producto->categoria->nombre }}</span>
                        @else
                            <span class="texto-gris">—</span>
                        @endif
                    </td>
                    <td class="precio">${{ number_format($producto->precio, 2) }}</td>
                    <td>
                        @if($producto->estado)
                            <span class="badge-activo">Activo</span>
                        @else
                            <span class="badge-inactivo">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <div class="acciones">
                            <button type="button" class="btn-editar"
                                onclick="abrirDrawerEditar(
                                    {{ $producto->id }},
                                    '{{ addslashes($producto->nombre) }}',
                                    '{{ addslashes($producto->descripcion ?? '') }}',
                                    '{{ $producto->precio }}',
                                    {{ $producto->categoria_id ?? 'null' }},
                                    {{ $producto->estado ? 'true' : 'false' }},
                                    '{{ route('admin.productos.actualizar', $producto->id) }}',
                                    '{{ $producto->imagen ? asset('storage/' . $producto->imagen) : '' }}'
                                )">Editar</button>

                            <form method="POST" action="{{ route('admin.productos.toggle', $producto->id) }}">
                                @csrf
                                @if($producto->estado)
                                    <button type="submit" class="btn-eliminar" style="background: rgba(224, 92, 92, 0.1); color: #f0a0a0; border-color: rgba(224, 92, 92, 0.25);">
                                        Desactivar
                                    </button>
                                @else
                                    <button type="submit" class="btn-editar" style="background: rgba(76, 175, 125, 0.12); color: #4caf7d; border-color: rgba(76, 175, 125, 0.3);">
                                        Activar
                                    </button>
                                @endif
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Datos de categorías para JS --}}
<script>
    const categoriasData = @json($categorias->map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre]));
</script>

@if($errors->any() || $editar)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($editar)
            abrirDrawerEditar(
                {{ $editar->id }},
                '{{ addslashes($editar->nombre) }}',
                '{{ addslashes($editar->descripcion ?? '') }}',
                '{{ $editar->precio }}',
                {{ $editar->categoria_id ?? 'null' }},
                {{ $editar->estado ? 'true' : 'false' }},
                '{{ route('admin.productos.actualizar', $editar->id) }}',
                '{{ $editar->imagen ? asset('storage/' . $editar->imagen) : '' }}'
            );
        @else
            abrirDrawerCrear();
        @endif
    });
</script>
@endif

<script>
    const overlay   = document.getElementById('drawerOverlay');
    const drawer    = document.getElementById('drawer');
    const titulo    = document.getElementById('drawerTitulo');
    const contenido = document.getElementById('drawerFormContent');
    const submitBtn = document.getElementById('drawerSubmitBtn');

    // ── Funciones del drawer principal (crear/editar) ─────────────────────
    function abrirDrawer() {
        overlay.classList.add('activo');
        drawer.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }

    function cerrarDrawer() {
        overlay.classList.remove('activo');
        drawer.classList.remove('activo');
        document.body.style.overflow = '';
    }

    // ── Funciones del drawer de importación ───────────────────────────────
    function abrirDrawerImportar() {
        overlay.classList.add('activo');
        document.getElementById('drawerImportar').classList.add('activo');
        document.body.style.overflow = 'hidden';
    }

    function cerrarDrawerImportar() {
        overlay.classList.remove('activo');
        document.getElementById('drawerImportar').classList.remove('activo');
        document.body.style.overflow = '';
    }

    // ── Cierra cualquier drawer abierto (click en el overlay) ─────────────
    function cerrarTodosDrawers() {
        cerrarDrawer();
        cerrarDrawerImportar();
    }

    // ── Mostrar nombre del archivo seleccionado ────────────────────────────
    document.getElementById('archivo_importar')?.addEventListener('change', function () {
        const label = document.getElementById('textoArchivoImportar');
        if (label) {
            label.textContent = this.files[0]?.name || 'Seleccionar archivo (.csv o .zip)';
        }
    });

    function abrirDrawerCrear() {
        const tpl = document.getElementById('tplCrear');
        contenido.innerHTML = '';
        contenido.appendChild(tpl.content.cloneNode(true));
        titulo.innerHTML = 'Nuevo producto';
        submitBtn.textContent = '+ Crear producto';
        abrirDrawer();
        setTimeout(() => { const i = contenido.querySelector('input[type="text"]'); if (i) i.focus(); }, 350);
    }

    function abrirDrawerEditar(id, nombre, descripcion, precio, categoriaId, estado, actionUrl, imagenUrl = '') {
        const opcionesCat = `<option value="">— Sin categoría —</option>` +
            categoriasData.map(c =>
                `<option value="${c.id}" ${c.id === categoriaId ? 'selected' : ''}>${escHtml(c.nombre)}</option>`
            ).join('');

        const previewHtml = imagenUrl 
            ? `<div class="imagen-actual-preview">
                <img src="${imagenUrl}" alt="Vista previa">
                <span>Imagen actual</span>
               </div>`
            : '';

        contenido.innerHTML = `
            <form method="POST" action="${actionUrl}" id="drawerForm" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <div class="grupo">
                    <label>Imagen del producto</label>
                    ${previewHtml}
                    <div class="input-archivo-diseno">
                        <input type="file" name="imagen" accept="image/*">
                        <div class="label-archivo">
                            <span class="icono-archivo"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg></span>
                            <span class="texto-archivo">${imagenUrl ? 'Cambiar foto' : 'Subir foto'}</span>
                        </div>
                    </div>
                </div>

                <div class="grupo">
                    <label for="nombre_editar">Nombre</label>
                    <input type="text" id="nombre_editar" name="nombre"
                           value="${escHtml(nombre)}" placeholder="Ej: Hamburguesa clásica" required autofocus>
                </div>
                <div class="grupo">
                    <label for="descripcion_editar">Descripción <small style="opacity:.5">(opcional)</small></label>
                    <textarea id="descripcion_editar" name="descripcion" rows="2"
                               placeholder="Descripción del producto...">${escHtml(descripcion)}</textarea>
                </div>
                <div class="grupo">
                    <label for="precio_editar">Precio</label>
                    <input type="number" id="precio_editar" name="precio"
                           value="${precio}" placeholder="0.00" step="0.01" min="0" required>
                </div>
                <div class="grupo">
                    <label for="categoria_editar">Categoría <small style="opacity:.5">(opcional)</small></label>
                    <select id="categoria_editar" name="categoria_id">${opcionesCat}</select>
                </div>
            </form>
        `;

        titulo.innerHTML = `Editar producto <span class="badge-editar">Editando</span>`;
        submitBtn.textContent = ' Guardar cambios';
        abrirDrawer();
        setTimeout(() => { const i = contenido.querySelector('input[type="text"]'); if (i) i.focus(); }, 350);
    }

    function escHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(String(str)));
        return d.innerHTML;
    }

    document.getElementById('drawerSubmitBtn').addEventListener('click', function () {
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

    // ── Enviar formulario de importación con token de staff ──────────────
    function enviarFormImportar() {
        const form = document.getElementById('formImportar');
        if (!form) return;

        if (form.checkValidity()) {
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
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarTodosDrawers(); });
</script>

@endsection