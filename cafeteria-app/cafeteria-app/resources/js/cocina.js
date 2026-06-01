/**
 * Lógica del Panel de Cocina
 */

// Configuración inicial que se leerá desde el DOM
let CONFIG = {
    csrf: '',
    rutaEstado: (id, estado) => `/cocina/pedidos/${id}/estado/${estado}`,
    rutaNuevos: '',
    rutaVerificar: '',
};

// Mapa de columnas por estado
const COLUMNAS = {
    'EN_PREPARACION': { colId: 'col-prep',       cntId: 'col-cnt-prep',   chipId: 'cnt-prep'    },
    'LISTO':          { colId: 'col-listos',      cntId: 'col-cnt-listos', chipId: 'cnt-listos'  },
    'ENTREGADO':      { colId: null,              cntId: null,             chipId: null          },
};

// Mapa de botones por estado siguiente
const BTN_MAP = {
    'EN_PREPARACION': { label: 'Listo',      clase: 'btn-ready',   sig: 'LISTO'          },
};

// Estado visual por clase CSS
const CLASE_MAP = {
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
    setInterval(sincronizarEstadosExistentes, 5000);
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
            } else {
                // Si no hay botón (estado LISTO), reemplazar con "Esperando al mesero..."
                const parent = btn.parentElement;
                if (parent) {
                    parent.innerHTML = `<div style="text-align: center; color: var(--text-dim); font-size: 0.8rem; padding: 0.5rem 0; font-style: italic;">Esperando al mesero...</div>`;
                }
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
 * Polling para detectar nuevos pedidos (RF-149)
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

            // Alerta visual: Flashear el header de la columna Nuevos (RF-149)
            const colNuevos = document.getElementById('col-nuevos');
            if (colNuevos) {
                const header = colNuevos.querySelector('.kanban-col-header');
                if (header) {
                    header.classList.remove('flash-header');
                    void header.offsetWidth; // Disparar reflow
                    header.classList.add('flash-header');
                }
            }

            // Opcional: intentar reproducir una alerta acústica discreta
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5 chime
                gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.5);
            } catch (audioErr) {
                // Silencioso si el navegador bloquea audio sin interacción previa
            }

            ultimoCheck = new Date().toISOString();
            actualizarContadores();
            mostrarToast(`${data.total} pedido${data.total > 1 ? 's' : ''} nuevo${data.total > 1 ? 's' : ''}`);
        }
    } catch (err) {
        // Error silencioso en polling
    }
}

/**
 * Polling para sincronizar estados de comandas en pantalla (RF-151)
 */
