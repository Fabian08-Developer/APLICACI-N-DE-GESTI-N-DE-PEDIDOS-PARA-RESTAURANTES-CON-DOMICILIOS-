/**
 * Lógica del Panel de Mesero
 */

let CONFIG = {
    csrf: '',
    rutaEntregar: (id) => `/mesero/pedidos/${id}/entregar`,
    rutaCancelar: (id) => `/mesero/pedidos/${id}/cancelar`,
    rutaConfirmarPago: (id) => `/mesero/pedidos/${id}/confirmar-pago`,
    rutaRegistrarCobro: (id) => `/mesero/pedidos/${id}/registrar-cobro`,
    pedidos: [],
};

const ESTADO_LABEL = {
    'PENDIENTE_PAGO':  'Pendiente de Pago',
    'CREADO':          'Nuevo',
    'EN_PREPARACION':  'En preparación',
    'LISTO':           '¡Listo para entregar!',
    'ENTREGADO':       'Entregado',
    'CANCELADO':       'Cancelado',
};

let pedidoActual = null;
let pedidoACancelar = null;

/**
 * Inicializa el panel de mesero (Datos y WebSockets/Polling local)
 */
export function initMesero(config) {
    CONFIG = { ...CONFIG, ...config };
    
    // Escutamos la tecla Escape para cerrar modal o drawer principal si existe
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && typeof cerrarDrawer === 'function') cerrarDrawer();
    });

    // Polling silencioso
    setInterval(verificarActualizaciones, 10000);
}

// Global logic for UI (Navigation / Hamburguer)
document.addEventListener('DOMContentLoaded', () => {
    // Toggle de la Sidebar Móvil
    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
    const sidebar = document.getElementById('mesero-sidebar');
    const sidebarOverlay = document.getElementById('mesero-sidebar-overlay');

    if (btnToggleSidebar && sidebar && sidebarOverlay) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.add('abierto');
            sidebarOverlay.classList.add('visible');
        });

        // Cerrar al tocar el fondo
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('abierto');
            sidebarOverlay.classList.remove('visible');
        });
    }
});

/**
 * Abre el panel lateral con los detalles del pedido
 */
export function abrirDrawer(id) {
    const p = CONFIG.pedidos.find(x => x.id === id);
    if (!p) return;

    pedidoActual = p;

    // Actualizar UI del drawer
    const elNum = document.getElementById('drawer-num');
    if (elNum) elNum.childNodes[0].textContent = `#${p.short_id || p.id}`;
    
    const elEstado = document.getElementById('drawer-estado-label');
    if (elEstado) elEstado.textContent = ESTADO_LABEL[p.estado] ?? p.estado;

    document.getElementById('drawer-mesa').textContent   = `Mesa ${p.mesa}`;
    document.getElementById('drawer-mesero').textContent = p.mesero;
    document.getElementById('drawer-hora').textContent   = p.hora;

    const pagoBadge = p.estado_pago === 'PENDIENTE'
        ? `<span class="estado-badge" style="background: rgba(245,158,11,0.1); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); margin-left: 6px;">⏳ Por cobrar</span>`
        : `<span class="estado-badge" style="background: rgba(16,185,129,0.1); color: #10B981; border: 1px solid rgba(16,185,129,0.3); margin-left: 6px;">✓ Pagado</span>`;

    document.getElementById('drawer-badge').innerHTML =
        `<span class="estado-badge estado-${p.estado}">${ESTADO_LABEL[p.estado] ?? p.estado}</span>${p.estado_pago ? pagoBadge : ''}`;

    document.getElementById('drawer-productos').innerHTML = p.detalles.map(d => `
        <div class="producto-item">
            <div class="producto-cant">${d.cantidad}</div>
            <div class="producto-info">
                <div class="producto-nombre">${d.nombre}</div>
                ${d.notas ? `<div class="producto-notas">${d.notas}</div>` : ''}
            </div>
            <div class="producto-precio">$${d.subtotal}</div>
        </div>
    `).join('');

    document.getElementById('drawer-total').textContent =
        `$${Number(p.total).toLocaleString('es-CO')}`;

    renderFooter(p);

    const drawer = document.getElementById('drawer');
    const overlay = document.getElementById('overlay');
    if (drawer) {
        drawer.className = `drawer estado-${p.estado}`;
        if (overlay) overlay.classList.add('visible');
        requestAnimationFrame(() => drawer.classList.add('abierto'));
    }
    
    document.querySelectorAll('.pedido-row').forEach(r => r.classList.remove('activo'));
    const row = document.getElementById(`row-${p.id}`);
    if (row) row.classList.add('activo');

    document.body.style.overflow = 'hidden';
}

