const overlay = document.getElementById("drawerOverlay");
const drawer = document.getElementById("drawer");
const titulo = document.getElementById("drawerTitulo");
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
    // Clonar el template de creación
    const tpl = document.getElementById("tplCrear");
    contenido.innerHTML = "";
    contenido.appendChild(tpl.content.cloneNode(true));

    titulo.innerHTML = "Nueva categoría";
    submitBtn.textContent = "+ Crear categoría";
    abrirDrawer();

    // Foco en el primer campo
    setTimeout(() => {
        const input = contenido.querySelector('input[type="text"]');
        if (input) input.focus();
    }, 350);
}

function abrirDrawerEditar(id, nombre, descripcion, actionUrl) {
    // Construir formulario de edición dinámicamente
    contenido.innerHTML = `
            <form method="POST" action="${actionUrl}" id="drawerForm">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                <div class="grupo">
                    <label for="nombre_editar">Nombre</label>
                    <input type="text" id="nombre_editar" name="nombre"
                           value="${escHtml(nombre)}"
                           placeholder="Ej: Bebidas, Entradas..."
                           required autofocus>
                </div>
                <div class="grupo">
                    <label for="descripcion_editar">Descripción <small style="opacity:.5">(opcional)</small></label>
                    <textarea id="descripcion_editar" name="descripcion" rows="4"
                              placeholder="Descripción breve de la categoría...">${escHtml(descripcion)}</textarea>
                </div>
            </form>
        `;

    titulo.innerHTML = `Editar categoría <span class="badge-editar">Editando</span>`;
    submitBtn.textContent = "💾 Guardar cambios";
    abrirDrawer();

    setTimeout(() => {
        const input = contenido.querySelector('input[type="text"]');
        if (input) input.focus();
    }, 350);
}

function escHtml(str) {
    const d = document.createElement("div");
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// Cerrar con Escape
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") cerrarDrawer();
});

// Botón dinámico de Submit (ya que el formulario se inyecta)
if (submitBtn) {
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
}

// Exponer funciones globals para que los onclick de HTML puedan llamarlas (Vite encapsula scopes)
window.abrirDrawerCrear = abrirDrawerCrear;
window.abrirDrawerEditar = abrirDrawerEditar;
window.cerrarDrawer = cerrarDrawer;
