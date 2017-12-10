// 'install' -- happens only the first time, or when a new version of service-worker.js is detected
self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open('feedreader').then(function(cache) {
            return cache.addAll([
            '/css/app.css',
            '/js/app.js',
            '/manifest.json'
            ]);
        })
    );
});

// // 'activate' -- happens after installation
// self.addEventListener('activate', function(event){
//     console.log('The service worker has been activated');
// });

// 'fetch'
self.addEventListener('fetch', function(event) {
    console.log(event.request.url);
        event.respondWith(
        caches.match(event.request).then(function(response) {
        return response || fetch(event.request);
        })
    );
});