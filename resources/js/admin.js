/**
 * Lógica del panel administrativo
 */

export function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
}

// Escuchamos la tecla Escape para cerrar el sidebar
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.body.classList.remove('sidebar-open');
    }
});

// Exportamos al objeto window para que los "onclick" de Blade sigan funcionando
window.toggleSidebar = toggleSidebar;
