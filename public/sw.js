/* ================================================================
   SERVICE WORKER — Sistema Lanchonete PWA
   Charset: UTF-8 — Português Brasileiro
   ================================================================ */

const CACHE_VERSION  = 'lanchonete-v1.0.0';
const CACHE_ESTATICO = 'static-' + CACHE_VERSION;
const CACHE_DINAMICO = 'dynamic-' + CACHE_VERSION;
const CACHE_IMAGENS  = 'images-' + CACHE_VERSION;

const ARQUIVOS_ESTATICOS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
    '/img/pix.svg',
    '/img/icones/icon-192x192.png',
    '/img/icones/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
];

const ROTAS_NUNCA_CACHE = [
    '/checkout/criar',
    '/login',
    '/logout',
    '/registro',
    '/api/',
    '/webhook/',
    '/admin/',
];

/* ── INSTALL ──────────────────────────────────────────────────── */
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_ESTATICO)
            .then(cache => cache.addAll(ARQUIVOS_ESTATICOS.filter(url => !url.startsWith('http') || url.startsWith('https://fonts') || url.startsWith('https://cdn'))))
            .then(() => self.skipWaiting())
    );
});

/* ── ACTIVATE ─────────────────────────────────────────────────── */
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_ESTATICO && k !== CACHE_DINAMICO && k !== CACHE_IMAGENS)
                    .map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

/* ── FETCH ────────────────────────────────────────────────────── */
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorar requisições não-GET
    if (request.method !== 'GET') return;

    // Ignorar rotas que nunca devem ser cacheadas
    if (ROTAS_NUNCA_CACHE.some(r => url.pathname.startsWith(r))) return;

    // Ignorar extensões de ws/wss
    if (url.protocol === 'ws:' || url.protocol === 'wss:') return;

    // Imagens: cache first
    if (request.destination === 'image') {
        event.respondWith(cacheFirst(request, CACHE_IMAGENS, 60 * 60 * 24 * 7));
        return;
    }

    // Fontes e assets estáticos: cache first
    if (url.pathname.startsWith('/css/') || url.pathname.startsWith('/js/') || url.hostname.includes('fonts.g') || url.hostname.includes('cdn.jsdelivr')) {
        event.respondWith(cacheFirst(request, CACHE_ESTATICO));
        return;
    }

    // Páginas HTML: network first, fallback cache
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirst(request, CACHE_DINAMICO));
        return;
    }

    // Demais requisições: stale while revalidate
    event.respondWith(staleWhileRevalidate(request, CACHE_DINAMICO));
});

/* ── ESTRATÉGIAS ──────────────────────────────────────────────── */
async function cacheFirst(request, cacheName, maxAge = null) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
    }
}

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;
        return caches.match('/') || new Response('<h1>Sem conexão</h1><p>Verifique sua internet.</p>', {
            status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' }
        });
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache  = await caches.open(cacheName);
    const cached = await cache.match(request);
    const fetchPromise = fetch(request).then(response => {
        if (response.ok) cache.put(request, response.clone());
        return response;
    }).catch(() => null);
    return cached || fetchPromise;
}

/* ── PUSH NOTIFICATIONS ───────────────────────────────────────── */
self.addEventListener('push', event => {
    if (!event.data) return;
    let dados;
    try { dados = event.data.json(); } catch { dados = { titulo: 'Nova notificação', corpo: event.data.text() }; }

    event.waitUntil(
        self.registration.showNotification(dados.titulo || 'Lanchonete', {
            body:   dados.corpo || '',
            icon:   dados.icone || '/img/icones/icon-192x192.png',
            badge:  '/img/icones/icon-72x72.png',
            data:   { url: dados.url || '/' },
            vibrate:[200, 100, 200],
            actions: dados.acoes || [],
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            const existente = clientList.find(c => c.url.includes(url) && 'focus' in c);
            if (existente) return existente.focus();
            return clients.openWindow(url);
        })
    );
});

/* ── BACKGROUND SYNC ──────────────────────────────────────────── */
self.addEventListener('sync', event => {
    if (event.tag === 'sync-pedidos') {
        event.waitUntil(syncPedidosPendentes());
    }
});

async function syncPedidosPendentes() {
    // Sincronizar dados pendentes quando a conexão for restaurada
    const cache = await caches.open('pendentes-v1');
    const keys  = await cache.keys();
    for (const key of keys) {
        try {
            const req = await cache.match(key);
            const body = await req.clone().json();
            await fetch('/checkout/criar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': body._token },
                body: JSON.stringify(body),
            });
            cache.delete(key);
        } catch { /* continuar na próxima sync */ }
    }
}