/**
 * Renders the drawer footer based on order state
 */
function renderFooter(p) {
    const footer = document.getElementById('drawer-footer');
    if (!footer) return;

    if (p.estado === 'PENDIENTE_PAGO') {
        footer.innerHTML = `
            <button class="btn-confirmar-pago listo" onclick="confirmarPagoEfectivo('${p.id}')">
                ✓ Confirmar Pago en Efectivo
            </button>
            <button class="btn-cancelar-pedido" onclick="cancelarPedido('${p.id}')" style="margin-top: 10px;">
                ✕ Cancelar este pedido
            </button>`;
    } else if (p.estado === 'LISTO') {
        footer.innerHTML = `
            <button class="btn-entregar listo" onclick="marcarEntregado('${p.id}')">
                ✓ Marcar como entregado
            </button>`;
    } else if (p.estado === 'CREADO') {
        footer.innerHTML = `
            <div class="drawer-estado-msg"> Esperando que cocina lo reciba</div>
            <button class="btn-cancelar-pedido" onclick="cancelarPedido('${p.id}')">
                ✕ Cancelar este pedido
            </button>`;
    } else if (p.estado === 'ENTREGADO' || p.estado === 'CANCELADO') {
        footer.innerHTML = `
            <div class="drawer-estado-msg">
                ${p.estado === 'ENTREGADO' ? ' Pedido entregado correctamente' : ' Pedido cancelado'}
            </div>`;
    } else {
        const textos = {
            'EN_PREPARACION': ' Cocinero preparando el pedido',
        };
        footer.innerHTML = `<div class="drawer-estado-msg">${textos[p.estado] ?? 'Procesando...'}</div>`;
    }

    if (p.estado_pago === 'PENDIENTE' && p.estado !== 'PENDIENTE_PAGO' && p.estado !== 'CANCELADO') {
        const btnCobrar = `
            <button class="btn-confirmar-pago" onclick="registrarCobroPedido('${p.id}')" style="background: linear-gradient(135deg, #3B82F6, #2563EB); box-shadow: 0 4px 12px rgba(59,130,246,0.3); margin-top: 10px; width: 100%; font-weight: 700; padding: 0.8rem; border: none; border-radius: 8px; color: white; cursor: pointer; transition: all 0.2s;">
                💲 Registrar Cobro en Efectivo ($${Number(p.total).toLocaleString('es-CO')})
            </button>`;
        footer.innerHTML += btnCobrar;
    }
}

export function cerrarDrawer() {
    if (drawer) drawer.classList.remove('abierto');
    if (overlay) overlay.classList.remove('visible');
    document.body.style.overflow = '';
    document.querySelectorAll('.pedido-row').forEach(r => r.classList.remove('activo'));
    setTimeout(() => { pedidoActual = null; }, 350);
}

/**
 * Acción de marcar como entregado
 */
export async function marcarEntregado(id) {
    const btn = document.querySelector('.btn-entregar');
    if (!btn) return;
    btn.disabled = true;
    btn.textContent = 'Entregando...';

    try {
        const res = await fetch(CONFIG.rutaEntregar(id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrf,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.ok) {
            removerFila(id);
            mostrarToast(`Pedido #${id} entregado ✓`);
            cerrarDrawer();
        } else {
            mostrarToast(data.mensaje || 'No se pudo actualizar', true);
            btn.disabled = false;
            btn.textContent = '✓ Marcar como entregado';
        }
    } catch (err) {
        mostrarToast('Error de conexión', true);
        btn.disabled = false;
        btn.textContent = '✓ Marcar como entregado';
    }
}

/**
 * Lógica de cancelación
 */
export function cancelarPedido(id) {
    pedidoACancelar = id;
    const inputMotivo = document.getElementById('motivo_cancelacion');
    if (inputMotivo) inputMotivo.value = '';
    
    const btn = document.getElementById('btn-confirmar-cancelacion');
    if (btn) { btn.disabled = false; btn.textContent = 'Sí, cancelar'; }
    
    document.getElementById('modal-cancelar').classList.add('visible');
}

export function cerrarModalCancelacion() {
    document.getElementById('modal-cancelar').classList.remove('visible');
    pedidoACancelar = null;
}

export async function ejecutarCancelacion() {
    if (!pedidoACancelar) return;

    const btn = document.getElementById('btn-confirmar-cancelacion');
    const motivo = document.getElementById('motivo_cancelacion').value.trim() || 'Cancelado por el mesero desde el panel.';
    
    if (btn) { btn.disabled = true; btn.textContent = 'Cancelando...'; }

    try {
        const res = await fetch(CONFIG.rutaCancelar(pedidoACancelar), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ motivo: motivo }),
        });
        const data = await res.json();

        if (data.ok) {
            removerFila(pedidoACancelar);
            mostrarToast(`Pedido #${pedidoACancelar} cancelado`);
            cerrarModalCancelacion();
            cerrarDrawer();
        } else {
            mostrarToast(data.mensaje || 'No se pudo cancelar.', true);
            if (btn) { btn.disabled = false; btn.textContent = 'Sí, cancelar'; }
        }
    } catch (err) {
        mostrarToast('Error de conexión', true);
        if (btn) { btn.disabled = false; btn.textContent = 'Sí, cancelar'; }
    }
}

