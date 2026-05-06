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
    titulo.innerHTML = "Nueva mesa";
    submitBtn.textContent = "+ Crear mesa";
    abrirDrawer();
    setTimeout(() => {
        const i = contenido.querySelector("input");
        if (i) i.focus();
    }, 350);
}

function abrirDrawerEditar(id, numero, capacidad, estado, actionUrl) {
    const estados = ["DISPONIBLE", "OCUPADA", "RESERVADA"];
    const opcionesEstado = estados
        .map(
            (e) =>
                `<option value="${e}" ${e === estado ? "selected" : ""}>${e === "DISPONIBLE" ? "✅" : e === "OCUPADA" ? "🔴" : "🟡"} ${e.charAt(0) + e.slice(1).toLowerCase()}</option>`,
        )
        .join("");

    contenido.innerHTML = `
            <form method="POST" action="${actionUrl}" id="drawerForm">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
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

    titulo.innerHTML = `Editar mesa <span class="badge-editar">Editando</span>`;
    submitBtn.textContent = "💾 Guardar cambios";
    abrirDrawer();
    setTimeout(() => {
        const i = contenido.querySelector("input");
        if (i) i.focus();
    }, 350);
}

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") cerrarDrawer();
});
