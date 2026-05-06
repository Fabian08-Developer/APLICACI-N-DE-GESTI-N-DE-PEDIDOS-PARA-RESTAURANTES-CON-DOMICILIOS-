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
    const tpl = document.getElementById("tplCrear");

    contenido.innerHTML = "";
    contenido.appendChild(tpl.content.cloneNode(true));

    titulo.innerHTML = "Nuevo producto";
    submitBtn.textContent = "+ Crear producto";

    abrirDrawer();
}

function abrirDrawerEditar(
    id,
    nombre,
    descripcion,
    precio,
    categoriaId,
    estado,
    actionUrl,
) {
    const opcionesCat =
        `<option value="">— Sin categoría —</option>` +
        categoriasData
            .map(
                (c) =>
                    `<option value="${c.id}" ${c.id === categoriaId ? "selected" : ""}>${escHtml(c.nombre)}</option>`,
            )
            .join("");

    contenido.innerHTML = `
        <form method="POST" action="${actionUrl}" id="drawerForm">
            <input type="hidden" name="_token" value="${document.querySelector("meta[name=csrf-token]").content}">

            <div class="grupo">
                <label>Nombre</label>
                <input type="text" name="nombre" value="${escHtml(nombre)}" required>
            </div>

            <div class="grupo">
                <label>Descripción</label>
                <textarea name="descripcion">${escHtml(descripcion)}</textarea>
            </div>

            <div class="grupo">
                <label>Precio</label>
                <input type="number" name="precio" value="${precio}" step="0.01" min="0" required>
            </div>

            <div class="grupo">
                <label>Categoría</label>
                <select name="categoria_id">
                    ${opcionesCat}
                </select>
            </div>

            <div class="grupo">
                <label>
                    <input type="checkbox" name="estado" value="1" ${estado ? "checked" : ""}>
                    Producto activo
                </label>
            </div>
        </form>
    `;

    titulo.innerHTML = "Editar producto";
    submitBtn.textContent = "Guardar cambios";

    abrirDrawer();
}

function escHtml(str) {
    if (!str) return "";
    const d = document.createElement("div");
    d.appendChild(document.createTextNode(String(str)));
    return d.innerHTML;
}

document
    .getElementById("drawerSubmitBtn")
    .addEventListener("click", function () {
        const form = document.getElementById("drawerForm");

        if (!form) return;

        if (form.checkValidity()) {
            form.submit();
        } else {
            form.reportValidity();
        }
    });

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") cerrarDrawer();
});
