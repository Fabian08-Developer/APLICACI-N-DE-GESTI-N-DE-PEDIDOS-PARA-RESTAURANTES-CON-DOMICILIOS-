/**
 * echo-setup.js — Configuración de Laravel Echo + Reverb + Web Push
 *
 * Responsabilidades:
 *   1. Inicializar Laravel Echo con Reverb (WebSocket en tiempo real)
 *   2. Registrar el Service Worker (base para PWA)
 *   3. Gestionar la suscripción a Web Push Notifications (VAPID)
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Leer contexto del usuario desde meta tags inyectados por el layout
const authUserId     = document.head.querySelector('meta[name="auth-user-id"]')?.content;
const authSucursalId = document.head.querySelector('meta[name="auth-sucursal-id"]')?.content;
const csrfToken      = document.head.querySelector('meta[name="csrf-token"]')?.content;

// ─── 1. Inicializar Echo si hay usuario autenticado con sucursal ──────────────
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

    // Exponer contexto globalmente para uso en layouts y Alpine.js
    window.__SGPD_ECHO = {
        userId:     authUserId,
        sucursalId: authSucursalId,
        ready:      true,
    };
}

// ─── 2. Registro del Service Worker ──────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
            console.debug('[SGPD] Service Worker registrado:', registration.scope);

            // ─── 3. Web Push Notifications ───────────────────────────────────
            // Solo intentar si el usuario está autenticado y las claves VAPID existen
            const vapidPublicKey = import.meta.env.VITE_VAPID_PUBLIC_KEY;

            if (authUserId && vapidPublicKey) {
                await initWebPush(registration, vapidPublicKey, csrfToken);
            }
        } catch (err) {
            // El SW es opcional — el sistema funciona sin él
            console.warn('[SGPD] Service Worker no pudo registrarse:', err);
        }
    });
}

/**
 * Solicita permiso de notificaciones y registra la suscripción Push con el backend.
 *
 * @param {ServiceWorkerRegistration} registration
 * @param {string} vapidPublicKey  Clave pública VAPID en Base64 URL-safe
 * @param {string|null} csrf       Token CSRF para las peticiones al backend
 */
async function initWebPush(registration, vapidPublicKey, csrf) {
    try {
        // Pedir permiso al usuario si aún no lo ha dado ni denegado
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                console.info('[SGPD Push] Permiso de notificaciones denegado por el usuario.');
                return;
            }
        }

        if (Notification.permission !== 'granted') {
            return; // Ya estaba denegado
        }

        // Obtener o crear suscripción push en el browser
        const existingSub = await registration.pushManager.getSubscription();
        const subscription = existingSub ?? await registration.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        });

        // Enviar la suscripción al backend para guardarla en DB
        await fetch('/push/subscribe', {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrf ?? '',
                'Accept':           'application/json',
            },
            body: JSON.stringify({
                endpoint:         subscription.endpoint,
                public_key:       subscription.getKey ? btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('p256dh')))) : null,
                auth_token:       subscription.getKey ? btoa(String.fromCharCode(...new Uint8Array(subscription.getKey('auth'))))   : null,
                content_encoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
            }),
        });

        console.info('[SGPD Push] Suscripción push registrada correctamente.');
    } catch (err) {
        console.warn('[SGPD Push] Error al registrar suscripción push:', err);
    }
}

/**
 * Convierte una clave VAPID de Base64 URL-safe a Uint8Array (requerido por pushManager.subscribe).
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = window.atob(base64);
    return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)));
}
