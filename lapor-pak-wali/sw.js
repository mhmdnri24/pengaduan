// Service Worker for Lapor Pak Wali PWA
const CACHE_NAME = 'lapor-pak-wali-v1.0.0';
const STATIC_CACHE_NAME = 'lapor-pak-wali-static-v1.0.0';
const DYNAMIC_CACHE_NAME = 'lapor-pak-wali-dynamic-v1.0.0';

// Resources to cache immediately
const STATIC_RESOURCES = [
  '/',
  '/index.html',
  '/pages/login.html',
  '/pages/register.html',
  '/pages/dashboard.html',
  '/css/style.css',
  '/js/app.js',
  '/js/login.js',
  '/js/register.js',
  '/js/dashboard.js',
  '/manifest.json',
  // External resources
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Resources to cache on demand
const DYNAMIC_RESOURCES = [
  '/pages/create-report.html',
  '/pages/my-reports.html',
  '/pages/notifications.html',
  '/pages/help.html',
  '/pages/contact.html'
];

// Network-first resources (always try network first)
const NETWORK_FIRST = [
  '/api/',
  '/pages/dashboard.html'
];

// Cache-first resources
const CACHE_FIRST = [
  '/css/',
  '/js/',
  '/images/',
  'https://fonts.googleapis.com',
  'https://cdnjs.cloudflare.com'
];

// Install Event - Cache static resources
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching static resources');
        return cache.addAll(STATIC_RESOURCES);
      })
      .catch((error) => {
        console.error('Service Worker: Error caching static resources', error);
      })
  );
  
  // Force the waiting service worker to become the active service worker
  self.skipWaiting();
});

// Activate Event - Clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE_NAME && 
                cacheName !== DYNAMIC_CACHE_NAME &&
                cacheName !== CACHE_NAME) {
              console.log('Service Worker: Deleting old cache', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Claiming clients');
        return self.clients.claim();
      })
  );
});

// Fetch Event - Handle requests with different strategies
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip chrome-extension and other non-http(s) requests
  if (!url.protocol.startsWith('http')) {
    return;
  }
  
  // Apply different caching strategies based on the request
  if (shouldUseNetworkFirst(request.url)) {
    event.respondWith(networkFirstStrategy(request));
  } else if (shouldUseCacheFirst(request.url)) {
    event.respondWith(cacheFirstStrategy(request));
  } else {
    event.respondWith(staleWhileRevalidateStrategy(request));
  }
});

// Network First Strategy - For dynamic content
function networkFirstStrategy(request) {
  return fetch(request)
    .then((networkResponse) => {
      // If network is successful, cache the response and return it
      if (networkResponse.status === 200) {
        const responseClone = networkResponse.clone();
        caches.open(DYNAMIC_CACHE_NAME)
          .then((cache) => {
            cache.put(request, responseClone);
          });
      }
      return networkResponse;
    })
    .catch(() => {
      // If network fails, try to get from cache
      return caches.match(request)
        .then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // If not in cache, return offline page
          return getOfflinePage(request);
        });
    });
}

// Cache First Strategy - For static assets
function cacheFirstStrategy(request) {
  return caches.match(request)
    .then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      
      // If not in cache, fetch from network and cache
      return fetch(request)
        .then((networkResponse) => {
          if (networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(STATIC_CACHE_NAME)
              .then((cache) => {
                cache.put(request, responseClone);
              });
          }
          return networkResponse;
        })
        .catch(() => {
          return getOfflinePage(request);
        });
    });
}

// Stale While Revalidate Strategy - For regular pages
function staleWhileRevalidateStrategy(request) {
  return caches.match(request)
    .then((cachedResponse) => {
      const fetchPromise = fetch(request)
        .then((networkResponse) => {
          if (networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(DYNAMIC_CACHE_NAME)
              .then((cache) => {
                cache.put(request, responseClone);
              });
          }
          return networkResponse;
        })
        .catch(() => {
          // Network failed, return cached version if available
          return cachedResponse;
        });
      
      // Return cached version immediately if available, otherwise wait for network
      return cachedResponse || fetchPromise;
    });
}

// Helper function to determine caching strategy
function shouldUseNetworkFirst(url) {
  return NETWORK_FIRST.some(pattern => url.includes(pattern));
}

function shouldUseCacheFirst(url) {
  return CACHE_FIRST.some(pattern => url.includes(pattern));
}

