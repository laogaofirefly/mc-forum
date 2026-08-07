/* Cache Dynmap static assets and rendered map tiles for repeat visits. */
const CACHE = 'mc-forum-dynmap-v2';
const cacheable = request => request.method === 'GET' && (
  request.url.includes('/tiles/') || /\.(?:png|jpe?g|webp|svg|woff2?|css|js)(?:\?|$)/i.test(request.url)
);
self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', event => {
  if (!cacheable(event.request)) return;
  event.respondWith(caches.open(CACHE).then(async cache => {
    const cached = await cache.match(event.request);
    if (cached) return cached;
    const response = await fetch(event.request);
    if (response && response.ok) cache.put(event.request, response.clone());
    return response;
  }));
});