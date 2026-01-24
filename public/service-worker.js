const CACHE_VERSION = 'infraread-pwa-v1';
const PRECACHE_URLS = [
    '/',
    '/manifest.webmanifest',
    '/css/app.css',
    '/js/app.js',
    '/img/infraread192.png',
    '/img/infraread512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_VERSION)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_VERSION)
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

const isSameOrigin = (url) => url.origin === self.location.origin;

const isApiRequest = (url) => url.pathname.startsWith('/api/');

const cacheFirst = async (request) => {
    const cache = await caches.open(CACHE_VERSION);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.ok) {
        cache.put(request, response.clone());
    }

    return response;
};

const networkFirst = async (request) => {
    const cache = await caches.open(CACHE_VERSION);

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        return cache.match('/');
    }
};

const staleWhileRevalidate = async (request) => {
    const cache = await caches.open(CACHE_VERSION);
    const cached = await cache.match(request);
    const fetchPromise = fetch(request).then((response) => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    });

    return cached || fetchPromise;
};

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (!isSameOrigin(url)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
        return;
    }

    if (isApiRequest(url)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    if (['style', 'script', 'image', 'font'].includes(request.destination)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    event.respondWith(networkFirst(request));
});
