// Service Worker for CuciKuy Kasir PWA
// Provides offline capability and caching

const CACHE_NAME = 'cucikuy-kasir-v1';
const STATIC_CACHE = 'cucikuy-static-v1';
const DYNAMIC_CACHE = 'cucikuy-dynamic-v1';
const OFFLINE_CACHE = 'cucikuy-offline-v1';

// Files to cache immediately on install
const STATIC_ASSETS = [
  '/',
  '/kasir/dashboard',
  '/kasir/antrian',
  '/kasir/bookings/walkin',
  '/kasir/bookings/scan',
  '/kasir/operator',
  '/manifest.json',
  '/offline.html',
  // Add other critical assets
];

// API endpoints that should be cached for offline use
const API_CACHE_PATTERNS = [
  /\/api\/v1\/bookings/,
  /\/api\/v1\/services/,
  /\/api\/v1\/branches/,
  /\/api\/v1\/customers/,
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { credentials: 'same-origin' })));
      })
      .then(() => {
        console.log('[SW] Static assets cached');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[SW] Failed to cache static assets:', error);
      })
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
            .map((name) => {
              console.log('[SW] Deleting old cache:', name);
              return caches.delete(name);
            })
        );
      })
      .then(() => {
        console.log('[SW] Activated');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    // For POST/PUT/DELETE, try network first, queue for background sync if offline
    if (!navigator.onLine) {
      event.respondWith(
        handleOfflineMutation(request)
      );
    }
    return;
  }

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // Check if it's an API request
  const isApiRequest = API_CACHE_PATTERNS.some(pattern => pattern.test(url.pathname));

  if (isApiRequest) {
    // API requests: Network first, cache fallback
    event.respondWith(networkFirstStrategy(request));
  } else {
    // Static assets: Cache first, network fallback
    event.respondWith(cacheFirstStrategy(request));
  }
});

// Network first strategy for API calls
async function networkFirstStrategy(request) {
  try {
    const networkResponse = await fetch(request);
    
    // Cache successful responses
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    // Network failed, try cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      // Add offline indicator header
      const response = cachedResponse.clone();
      response.headers.set('X-Offline', 'true');
      return response;
    }
    
    // Return offline page for navigation requests
    if (request.mode === 'navigate') {
      return getOfflinePage();
    }
    
    throw error;
  }
}

// Cache first strategy for static assets
async function cacheFirstStrategy(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    // Update cache in background
    fetch(request).then(networkResponse => {
      if (networkResponse.ok) {
        caches.open(STATIC_CACHE).then(cache => {
          cache.put(request, networkResponse);
        });
      }
    }).catch(() => {
      // Ignore background update errors
    });
    
    return cachedResponse;
  }
  
  // Not in cache, fetch from network
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    // Return offline page for navigation requests
    if (request.mode === 'navigate') {
      return getOfflinePage();
    }
    
    throw error;
  }
}

// Handle offline mutations (POST/PUT/DELETE)
async function handleOfflineMutation(request) {
  try {
    // Try network first
    const response = await fetch(request);
    return response;
  } catch (error) {
    // Queue for background sync
    await queueForSync(request);
    
    // Return a mock response indicating queued
    return new Response(
      JSON.stringify({ 
        success: true, 
        queued: true, 
        message: 'Request queued for sync when online' 
      }),
      {
        status: 202,
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Queue failed requests for background sync
async function queueForSync(request) {
  const db = await openSyncDB();
  const tx = db.transaction('sync-queue', 'readwrite');
  const store = tx.objectStore('sync-queue');
  
  const requestData = {
    url: request.url,
    method: request.method,
    headers: [...request.headers.entries()],
    body: await request.clone().text(),
    timestamp: Date.now(),
  };
  
  await store.add(requestData);
  return tx.complete;
}

// Open IndexedDB for sync queue
function openSyncDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('cucikuy-sync', 1);
    
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('sync-queue')) {
        db.createObjectStore('sync-queue', { keyPath: 'id', autoIncrement: true });
      }
    };
    
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

// Get offline page
async function getOfflinePage() {
  const cache = await caches.open(OFFLINE_CACHE);
  const offlinePage = await cache.match('/offline.html');
  
  if (offlinePage) {
    return offlinePage;
  }
  
  // Create basic offline page if not cached
  return new Response(`
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Offline - CuciKuy</title>
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="min-h-screen flex items-center justify-center bg-gray-50">
      <div class="text-center p-8 max-w-md mx-4">
        <div class="w-24 h-24 mx-auto mb-6 bg-primary/10 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m0 5.656l-3.536 3.536m13.732 0l-3.536-3.536M9.172 21.172l3.536-3.536M12 21a9 9 0 11-9-9c0 2.52.832 4.899 2.224 6.855M12 3a9 9 0 109 9c-2.52 0-4.899-.832-6.855-2.224z"/>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Anda Offline</h1>
        <p class="text-gray-600 mb-6">Tidak ada koneksi internet. Beberapa fitur mungkin tidak tersedia.</p>
        <button onclick="window.location.reload()" class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary/90 transition-colors">
          Coba Lagi
        </button>
      </div>
    </body>
    </html>
  `, {
    headers: { 'Content-Type': 'text/html' }
  });
}

// Background sync for queued requests
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-mutations') {
    event.waitUntil(processSyncQueue());
  }
});

// Process queued requests when online
async function processSyncQueue() {
  const db = await openSyncDB();
  const tx = db.transaction('sync-queue', 'readwrite');
  const store = tx.objectStore('sync-queue');
  
  const requests = await store.getAll();
  
  for (const requestData of requests) {
    try {
      const response = await fetch(requestData.url, {
        method: requestData.method,
        headers: requestData.headers,
        body: requestData.body,
      });
      
      if (response.ok) {
        // Remove from queue on success
        await store.delete(requestData.id);
      }
    } catch (error) {
      console.error('[SW] Sync failed for:', requestData.url, error);
      // Keep in queue for next sync
    }
  }
  
  // Notify clients that sync completed
  self.clients.matchAll().then(clients => {
    clients.forEach(client => {
      client.postMessage({ type: 'SYNC_COMPLETED' });
    });
  });
}

// Push notification handling
self.addEventListener('push', (event) => {
  if (!event.data) return;
  
  const data = event.data.json();
  const options = {
    body: data.body || 'Notifikasi baru',
    icon: '/icons/icon-192.png',
    badge: '/icons/badge-72.png',
    vibrate: [200, 100, 200],
    data: data.data || {},
    actions: data.actions || [],
    requireInteraction: data.requireInteraction || false,
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title || 'CuciKuy', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  if (event.action) {
    // Handle action buttons
    const actionData = event.notification.data;
    if (actionData.url) {
      event.waitUntil(
        clients.openWindow(actionData.url)
      );
    }
  } else {
    // Default click - open app
    event.waitUntil(
      clients.matchAll({ type: 'window' }).then(clientList => {
        for (const client of clientList) {
          if (client.url === '/' && 'focus' in client) {
            return client.focus();
          }
        }
        return clients.openWindow('/');
      })
    );
  }
});

// Message handling from main thread
self.addEventListener('message', (event) => {
  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
  
  if (event.data.type === 'CLEAR_CACHE') {
    caches.keys().then(names => {
      names.forEach(name => caches.delete(name));
    }).then(() => {
      event.ports[0].postMessage({ success: true });
    });
  }
});

console.log('[SW] Service Worker loaded');