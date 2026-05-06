/**
 * Lógica del Panel de Cocina
 */

// Configuración inicial que se leerá desde el DOM
let CONFIG = {
    csrf: '',
    rutaEstado: (id, estado) => `/cocina/pedidos/${id}/estado/${estado}`,
    rutaNuevos: '',
};

// Mapa de columnas por estado
const COLUMNAS = {
    'EN_COCINA':      { colId: 'col-en-cocina', cntId: 'col-cnt-cocina', chipId: 'cnt-cocina'  },
    'EN_PREPARACION': { colId: 'col-prep',       cntId: 'col-cnt-prep',   chipId: 'cnt-prep'    },
    'LISTO':          { colId: 'col-listos',      cntId: 'col-cnt-listos', chipId: 'cnt-listos'  },
    'ENTREGADO':      { colId: null,              cntId: null,             chipId: null          },
};

// Mapa de botones por estado siguiente
const BTN_MAP = {
    'EN_COCINA':      { label: 'Preparando', clase: 'btn-prep',    sig: 'EN_PREPARACION' },
    'EN_PREPARACION': { label: 'Listo',      clase: 'btn-ready',   sig: 'LISTO'          },
};

// Estado visual por clase CSS
const CLASE_MAP = {
    'EN_COCINA': 'state-cooking', 
    'EN_PREPARACION': 'state-prep',
    'LISTO': 'state-ready',       
    'ENTREGADO': '',
};

/**
 * Inicializa la configuración desde elementos data del DOM
 */
export function initCocina(config) {
    CONFIG = { ...CONFIG, ...config };
    actualizarContadores();
    actualizarTimers();
    setInterval(actualizarTimers, 10000);
    setInterval(verificarPedidosNuevos, 5000);
}

/**
 * Cambia el estado de un pedido vía AJAX
 */
export async function cambiarEstado(btn, pedidoId, estadoSiguiente) {
    const card = document.getElementById('card-' + pedidoId);
    if (!card) return;
    
    card.classList.add('loading');

    try {
        const res = await fetch(CONFIG.rutaEstado(pedidoId, estadoSiguiente), {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': CONFIG.csrf, 
                'Accept': 'application/json' 
            },
        });
        const data = await res.json();

        if (!res.ok || !data.ok) {
            mostrarToast(data.mensaje || 'No se pudo actualizar el pedido', true);
            card.classList.remove('loading');
            return;
        }

        const destino = COLUMNAS[estadoSiguiente];

        if (destino?.colId) {
            const colDestino = document.getElementById(destino.colId);
            colDestino.querySelector('.col-empty')?.remove();

            // Actualizar clase de estado visual
            card.className = 'order-card ' + (CLASE_MAP[estadoSiguiente] || '');

            // Actualizar botón
            if (BTN_MAP[estadoSiguiente]) {
                const { label, clase, sig } = BTN_MAP[estadoSiguiente];
                btn.textContent = label;
                btn.className   = `btn-action ${clase}`;
                btn.onclick     = () => cambiarEstado(btn, pedidoId, sig);
            }

            colDestino.appendChild(card);
        } else {
            // ENTREGADO: animar salida y remover
            card.style.transition = 'opacity 0.35s, transform 0.35s';
            card.style.opacity    = '0';
            card.style.transform  = 'scale(0.95)';
            setTimeout(() => card.remove(), 350);
        }

        actualizarContadores();
        mostrarToast(data.mensaje);

    } catch (err) {
        console.error(err);
        mostrarToast('Error de conexión. Intenta de nuevo.', true);
        card.classList.remove('loading');
    }
}

/**
 * Actualiza los contadores de las columnas y chips
 */
