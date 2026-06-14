{{--
    ╔══════════════════════════════════════════════════════════════════════╗
    ║  STAFF TOKEN — Autenticación independiente por pestaña             ║
    ╠══════════════════════════════════════════════════════════════════════╣
    ║  sessionStorage es POR PESTAÑA (no por navegador como las cookies) ║
    ║  Cada pestaña almacena su propio token de sesión.                  ║
    ║                                                                    ║
    ║  Flujo:                                                            ║
    ║  1. Login redirige con ?_token_init=XXX en la URL                  ║
    ║  2. Este script captura el token, lo guarda en sessionStorage      ║
    ║     y cambia _token_init a _st en la URL (para que el middleware   ║
    ║     lo encuentre en recargas futuras)                              ║
    ║  3. En cada request, inyecta el token en forms, links y AJAX      ║
    ║  4. El middleware lee el token y autentica solo para ese request   ║
    ╚══════════════════════════════════════════════════════════════════════╝
--}}
<script>
(function() {
    const TOKEN_KEY = 'staff_token';

    // ── 1. Capturar token después del login ────────────────────────────
    // El login redirige a /admin/dashboard?_token_init=XXX
    // Renombramos _token_init → _st en la URL (para que el middleware lo vea en recargas)
    const url = new URL(window.location);
    const initToken = url.searchParams.get('_token_init');
    if (initToken) {
        sessionStorage.setItem(TOKEN_KEY, initToken);
        url.searchParams.delete('_token_init');
        url.searchParams.set('_st', initToken);
        window.history.replaceState(null, '', url.pathname + url.search + url.hash);
    }

    // Si ya hay _st en la URL, sincronizar con sessionStorage
    const urlToken = url.searchParams.get('_st');
    if (urlToken) {
        sessionStorage.setItem(TOKEN_KEY, urlToken);
    }

    const token = sessionStorage.getItem(TOKEN_KEY);

    // ── 2. Sin token = no autenticado → ir a login ─────────────────────
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // ── 3. Asegurar que _st esté en la URL actual ──────────────────────
    // Si por alguna razón no está (ej: navegación manual), agregarlo
    if (!url.searchParams.has('_st')) {
        url.searchParams.set('_st', token);
        window.history.replaceState(null, '', url.pathname + url.search + url.hash);
    }

    // ── 4. Inyectar token en TODOS los formularios antes de enviar ──────
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.querySelector('input[name="_st"]')) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_st';
        input.value = token;
        form.appendChild(input);
    }, true);

    // ── 4.5. Interceptar llamadas directas a form.submit() en JS ─────────
    const originalSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        if (!this.querySelector('input[name="_st"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_st';
            input.value = token;
            this.appendChild(input);
        }
        return originalSubmit.call(this);
    };

    // ── 5. Inyectar token en TODOS los links (solo mismo origen) ────────
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:')) return;

        try {
            const linkUrl = new URL(href, window.location.origin);
            if (linkUrl.origin !== window.location.origin) return;
            if (linkUrl.searchParams.has('_st')) return;
            linkUrl.searchParams.set('_st', token);
            link.setAttribute('href', linkUrl.pathname + linkUrl.search + linkUrl.hash);
        } catch(err) { /* ignorar URLs malformadas */ }
    }, true);

    // ── 6. Inyectar header en TODAS las llamadas fetch() ────────────────
    const _fetch = window.fetch;
    window.fetch = function(resource, init) {
        init = init || {};
        if (!init.headers) {
            init.headers = {};
        }
        if (init.headers instanceof Headers) {
            init.headers.set('X-Staff-Token', token);
        } else if (Array.isArray(init.headers)) {
            init.headers.push(['X-Staff-Token', token]);
        } else {
            init.headers['X-Staff-Token'] = token;
        }
        return _fetch.call(this, resource, init);
    };

    // ── 7. Inyectar header en TODAS las llamadas XMLHttpRequest ─────────
    const _xhrOpen = XMLHttpRequest.prototype.open;
    const _xhrSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function() {
        this._addStaffToken = true;
        return _xhrOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function() {
        if (this._addStaffToken) {
            try { this.setRequestHeader('X-Staff-Token', token); } catch(e) {}
        }
        return _xhrSend.apply(this, arguments);
    };

    // ── 8. Exponer función para limpiar token (usado en logout) ─────────
    window.__clearStaffToken = function() {
        sessionStorage.removeItem(TOKEN_KEY);
    };

    // ── 9. Exponer el token globalmente para inyecciones manuales ──────
    window.__STAFF_TOKEN = token;
})();
</script>
