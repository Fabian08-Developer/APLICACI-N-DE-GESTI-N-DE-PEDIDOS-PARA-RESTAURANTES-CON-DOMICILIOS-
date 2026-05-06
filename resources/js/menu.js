/* ─── CONFIGURACIÓN GLOBAL ─────────────────── */
let CONFIG = {
    csrf: '',
    token: '',
    routes: {
        logoutInactividad: '',
        sesionCerrada: ''
    }
};

/**
 * Inicializa la configuración desde Blade
 */
export function initMenu(cfg) {
    CONFIG = { ...CONFIG, ...cfg };
    
    // Inactividad
    timerInactividad = setTimeout(mostrarModalInactividad, INACTIVIDAD_MS);
}

/* ─── FILTRO DE CATEGORÍAS ─────────────────── */
window.filtrarCategoria = function(catId, btn) {
    // Almacena la categoría activa para que se seleccione al recargar
    sessionStorage.setItem('activeCatId', catId);
    
    document.querySelectorAll('.seccion').forEach(s => {
        s.style.display = s.id === 'cat-' + catId ? 'block' : 'none';
    });
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
};

/* ─── CARRITO — ACTUALIZAR CANTIDAD (AJAX) ─ */
window.actualizarCantidad = function(productoId, delta) {
    fetch(`/cliente/carrito/actualizar/${productoId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrf
            },
            body: JSON.stringify({
                delta,
                _t: CONFIG.token
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.eliminado) {
                document.getElementById('item-' + productoId)?.remove();

                if (data.itemsCount === 0) {
                    document.querySelector('.sidebar-items').innerHTML =
                        '<div class="sidebar-vacio"><div class="sidebar-vacio-icon" aria-hidden="true">—</div><span>Tu carrito está vacío.<br>Agrega algo del menú para empezar.</span></div>';
                    document.querySelector('.btn-confirmar').disabled = true;
                }
            } else {
                const qtyEl = document.getElementById('qty-' + productoId);
                const subEl = document.getElementById('sub-' + productoId);
                if (qtyEl) qtyEl.textContent = data.cantidad;
                if (subEl) subEl.textContent = '$' + data.subtotal;
            }

            const totalEl = document.querySelector('.total-valor');
            const countEl = document.querySelector('.sidebar-count');
            if (totalEl) totalEl.textContent = '$' + data.total;
            if (countEl) countEl.textContent = data.itemsCount + (data.itemsCount === 1 ? ' producto' : ' productos');

            const badge = document.getElementById('badge');
            if (badge) {
                badge.textContent = data.itemsCount;
                data.itemsCount > 0 ? badge.classList.add('visible') : badge.classList.remove('visible');
            }
        });
};

/* ─── MODAL LOGOUT ─────────────────────────── */
window.abrirModalLogout = function() {
    document.getElementById('modalLogout').classList.add('visible');
};

window.cerrarModalLogout = function() {
    document.getElementById('modalLogout').classList.remove('visible');
};

/* ─── INACTIVIDAD ──────────────────────────── */
const INACTIVIDAD_MS = 1 * 60 * 1000;
const COUNTDOWN_SEG = 60;

let timerInactividad;
let timerCountdown;
let segundosRestantes = COUNTDOWN_SEG;

// Variable para evitar saturación de eventos (throttling)
let throttlingActividad = false;

function manejarActividad(e) {
    const modalInactividad = document.getElementById('modalInactividad');
    // Si el modal está visible, ignoramos eventos de fondo
    // El usuario DEBE hacer clic en "Seguir pidiendo"
    if (modalInactividad && modalInactividad.classList.contains('visible')) {
        return;
    }

    if (throttlingActividad) return;
    throttlingActividad = true;

    restartInactividad();

    setTimeout(() => {
        throttlingActividad = false;
    }, 1000);
}

const eventosActividad = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
eventosActividad.forEach(ev =>
    document.addEventListener(ev, manejarActividad, {
        passive: true
    })
);

function restartInactividad() {
    const modalInactividad = document.getElementById('modalInactividad');
    if (modalInactividad && modalInactividad.classList.contains('visible')) {
        ocultarModalInactividad();
    }
    clearTimeout(timerInactividad);
    clearInterval(timerCountdown);
    segundosRestantes = COUNTDOWN_SEG;
    const cuentaEl = document.getElementById('cuentaRegresiva');
    if (cuentaEl) cuentaEl.textContent = segundosRestantes;
    timerInactividad = setTimeout(mostrarModalInactividad, INACTIVIDAD_MS);
}

function mostrarModalInactividad() {
    const modalInactividad = document.getElementById('modalInactividad');
    if (!modalInactividad) return;
    
    modalInactividad.classList.add('visible');
    segundosRestantes = COUNTDOWN_SEG;
    const cuentaEl = document.getElementById('cuentaRegresiva');
    if (cuentaEl) cuentaEl.textContent = segundosRestantes;

    timerCountdown = setInterval(() => {
        segundosRestantes--;
        if (cuentaEl) cuentaEl.textContent = segundosRestantes;
        if (segundosRestantes <= 0) {
            clearInterval(timerCountdown);
            window.cerrarSesion('inactividad');
        }
    }, 1000);
}

function ocultarModalInactividad() {
    const modalInactividad = document.getElementById('modalInactividad');
    if (modalInactividad) modalInactividad.classList.remove('visible');
    clearInterval(timerCountdown);
}

window.reiniciarInactividad = restartInactividad;

window.cerrarSesion = function(motivo = 'manual') {
    if (motivo === 'inactividad') {
        fetch(CONFIG.routes.logoutInactividad, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrf
            },
            body: JSON.stringify({
                motivo: 'inactividad',
                _t: CONFIG.token
            })
        }).finally(() => {
            window.location.href = CONFIG.routes.sesionCerrada;
        });
    } else {
        document.getElementById('formLogout').submit();
    }
};

/* ─── PANEL CARRITO ────────────────────────── */
window.cambiarCantidad = function(btn, delta) {
    const control = btn.closest('.cantidad-control');
    const numEl = control.querySelector('.cantidad-num');
    const inputEl = control.querySelector('.input-cantidad');
    let cantidad = parseInt(numEl.textContent) + delta;
    if (cantidad < 1) cantidad = 1;
    numEl.textContent = cantidad;
    inputEl.value = cantidad;
};

window.abrirCarrito = function() {
    document.getElementById('carritoSidebar').classList.add('abierto');
    document.getElementById('overlay').classList.add('activo');
    document.body.style.overflow = 'hidden';
};

window.cerrarCarrito = function() {
    const sidebar = document.getElementById('carritoSidebar');
    const overlay = document.getElementById('overlay');
    if (sidebar) sidebar.classList.remove('abierto');
    if (overlay) overlay.classList.remove('activo');
    document.body.style.overflow = '';
};

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') window.cerrarCarrito();
});

// Hacer global para permitir inicialización desde Blade
window.initMenu = initMenu;