export function actualizarContadores() {
    const cols = [
        ['col-nuevos',    'col-cnt-nuevos',  'cnt-nuevos'],
        ['col-en-cocina', 'col-cnt-cocina',  'cnt-cocina'],
        ['col-prep',      'col-cnt-prep',    'cnt-prep'  ],
        ['col-listos',    'col-cnt-listos',  'cnt-listos'],
    ];

    cols.forEach(([colId, cntColId, cntChipId]) => {
        const col = document.getElementById(colId);
        const total = col ? col.querySelectorAll('.order-card').length : 0;
        const elCol = document.getElementById(cntColId);
        const elChip = document.getElementById(cntChipId);
        if (elCol) elCol.textContent = total;
        if (elChip) elChip.textContent = total;
    });

    const elChipNuevos = document.getElementById('chip-nuevos');
    if (elChipNuevos) {
        const cntNuevos = parseInt(document.getElementById('cnt-nuevos')?.textContent) || 0;
        elChipNuevos.classList.toggle('has-items', cntNuevos > 0);
    }
}

let ultimoCheck = new Date().toISOString();

/**
 * Polling para detectar nuevos pedidos
 */
async function verificarPedidosNuevos() {
    if (!CONFIG.rutaNuevos) return;
    
    try {
        const res = await fetch(`${CONFIG.rutaNuevos}?desde=${encodeURIComponent(ultimoCheck)}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.total > 0) {
            data.pedidos.forEach(p => {
                if (document.getElementById('card-' + p.id)) return;
                const col = document.getElementById('col-nuevos');
                if (col) {
                    col.querySelector('.col-empty')?.remove();
                    col.insertAdjacentHTML('beforeend', construirCard(p));
                }
            });

            ultimoCheck = new Date().toISOString();
            actualizarContadores();
            mostrarToast(`${data.total} pedido${data.total > 1 ? 's' : ''} nuevo${data.total > 1 ? 's' : ''}`);
        }
    } catch (err) {
        // Error silencioso en polling
    }
}

/**
 * Construye el string HTML para una nueva card de pedido
 */
function construirCard(p) {
    const timerClass = p.minutos >= 15 ? 'timer-urgent' : p.minutos >= 10 ? 'timer-warn' : 'timer-ok';
    const items = p.detalles.map(d => `
        <div class="order-item">
            <span>${d.nombre}</span>
            <span class="order-item-qty">x${d.cantidad}</span>
        </div>`
    ).join('');

    return `
        <div class="order-card state-new" id="card-${p.id}">
            <div class="order-card-header">
                <div style="display:flex;align-items:center;gap:.5rem">
                    <span class="order-id">#${p.id}</span>
                    <span class="order-table">Mesa ${p.mesa}</span>
                </div>
                <span class="order-timer ${timerClass}" data-creado="${p.created_at}">
                    ${p.minutos}min
                </span>
            </div>
            <div class="order-items">${items}</div>
            <div class="order-footer">
                <button class="btn-action btn-start"
                    onclick="cambiarEstado(this, ${p.id}, 'EN_COCINA')">
                    Iniciar
                </button>
            </div>
        </div>`;
}

/**
 * Actualiza los cronómetros de "hace X min"
 */
export function actualizarTimers() {
    document.querySelectorAll('.order-timer[data-creado]').forEach(el => {
        const minutos = Math.floor((Date.now() - new Date(el.dataset.creado)) / 60000);
        el.textContent = minutos + 'min';
        el.className = 'order-timer ' + (
            minutos >= 15 ? 'timer-urgent' :
            minutos >= 10 ? 'timer-warn' : 'timer-ok'
        );
    });

    const h = document.getElementById('live-time');
    if (h) h.textContent = new Date().toLocaleString('es-CO', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

/**
 * Sistema de notificaciones fugaces (Toast)
 */
let toastTimer;
export function mostrarToast(msg, isError = false) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.className = 'visible' + (isError ? ' error' : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('visible'), 2500);
}

// Globalizar para Blade
window.cambiarEstado = cambiarEstado;
window.initCocina = initCocina;
window.actualizarContadores = actualizarContadores;
window.mostrarToast = mostrarToast;
