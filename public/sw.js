/**
 * MiensaFleet — Service Worker v1
 * Stratégies : Cache First (assets), Network First (pages/API) + Background Sync + Push
 */

const CACHE_VERSION   = 'v1';
const CACHE_STATIC    = 'mf-static-'  + CACHE_VERSION;
const CACHE_PAGES     = 'mf-pages-'   + CACHE_VERSION;
const OFFLINE_URL     = '/offline.html';
const DB_NAME         = 'MiensaFleetDB';
const DB_VERSION      = 1;

// ── Assets mis en cache à l'installation ────────────────────────────────────
const PRECACHE_ASSETS = [
  '/offline.html',
  '/manifest.json',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
];

// ── Routes mises en cache (Network First, avec fallback) ─────────────────────
const PRECACHE_PAGES = [
  '/dashboard',
  '/inspections',
  '/affectations',
  '/reparations',
];

// ════════════════════════════════════════════════════════════════════════════
// IndexedDB helpers
// ════════════════════════════════════════════════════════════════════════════

function openDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VERSION);
    req.onupgradeneeded = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains('pending_inspections')) {
        db.createObjectStore('pending_inspections', { keyPath: 'id', autoIncrement: true });
      }
      if (!db.objectStoreNames.contains('pending_trips')) {
        db.createObjectStore('pending_trips', { keyPath: 'id', autoIncrement: true });
      }
    };
    req.onsuccess  = () => resolve(req.result);
    req.onerror    = () => reject(req.error);
  });
}

async function dbGetAll(storeName) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx  = db.transaction(storeName, 'readonly');
    const req = tx.objectStore(storeName).getAll();
    req.onsuccess = () => resolve(req.result);
    req.onerror   = () => reject(req.error);
  });
}

async function dbDelete(storeName, id) {
  const db = await openDB();
  return new Promise((resolve, reject) => {
    const tx  = db.transaction(storeName, 'readwrite');
    const req = tx.objectStore(storeName).delete(id);
    req.onsuccess = () => resolve();
    req.onerror   = () => reject(req.error);
  });
}

// ════════════════════════════════════════════════════════════════════════════
// INSTALL — précache des assets statiques et des pages clés
// ════════════════════════════════════════════════════════════════════════════

self.addEventListener('install', (event) => {
  event.waitUntil(
    Promise.all([
      caches.open(CACHE_STATIC).then(cache => cache.addAll(PRECACHE_ASSETS)),
      caches.open(CACHE_PAGES).then(cache =>
        Promise.allSettled(PRECACHE_PAGES.map(url =>
          fetch(url, { credentials: 'include' })
            .then(res => { if (res.ok) cache.put(url, res); })
            .catch(() => {})
        ))
      ),
    ])
  );
  self.skipWaiting();
});

// ════════════════════════════════════════════════════════════════════════════
// ACTIVATE — nettoyage des vieux caches
// ════════════════════════════════════════════════════════════════════════════

self.addEventListener('activate', (event) => {
  const validCaches = [CACHE_STATIC, CACHE_PAGES];
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key.startsWith('mf-') && !validCaches.includes(key))
          .map(key => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

// ════════════════════════════════════════════════════════════════════════════
// FETCH — stratégies de cache par type de ressource
// ════════════════════════════════════════════════════════════════════════════

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignorer les requêtes non-HTTP, les DevTools, et les hot-reload Vite
  if (!request.url.startsWith('http')) return;
  if (url.pathname.startsWith('/@')) return;
  if (url.hostname !== self.location.hostname) return;

  // POST/PUT/DELETE → passer directement au réseau (possibilité de Background Sync)
  if (request.method !== 'GET') return;

  // ── Assets statiques (JS, CSS, images, fonts) → Cache First ─────────────
  if (isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirst(request, CACHE_STATIC));
    return;
  }

  // ── Requêtes JSON/API → Network First, pas de fallback offline ──────────
  const acceptHeader = request.headers.get('Accept') || '';
  if (acceptHeader.includes('application/json') || url.pathname.startsWith('/api/')) {
    event.respondWith(networkOnly(request));
    return;
  }

  // ── Pages HTML → Network First avec fallback offline ────────────────────
  if (acceptHeader.includes('text/html') || url.pathname === '/') {
    event.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }
});

