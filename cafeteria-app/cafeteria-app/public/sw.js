/**
 * sw.js — Service Worker base SGPD
 *
 * Este archivo es el punto de extensión para la tarea de Configuración PWA.
 * Por ahora implementa:
 *   - Instalación y activación limpia
 *   - Handler de push notifications (para cuando se active Web Push)
 *
 * En la tarea PWA se extenderá con:
 *   - Estrategias de cache (Cache First, Network First)
 *   - Soporte offline
 *   - Sincronización en segundo plano
 */

const SW_VERSION   = 'sgpd-sw-v1';
const CACHE_NAME   = `${SW_VERSION}-cache`;

// ─── Instalación ──────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    // skipWaiting: activar inmediatamente sin esperar a que cierren otras pestañas
    event.waitUntil(self.skipWaiting());
});

// ─── Activación ───────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            // Reclamar control de todas las pestañas al instante
            clients.claim(),
            // Limpiar caches de versiones anteriores
            caches.keys().then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_NAME)
                        .map((key) => caches.delete(key))
                )
            ),
        ])
    );
});

// ─── Fetch (placeholder para cache offline — se completa en tarea PWA) ────────
self.addEventListener('fetch', (event) => {
    // Por ahora: pass-through. En la tarea PWA se implementan estrategias de cache.
    // event.respondWith(networkFirst(event.request));
});

// ─── Push Notifications ───────────────────────────────────────────────────────
// Recibe notificaciones push cuando el navegador está en segundo plano
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch {
        data = {
            title: 'SGPD',
            body:  event.data.text(),
        };
    }

    const options = {
        body:    data.body   ?? data.mensaje ?? '',
        icon:    data.icon   ?? '/icons/icon-192.png',
        badge:   data.badge  ?? '/icons/icon-72.png',
        tag:     data.tag    ?? 'sgpd-notification',
        data:    data.data   ?? {},
        vibrate: [100, 50, 100],
        actions: data.actions ?? [],
    };

    event.waitUntil(
        self.registration.showNotification(data.title ?? data.titulo ?? 'SGPD', options)
    );
});

// ─── Clic en notificación push ────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url ?? '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Si ya hay una pestaña abierta, enfocarla
            const existingWindow = windowClients.find((c) => c.url.includes(url));
            if (existingWindow) {
                return existingWindow.focus();
            }
            // Si no hay pestaña, abrir una nueva
            return clients.openWindow(url);
        })
    );
});
