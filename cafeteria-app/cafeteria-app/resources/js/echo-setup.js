/**
 * echo-setup.js — Configuración de Laravel Echo + Reverb
 *
 * R-07 (PWA-first): Este archivo también registra el Service Worker.
 * Así la tarea de Configuración PWA solo extiende sw.js sin tocar layouts.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Leer contexto del usuario desde meta tags inyectados por el layout (admin.blade.php)
const authUserId    = document.head.querySelector('meta[name="auth-user-id"]')?.content;
const authSucursalId = document.head.querySelector('meta[name="auth-sucursal-id"]')?.content;

// Solo inicializar Echo si hay un usuario autenticado con sucursal
if (authUserId && authSucursalId) {
    window.Echo = new Echo({
        broadcaster:       'reverb',
        key:               import.meta.env.VITE_REVERB_APP_KEY,
        wsHost:            import.meta.env.VITE_REVERB_HOST,
        wsPort:            import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort:           import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS:          (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint:      '/broadcasting/auth',
    });

    // Exponer contexto globalmente para uso en el layout y Alpine.js
    window.__SGPD_ECHO = {
        userId:     authUserId,
        sucursalId: authSucursalId,
        ready:      true,
    };
}

// ─── R-07: Registro del Service Worker (base para PWA) ───────────────────────
// El sw.js está vacío por ahora — se extiende en la tarea de Configuración PWA.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((reg) => {
                // SW registrado — listo para extender con Web Push y cache offline
                console.debug('[SGPD] Service Worker registrado:', reg.scope);
            })
            .catch((err) => {
                // No crítico — el sistema funciona sin SW
                console.warn('[SGPD] Service Worker no pudo registrarse:', err);
            });
    });
}
