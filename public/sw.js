/**
 * sw.js — Service Worker SGPD
 *
 * Implementa:
 *   - Instalación y activación limpia con skipWaiting
 *   - Estrategia de caché: Cache-First para assets, Network-First para HTML
 *   - Push Notifications desde el servidor (VAPID)
 *   - Click en notificación → abrir o enfocar ventana
 */

const SW_VERSION = 'sgpd-sw-v2';
const CACHE_NAME = `${SW_VERSION}-cache`;

// Assets estáticos que se precachean en la instalación
const PRECACHE_ASSETS = [
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

// ─── Instalación ──────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// ─── Activación ───────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
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

// ─── Fetch — Estrategia de caché híbrida ─────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Solo interceptar peticiones GET del mismo origen o assets estáticos
    if (event.request.method !== 'GET') return;
    if (!['http:', 'https:'].includes(url.protocol)) return;

    // Excluir rutas dinámicas de Livewire, broadcasting y autenticación
    const excludePatterns = ['/livewire/', '/broadcasting/', '/login', '/logout', '/push/'];
    if (excludePatterns.some((p) => url.pathname.startsWith(p))) return;

    // Assets (imágenes, fonts, CSS, JS): Cache-First
    const isAsset = /\.(png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|css|js)(\?.*)?$/.test(url.pathname);

    if (isAsset) {
        event.respondWith(cacheFirst(event.request));
    }
    // Páginas HTML: Network-First (contenido fresco), con fallback offline
    else if (event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirst(event.request));
    }
});

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('', { status: 503, statusText: 'Offline' });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached ?? new Response(
            '<html><body style="font-family:sans-serif;text-align:center;padding:40px"><h2>Sin conexión</h2><p>Por favor verifica tu conexión a internet.</p></body></html>',
            { headers: { 'Content-Type': 'text/html' } }
        );
    }
}

// ─── Push Notifications ───────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch {
        data = { title: 'SGPD', body: event.data.text() };
    }

    const options = {
        body:    data.body    ?? data.mensaje ?? '',
        icon:    data.icon    ?? '/icons/icon-192.png',
        badge:   data.badge   ?? '/icons/icon-72.png',
        tag:     data.tag     ?? 'sgpd-notification',
        data:    data.data    ?? { url: '/dashboard' },
        vibrate: [100, 50, 100],
        actions: data.actions ?? [],
        requireInteraction: false,
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
            // Si ya hay una pestaña abierta con esa URL, enfocarla
            const existing = windowClients.find((c) => c.url.includes(url));
            if (existing) return existing.focus();
            // Si no, abrir nueva ventana
            return clients.openWindow(url);
        })
    );
});