// Get offline page based on request type
function getOfflinePage(request) {
  const url = new URL(request.url);
  
  if (request.destination === 'document') {
    // Return cached main page or create simple offline page
    return caches.match('/index.html')
      .then((response) => {
        if (response) {
          return response;
        }
        
        // Create a simple offline response
        return new Response(`
          <!DOCTYPE html>
          <html lang="id">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Offline - Lapor Pak Wali</title>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                text-align: center; 
                padding: 2rem;
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
                color: white;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
              }
              .offline-container {
                background: rgba(255,255,255,0.1);
                padding: 2rem;
                border-radius: 1rem;
                backdrop-filter: blur(10px);
              }
              .icon { font-size: 4rem; margin-bottom: 1rem; }
              h1 { margin: 1rem 0; }
              p { opacity: 0.9; margin-bottom: 2rem; }
              button { 
                background: white; 
                color: #1e40af; 
                border: none; 
                padding: 1rem 2rem; 
                border-radius: 0.5rem; 
                font-weight: 600;
                cursor: pointer;
              }
            </style>
          </head>
          <body>
            <div class="offline-container">
              <div class="icon">📱</div>
              <h1>Anda Sedang Offline</h1>
              <p>Tidak ada koneksi internet. Silakan periksa koneksi Anda dan coba lagi.</p>
              <button onclick="window.location.reload()">Coba Lagi</button>
            </div>
          </body>
          </html>
        `, {
          status: 200,
          statusText: 'OK',
          headers: { 'Content-Type': 'text/html' }
        });
      });
  }
  
  // For other resources, return a generic offline response
  return new Response('Offline', { 
    status: 503, 
    statusText: 'Service Unavailable' 
  });
}

// Background Sync for offline report submissions
self.addEventListener('sync', (event) => {
  console.log('Service Worker: Background sync triggered', event.tag);
  
  if (event.tag === 'background-sync-reports') {
    event.waitUntil(syncOfflineReports());
  }
});

// Sync offline reports when connection is restored
function syncOfflineReports() {
  return new Promise((resolve) => {
    // Get offline reports from IndexedDB or localStorage
    // This is a placeholder - implement actual sync logic
    console.log('Service Worker: Syncing offline reports...');
    
    // Simulate sync process
    setTimeout(() => {
      console.log('Service Worker: Reports synced successfully');
      resolve();
    }, 1000);
  });
}

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('Service Worker: Push notification received');
  
  const options = {
    body: 'Anda memiliki update baru tentang laporan Anda',
    icon: '/images/icon-192x192.png',
    badge: '/images/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Lihat Laporan',
        icon: '/images/checkmark.png'
      },
      {
        action: 'close',
        title: 'Tutup',
        icon: '/images/xmark.png'
      }
    ],
    requireInteraction: true,
    tag: 'lapor-pak-wali-notification'
  };
  
  if (event.data) {
    const data = event.data.json();
    options.body = data.body || options.body;
    options.data = { ...options.data, ...data };
  }
  
  event.waitUntil(
    self.registration.showNotification('Lapor Pak Wali', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  console.log('Service Worker: Notification clicked', event);
  
  event.notification.close();
  
  if (event.action === 'explore') {
    // Open the app to the reports page
    event.waitUntil(
      clients.openWindow('/pages/my-reports.html')
    );
  } else if (event.action === 'close') {
    // Just close the notification
    return;
  } else {
    // Default action - open the app
    event.waitUntil(
      clients.openWindow('/pages/dashboard.html')
    );
  }
});

// Message handling for communication with main thread
self.addEventListener('message', (event) => {
  console.log('Service Worker: Message received', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
  
  if (event.data && event.data.type === 'CACHE_REPORT') {
    // Cache a report for offline access
    const report = event.data.report;
    caches.open(DYNAMIC_CACHE_NAME)
      .then((cache) => {
        // Create a request/response pair for the report
        const request = new Request(`/api/reports/${report.id}`);
        const response = new Response(JSON.stringify(report), {
          headers: { 'Content-Type': 'application/json' }
        });
        return cache.put(request, response);
      });
  }
});

// Periodic Background Sync (if supported)
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'periodic-sync-reports') {
    event.waitUntil(syncOfflineReports());
  }
});

// Error handling
self.addEventListener('error', (event) => {
  console.error('Service Worker: Error occurred', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
  console.error('Service Worker: Unhandled promise rejection', event.reason);
});

// Utility function to check if the browser is online
function isOnline() {
  return navigator.onLine;
}

// Cache management utilities
function clearOldCaches() {
  return caches.keys()
    .then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== STATIC_CACHE_NAME && 
              cacheName !== DYNAMIC_CACHE_NAME &&
              cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    });
}

// Preload important pages
function preloadPages() {
  const importantPages = [
    '/pages/dashboard.html',
    '/pages/create-report.html',
    '/pages/my-reports.html'
  ];
  
  return caches.open(DYNAMIC_CACHE_NAME)
    .then((cache) => {
      return cache.addAll(importantPages);
    })
    .catch((error) => {
      console.log('Service Worker: Error preloading pages', error);
    });
}

console.log('Service Worker: Script loaded');

// Self-update mechanism
self.addEventListener('updatefound', () => {
  console.log('Service Worker: Update found');
});

// Version logging
console.log(`Service Worker: Version ${CACHE_NAME} loaded`);