// ── Stratégie : Cache First ──────────────────────────────────────────────────
async function cacheFirst(request, cacheName) {
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
    return new Response('Asset indisponible', { status: 503 });
  }
}

// ── Stratégie : Network First avec fallback page offline ─────────────────────
async function networkFirstWithOfflineFallback(request) {
  try {
    const response = await fetch(request, { credentials: 'include' });
    if (response.ok) {
      const cache = await caches.open(CACHE_PAGES);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request, { cacheName: CACHE_PAGES });
    if (cached) return cached;
    const offline = await caches.match(OFFLINE_URL);
    return offline || new Response('<h1>Hors ligne</h1>', {
      status: 503,
      headers: { 'Content-Type': 'text/html; charset=utf-8' },
    });
  }
}

// ── Stratégie : Network Only ─────────────────────────────────────────────────
async function networkOnly(request) {
  try {
    return await fetch(request, { credentials: 'include' });
  } catch {
    return new Response(JSON.stringify({ error: 'offline' }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

// ── Détection asset statique ─────────────────────────────────────────────────
function isStaticAsset(pathname) {
  return /\.(js|css|png|jpg|jpeg|svg|gif|webp|ico|woff|woff2|ttf|eot)(\?.*)?$/.test(pathname)
    || pathname.startsWith('/build/')
    || pathname.startsWith('/icons/');
}

// ════════════════════════════════════════════════════════════════════════════
// BACKGROUND SYNC
// ════════════════════════════════════════════════════════════════════════════

self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-inspections') {
    event.waitUntil(syncPendingInspections());
  }
  if (event.tag === 'sync-trips') {
    event.waitUntil(syncPendingTrips());
  }
});

async function syncPendingInspections() {
  const records = await dbGetAll('pending_inspections');
  for (const record of records) {
    try {
      const res = await fetch('/inspections', {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN':  record._csrf || '',
          'Accept':        'application/json',
        },
        body: JSON.stringify(record.data),
      });
      if (res.ok || res.status === 422) {
        await dbDelete('pending_inspections', record.id);
      }
    } catch {
      // Sera réessayé au prochain sync
    }
  }
}

async function syncPendingTrips() {
  const records = await dbGetAll('pending_trips');
  for (const record of records) {
    try {
      const res = await fetch('/trajets', {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN':  record._csrf || '',
          'Accept':        'application/json',
        },
        body: JSON.stringify(record.data),
      });
      if (res.ok || res.status === 422) {
        await dbDelete('pending_trips', record.id);
      }
    } catch {}
  }
}

// ════════════════════════════════════════════════════════════════════════════
// PUSH NOTIFICATIONS
// ════════════════════════════════════════════════════════════════════════════

self.addEventListener('push', (event) => {
  let data = { title: 'MiensaFleet', body: 'Nouvelle notification', url: '/dashboard' };
  try {
    data = { ...data, ...event.data.json() };
  } catch {}

  event.waitUntil(
    self.registration.showNotification(data.title, {
      body:    data.body,
      icon:    '/icons/icon-192x192.png',
      badge:   '/icons/icon-72x72.png',
      data:    { url: data.url },
      vibrate: [200, 100, 200],
      actions: [
        { action: 'open',    title: 'Ouvrir' },
        { action: 'dismiss', title: 'Ignorer' },
      ],
    })
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  if (event.action === 'dismiss') return;

  const targetUrl = (event.notification.data && event.notification.data.url)
    ? event.notification.data.url
    : '/dashboard';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      for (const client of windowClients) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
