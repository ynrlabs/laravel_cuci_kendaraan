// Service Worker for CuciKuy Kasir PWA
// Provides offline capability and resilient caching

const CACHE_NAME = 'cucikuy-kasir-v2';
const STATIC_CACHE = 'cucikuy-static-v2';
const DYNAMIC_CACHE = 'cucikuy-dynamic-v2';
const OFFLINE_CACHE = 'cucikuy-offline-v2';

// Only cache public static assets on install
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
];

// Install event - cache static assets safely
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(async (cache) => {
        for (const url of STATIC_ASSETS) {
          try {
            await cache.add(new Request(url, { credentials: 'same-origin' }));
          } catch (err) {
            // Ignore individual prefetch errors
          }
        }
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((name) => name !== STATIC_CACHE && name !== DYNAMIC_CACHE && name !== OFFLINE_CACHE)
            .map((name) => caches.delete(name))
        );
      })
      .then(() => self.clients.claim())
  );
});

// Fetch event - Network-first for navigation & API, Cache-first for static build files
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests and cross-origin
  if (request.method !== 'GET' || url.origin !== location.origin) {
    return;
  }

  // Navigation requests (HTML pages): ALWAYS Network-first
  if (request.mode === 'navigate' || request.destination === 'document') {
    event.respondWith(
      fetch(request).catch(async () => {
        const cached = await caches.match(request);
        if (cached) return cached;
        return getOfflinePage();
      })
    );
    return;
  }

  // Build assets (Vite /build/*): Cache first
  if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(request, responseClone));
          }
          return networkResponse;
        });
      })
    );
    return;
  }

  // Default: Network with graceful fallback
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});

// Fallback offline page
function getOfflinePage() {
  return new Response(`
    <!DOCTYPE html>
    <html lang="id" class="dark">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Offline - CuciKuy</title>
      <style>
        body { font-family: sans-serif; background: #020617; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; text-align: center; }
        .box { background: #0f172a; padding: 2rem; border-radius: 1rem; border: 1px solid #1e293b; max-width: 400px; }
        button { background: #003EC7; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: bold; cursor: pointer; margin-top: 1rem; }
      </style>
    </head>
    <body>
      <div class="box">
        <h2>Koneksi Terputus</h2>
        <p style="color: #94a3b8; font-size: 0.875rem;">Server sedang dimuat ulang atau perangkat Anda sedang offline.</p>
        <button onclick="window.location.reload()">Coba Muat Ulang</button>
      </div>
    </body>
    </html>
  `, {
    headers: { 'Content-Type': 'text/html' }
  });
}