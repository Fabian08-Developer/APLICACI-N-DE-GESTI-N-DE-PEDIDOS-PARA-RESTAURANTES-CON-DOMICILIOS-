/**
 * Lógica del panel administrativo
 */

export function toggleSidebar(forceClose = false) {
    if (forceClose === true) {
        document.body.classList.remove('sidebar-open');
    } else {
        document.body.classList.toggle('sidebar-open');
    }
}

// Escuchamos la tecla Escape para cerrar el sidebar
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.body.classList.remove('sidebar-open');
    }
});

// Exportamos al objeto window para que los "onclick" de Blade sigan funcionando
window.toggleSidebar = toggleSidebar;

/* ══════════════════════════════════════════════════════════════
   PRESERVACIÓN DE SCROLL EN BARRA LATERAL (.sidebar-nav)
   Usa sessionStorage para restaurar la posición al navegar
   con wire:navigate (sin depender de @persist)
══════════════════════════════════════════════════════════════ */

function preserveSidebarScroll() {
    const sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebarNav) return;

    sidebarNav.addEventListener('scroll', () => {
        sessionStorage.setItem('sgpd_sidebar_scroll_top', sidebarNav.scrollTop);
    }, { passive: true });
}

function restoreSidebarScroll() {
    const sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebarNav) return;

    const savedScroll = sessionStorage.getItem('sgpd_sidebar_scroll_top');
    if (savedScroll !== null) {
        const top = parseInt(savedScroll, 10);
        sidebarNav.scrollTop = top;
        requestAnimationFrame(() => {
            if (sidebarNav && sidebarNav.scrollTop !== top) {
                sidebarNav.scrollTop = top;
            }
        });
    } else {
        const activeItem = sidebarNav.querySelector('.nav-item.activo');
        if (activeItem) {
            activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }
}

// Inicializar en primera carga
document.addEventListener('DOMContentLoaded', () => {
    preserveSidebarScroll();
    restoreSidebarScroll();
});

// Guardar scroll justo antes de navegar con Livewire
document.addEventListener('livewire:navigating', () => {
    const sidebarNav = document.querySelector('.sidebar-nav');
    if (sidebarNav) {
        sessionStorage.setItem('sgpd_sidebar_scroll_top', sidebarNav.scrollTop);
    }
});

// Al terminar la navegación, restaurar el scroll
// (el servidor ya renderizó la clase activo correctamente sin @persist)
document.addEventListener('livewire:navigated', () => {
    preserveSidebarScroll();
    restoreSidebarScroll();
});
