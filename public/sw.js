// HYBRID 20 - Service Worker (Fase 7 PWA)
// Aturan Keras (R8, R9): Hanya cache aset statis.
// Halaman privat, JSON endpoint, dan foto progres TIDAK BOLEH disimpan ke cache.

const CACHE_NAME = 'hybrid20-static-v1.0.0';

const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-192-maskable.png',
    '/icons/icon-512-maskable.png',
    '/icons/icon.svg',
    '/fonts/plus-jakarta-sans-latin-variable.woff2',
    '/fonts/space-grotesk-latin-variable.woff2'
];

// 1. Install Event: Precache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event: Clean up stale caches and claim clients
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event: Strict routing
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) {
        return;
    }

    // A. Navigation Requests (HTML document)
    // R9: Navigasi dan halaman HTML bersifat NETWORK-ONLY.
    // Jika koneksi gagal (offline), fallback ke /offline.html.
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(async () => {
                const cache = await caches.open(CACHE_NAME);
                const offlineResponse = await cache.match('/offline.html');
                return offlineResponse || new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
            })
        );
        return;
    }

    // B. Explicitly Deny Caching for Private/Dynamic Routes & Photos (R8, R9)
    // Foto privat, endpoint grafik JSON, API, search, dan route dinamis lainnya: NETWORK-ONLY.
    const isPrivateOrDynamic = 
        url.pathname.startsWith('/progress-photos') ||
        url.pathname.startsWith('/charts') ||
        url.pathname.startsWith('/foods/search') ||
        url.pathname.startsWith('/api') ||
        url.pathname.startsWith('/daily-activities') ||
        url.pathname.startsWith('/body') ||
        url.pathname.startsWith('/workouts') ||
        url.pathname.startsWith('/settings') ||
        url.pathname.startsWith('/food-logs') ||
        url.pathname.startsWith('/plan') ||
        url.pathname.startsWith('/program-weeks') ||
        url.pathname.startsWith('/scheduled-workouts');

    if (isPrivateOrDynamic) {
        event.respondWith(fetch(event.request));
        return;
    }

    // C. Static Build Assets (/build/assets/*) & Precached Static Files
    const isStaticAsset = 
        url.pathname.startsWith('/build/assets/') ||
        url.pathname.startsWith('/fonts/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname === '/manifest.webmanifest' ||
        url.pathname === '/offline.html';

    if (isStaticAsset) {
        // Cache-First with Network Fallback (Vite build assets contain immutable hashes)
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // D. Default for any other GET requests: Network only
    event.respondWith(fetch(event.request));
});

// 4. Message Event: Skip waiting on demand
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