function removerFila(id) {
    const row = document.getElementById(`row-${id}`);
    if (row) {
        row.style.transition = 'opacity .3s, transform .3s';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-8px)';
        setTimeout(() => {
            row.remove();
            actualizarContadorHeader();
        }, 300);
    }
}

function actualizarContadorHeader() {
    const lista = document.getElementById('lista-activos');
    const quedan = lista ? lista.querySelectorAll('.pedido-row').length : 0;
    const counts = document.querySelectorAll('.seccion-label .count');
    if (counts.length) counts[0].textContent = quedan;
}

async function verificarActualizaciones() {
    if (drawer && drawer.classList.contains('abierto')) return;

    try {
        const res = await fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nuevaLista = doc.getElementById('lista-activos');
        const listaActual = document.getElementById('lista-activos');
        if (nuevaLista && listaActual && nuevaLista.innerHTML !== listaActual.innerHTML) {
            location.reload();
        }
    } catch (err) { }
}

let toastTimer;
export function mostrarToast(msg, isError = false) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.style.background = isError ? '#7f1d1d' : '';
    t.classList.add('visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('visible'), 2600);
}

/**
 * Acción de confirmar pago en efectivo (Mesero)
 */
export async function confirmarPagoEfectivo(id) {
    const btn = document.querySelector('.btn-confirmar-pago');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Confirmando...';
    }

    try {
        const res = await fetch(CONFIG.rutaConfirmarPago(id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        const data = await res.json();

        if (data.ok) {
            removerFila(id);
            mostrarToast(`Pago del Pedido #${id} confirmado ✓`);
            cerrarDrawer();
        } else {
            mostrarToast(data.mensaje || 'No se pudo confirmar el pago', true);
            if (btn) {
                btn.disabled = false;
                btn.textContent = '✓ Confirmar Pago en Efectivo';
            }
        }
    } catch (err) {
        mostrarToast('Error de conexión', true);
        if (btn) {
            btn.disabled = false;
            btn.textContent = '✓ Confirmar Pago en Efectivo';
        }
    }
}

/**
 * Acción de registrar cobro en efectivo de pedido activo/manual
 */
export async function registrarCobroPedido(id) {
    const btn = document.querySelector('button[onclick*="registrarCobroPedido"]');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Registrando cobro...';
    }

    try {
        const res = await fetch(CONFIG.rutaRegistrarCobro(id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CONFIG.csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ metodo_pago: 'Efectivo' }),
        });
        const data = await res.json();

        if (data.ok) {
            mostrarToast(`Cobro del Pedido #${id} registrado ✓`);
            const p = CONFIG.pedidos.find(x => x.id === id || x.id == id);
            if (p) {
                p.estado_pago = 'COMPLETADO';
                if (p.estado === 'ENTREGADO') {
                    removerFila(id);
                    cerrarDrawer();
                } else {
                    abrirDrawer(id);
                    const row = document.getElementById(`row-${id}`);
                    if (row) {
                        const small = row.querySelector('.pedido-total small');
                        if (small) {
                            small.textContent = '✓ Pagado';
                            small.style.color = '#10B981';
                        }
                    }
                }
            } else {
                cerrarDrawer();
            }
        } else {
            mostrarToast(data.mensaje || 'No se pudo registrar el cobro', true);
            if (btn) {
                btn.disabled = false;
                btn.textContent = `💲 Registrar Cobro en Efectivo`;
            }
        }
    } catch (err) {
        mostrarToast('Error de conexión', true);
        if (btn) {
            btn.disabled = false;
            btn.textContent = `💲 Registrar Cobro en Efectivo`;
        }
    }
}

// Globalizar
window.abrirDrawer = abrirDrawer;
window.cerrarDrawer = cerrarDrawer;
window.marcarEntregado = marcarEntregado;
window.cancelarPedido = cancelarPedido;
window.cerrarModalCancelacion = cerrarModalCancelacion;
window.ejecutarCancelacion = ejecutarCancelacion;
window.confirmarPagoEfectivo = confirmarPagoEfectivo;
window.registrarCobroPedido = registrarCobroPedido;
window.initMesero = initMesero;