async function sincronizarEstadosExistentes() {
    if (!CONFIG.rutaVerificar) return;

    const cards = document.querySelectorAll('.order-card');
    const ids = Array.from(cards).map(card => card.id.replace('card-', ''));

    if (ids.length === 0) return;

    try {
        const res = await fetch(CONFIG.rutaVerificar, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids: ids }),
        });
        const data = await res.json();

        if (data.ok && data.estados) {
            let actualizoContadores = false;

            Object.entries(data.estados).forEach(([id, estado]) => {
                const card = document.getElementById('card-' + id);
                if (!card) return;

                if (estado === 'CANCELADO' || estado === 'ENTREGADO') {
                    // Remover tarjeta
                    card.style.transition = 'opacity 0.35s, transform 0.35s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.remove();
                        actualizarContadores();
                    }, 350);
                    actualizoContadores = true;
                    if (estado === 'CANCELADO') {
                        mostrarToast(`Pedido #${id} cancelado por el cliente/mesero`, true);
                    }
                } else {
                    const destino = COLUMNAS[estado];
                    const colActual = card.parentElement;

                    if (destino?.colId && colActual && colActual.id !== destino.colId) {
                        const colDestino = document.getElementById(destino.colId);
                        if (colDestino) {
                            colDestino.querySelector('.col-empty')?.remove();
                            card.className = 'order-card ' + (CLASE_MAP[estado] || '');

                            const btn = card.querySelector('.btn-action');
                            if (btn) {
                                if (BTN_MAP[estado]) {
                                    const { label, clase, sig } = BTN_MAP[estado];
                                    btn.textContent = label;
                                    btn.className = `btn-action ${clase}`;
                                    btn.onclick = () => cambiarEstado(btn, id, sig);
                                } else {
                                    // Reemplazar con el texto de espera del mesero
                                    const parent = btn.parentElement;
                                    if (parent) {
                                        parent.innerHTML = `<div style="text-align: center; color: var(--text-dim); font-size: 0.8rem; padding: 0.5rem 0; font-style: italic;">Esperando al mesero...</div>`;
                                    }
                                }
                            }
                            colDestino.appendChild(card);
                            actualizoContadores = true;
                        }
                    }
                }
            });

            // Remover IDs huérfanos que ya no existen
            ids.forEach(id => {
                if (data.estados[id] === undefined) {
                    const card = document.getElementById('card-' + id);
                    if (card) {
                        card.style.transition = 'opacity 0.35s, transform 0.35s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();
                            actualizarContadores();
                        }, 350);
                        actualizoContadores = true;
                    }
                }
            });

            if (actualizoContadores) {
                actualizarContadores();
            }
        }
    } catch (err) {
        // Silencioso
    }
}

/**
 * Construye el string HTML para una nueva card de pedido (RF-147, RF-148)
 */
function construirCard(p) {
    const timerClass = p.minutos >= 15 ? 'timer-urgent' : p.minutos >= 10 ? 'timer-warn' : 'timer-ok';
    
    const items = p.detalles.map(d => {
        let variantsHtml = '';
        if (d.variantes_elegidas && Object.keys(d.variantes_elegidas).length > 0) {
            variantsHtml = `<div class="order-item-variants">` + 
                Object.entries(d.variantes_elegidas).map(([k, v]) => {
                    const text = (typeof v === 'object' && v !== null) ? `${v.nombre || ''}: ${v.opcion || ''}` : `${k}: ${v}`;
                    return `<span class="order-variant">• ${text}</span>`;
                }).join('') + 
                `</div>`;
        }

        let additionsHtml = '';
        if (d.adiciones_elegidas && d.adiciones_elegidas.length > 0) {
            additionsHtml = `<div class="order-item-additions">` + 
                d.adiciones_elegidas.map(ad => {
                    const text = (typeof ad === 'object' && ad !== null) ? (ad.nombre || '') : ad;
                    return `<span class="order-addition">+ ${text}</span>`;
                }).join('') + 
                `</div>`;
        }

        let notesHtml = '';
        if (d.notas) {
            notesHtml = `<div class="order-item-notes">Nota: ${d.notas}</div>`;
        }

        return `
        <div class="order-item-group">
            <div class="order-item-main">
                <span class="order-item-name">${d.nombre}</span>
                <span class="order-item-qty">x${d.cantidad}</span>
            </div>
            ${variantsHtml}
            ${additionsHtml}
            ${notesHtml}
        </div>`;
    }).join('');

    const mesaText = p.tipo === 'DOMICILIO' ? 'Domicilio' : `Mesa ${p.mesa}`;

    return `
        <div class="order-card state-new flash-new-card" id="card-${p.id}">
            <div class="order-card-header">
                <div style="display:flex;align-items:center;gap:.5rem">
                    <span class="order-id">#${p.id}</span>
                    <span class="order-table">${mesaText}</span>
                </div>
                <span class="order-timer ${timerClass}" data-creado="${p.created_at}">
                    ${p.minutos}min
                </span>
            </div>
            <div class="order-items">${items}</div>
            <div class="order-footer">
                <button class="btn-action btn-prep"
                    onclick="cambiarEstado(this, ${p.id}, 'EN_PREPARACION')">
                    Preparar
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
