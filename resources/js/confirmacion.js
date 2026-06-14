/**
 * Lógica de Seguimiento de Pedido (Cliente)
 */

window.initConfirmacion = function(config) {
    const { 
        pedidoId, 
        estadoInit, 
        rutaEstado, 
        rutaCancelar, 
        csrf, 
        intervaloMs 
    } = config;

    const ESTADOS = {
        'CREADO':         { orden: 0, barra: 5,   texto: 'Pedido recibido, esperando cocina...' },
        'EN_PREPARACION': { orden: 1, barra: 40,  texto: 'Preparando tu pedido...' },
        'LISTO':          { orden: 2, barra: 75,  texto: '¡Tu pedido está listo!' },
        'ENTREGADO':      { orden: 3, barra: 100, texto: '¡Pedido entregado! ' },
        'CANCELADO':      { orden: -1, barra: 0,  texto: 'Pedido cancelado.' },
    };

    const ORDEN_PASOS = ['CREADO', 'EN_PREPARACION', 'LISTO', 'ENTREGADO'];
    
    let timerPolling;
    let estadoActual = estadoInit;

    function actualizarUI(estado, cancelable) {
        const info = ESTADOS[estado];
        if (!info) return;

        const barra = document.getElementById('tracker-barra');
        const dot   = document.getElementById('tracker-dot');
        const msg   = document.getElementById('tracker-mensaje');
        const texto = document.getElementById('tracker-texto');
        const wrapCancelar = document.getElementById('wrap-cancelar');

        if (barra) {
            barra.style.width = info.barra + '%';
            barra.classList.toggle('verde', estado === 'ENTREGADO');
            barra.classList.toggle('rojo',  estado === 'CANCELADO');
        }

        if (texto) texto.textContent = info.texto;

        if (msg) {
            msg.className = 'tracker-mensaje'; // reset
            if (estado === 'ENTREGADO') msg.classList.add('entregado');
            if (estado === 'CANCELADO') msg.classList.add('cancelado');
        }

        if (dot) {
            dot.className = 'tracker-dot'; // reset
            if (estado === 'ENTREGADO') dot.classList.add('verde');
            if (estado === 'CANCELADO') dot.classList.add('rojo');
        }

        // Actualizar pasos individuales
        if (estado !== 'CANCELADO') {
            ORDEN_PASOS.forEach(e => {
                const stepEl = document.getElementById('paso-' + e);
                if (!stepEl) return;
                
                stepEl.classList.remove('completado', 'activo', 'pendiente');
                
                const stepOrden = ESTADOS[e].orden;
                const currentOrden = info.orden;

                if (stepOrden < currentOrden) stepEl.classList.add('completado');
                else if (stepOrden === currentOrden) stepEl.classList.add('activo');
                else stepEl.classList.add('pendiente');
            });
        }

        // Mostrar/ocultar cancelar
        if (wrapCancelar) {
            wrapCancelar.style.display = cancelable ? 'block' : 'none';
        }

        // Detener polling si terminó
        if (estado === 'ENTREGADO' || estado === 'CANCELADO') {
            clearInterval(timerPolling);
        }
    }

    async function consultarEstado() {
        try {
            const res = await fetch(rutaEstado, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();

            if (data.estado && data.estado !== estadoActual) {
                console.log(`[Confirmacion] Cambio de estado: ${estadoActual} -> ${data.estado}`);
                estadoActual = data.estado;
                actualizarUI(estadoActual, data.cancelable ?? false);
            }
        } catch (err) {
            console.error('[Confirmacion] Error al consultar estado', err);
        }
    }

    // Modal Cancerlación
    window.abrirModal = function() {
        document.getElementById('modal-cancelar').classList.add('visible');
    };

    window.cerrarModal = function() {
        document.getElementById('modal-cancelar').classList.remove('visible');
        document.getElementById('motivo').value = '';
    };

    window.confirmarCancelacion = async function() {
        const btn = document.getElementById('btn-confirmar');
        const motivo = document.getElementById('motivo').value.trim();
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(rutaCancelar, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': csrf, 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ motivo }),
            });
            const data = await res.json();

            if (data.ok) {
                window.location.href = data.redirect_url;
            } else {
                alert(data.mensaje || 'Error al cancelar.');
                if (btn) btn.disabled = false;
            }
        } catch (err) {
            alert('Error de conexión.');
            if (btn) btn.disabled = false;
        }
    };

    // Inicialización
    const initialCancelable = (estadoInit === 'CREADO');
    actualizarUI(estadoInit, initialCancelable);

    if (estadoInit !== 'ENTREGADO' && estadoInit !== 'CANCELADO') {
        timerPolling = setInterval(consultarEstado, intervaloMs);
    }
    
    // Cerrar modal click fuera
    document.getElementById('modal-cancelar').addEventListener('click', (e) => {
        if (e.target.id === 'modal-cancelar') window.cerrarModal();
    });
};
