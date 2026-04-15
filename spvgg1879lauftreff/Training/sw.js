// Service Worker – Lauftreff Training
// Strategie:
//   - Statische Assets (CSS, JS, Icons): Cache-first
//   - PHP-Seiten: Network-first (immer frischer Inhalt)

const CACHE_NAME = 'lauftreff-v1';

const STATIC_ASSETS = [
    '/training/assets/css/training-modern.css',
    '/training/assets/css/training.css',
    '/training/assets/icons/icon-192.png',
    '/training/assets/icons/icon-512.png',
];

// Installation: statische Assets cachen
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Aktivierung: alten Cache aufräumen
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch: Cache-first für statische Assets, Network-first für alles andere
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Nur GET-Anfragen behandeln
    if (event.request.method !== 'GET') return;

    // Statische Assets: Cache-first
    const isStatic =
        url.pathname.startsWith('/training/assets/') ||
        url.pathname.startsWith('/training/assets/icons/');

    if (isStatic) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                return cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // PHP-Seiten: Network-first, kein Offline-Fallback
    // (App braucht Login und Live-Daten — Offline würde nur verwirren)
    event.respondWith(fetch(event.request));
});
