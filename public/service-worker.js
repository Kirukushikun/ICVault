/* =============================================================
   ICVault service worker.

   Lives at the origin root on purpose: a worker's scope is the directory it
   is served from, and this one has to control every page.

   The strategy per request type is not arbitrary — this is an authenticated,
   session-bound Livewire app, and the obvious "cache the app shell" approach
   would actively break it:

     • Every page embeds a CSRF token and a Livewire snapshot. Serving a page
       from cache hands back a stale token, which surfaces as a 419 the reader
       cannot explain or clear.
     • Every page but /login sits behind auth. A cached document could be
       replayed after logout.

   So documents are never cached — they go to the network, and fall back to a
   flat offline page when that fails. What IS cached is the part that is safe
   to: content-hashed build assets, images, and fonts. That still gets you a
   fast repeat load and a graceful offline screen; it does not get you a
   fully offline app, which this app cannot honestly be.
   ============================================================= */

/* Bump to invalidate everything: old caches are dropped on activate. */
const VERSION = 'icvault-v1';

const ASSET_CACHE = `${VERSION}-assets`;
const FONT_CACHE = `${VERSION}-fonts`;
const OWNED_CACHES = [ASSET_CACHE, FONT_CACHE];

const OFFLINE_URL = '/offline.html';

/* The offline page and the marks it needs, so the fallback is never itself a
   broken page. Build assets are absent by design — Vite hashes their names
   per build, so they cannot be named ahead of time and are picked up at
   runtime instead. */
const PRECACHE = [
    OFFLINE_URL,
    '/images/ic-logo.png',
    '/images/ic-icon.ico',
    '/images/icon-192.png',
    '/images/icon-512.png',
    '/images/icon-maskable-512.png',
];

/* Anything session-shaped. Livewire's update endpoint is a POST and would be
   skipped anyway, but naming it here keeps the intent obvious to the next
   reader, and covers the polling GETs some Livewire features make. */
const BYPASS_PREFIXES = ['/livewire/', '/login', '/logout', '/storage/'];

const FONT_ORIGINS = ['https://fonts.googleapis.com', 'https://fonts.gstatic.com'];

const STATIC_FILE = /\.(?:png|jpe?g|gif|svg|webp|avif|ico|woff2?|ttf|otf)$/i;

/* ---------- lifecycle ---------- */

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(ASSET_CACHE)
            // Individually, not addAll: addAll rejects as a unit, so one
            // missing icon would leave the worker with nothing precached.
            .then((cache) => Promise.all(
                PRECACHE.map((url) => cache.add(url).catch(() => null))
            ))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) => Promise.all(
                keys.filter((key) => !OWNED_CACHES.includes(key)).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

/* ---------- strategies ---------- */

/** Only responses we can actually re-serve. Opaque ones have no status to trust. */
function isCacheable(response) {
    return response && response.ok && (response.type === 'basic' || response.type === 'cors');
}

/** For immutable things — a hit is always correct, so never pay for a round trip. */
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (isCacheable(response)) {
        const cache = await caches.open(cacheName);
        await cache.put(request, response.clone());
    }

    return response;
}

/** For things that rarely change but might — serve now, refresh for next time. */
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fresh = fetch(request)
        .then((response) => {
            if (isCacheable(response)) {
                cache.put(request, response.clone());
            }

            return response;
        })
        // Offline with nothing cached is a legitimate outcome here, not a fault.
        .catch(() => null);

    return cached || (await fresh) || Response.error();
}

/**
 * Documents always go to the network. The cache is a fallback for failure,
 * never a source of truth — see the note at the top of this file.
 */
async function networkOnlyWithOfflinePage(request) {
    try {
        return await fetch(request);
    } catch {
        return (await caches.match(OFFLINE_URL)) || Response.error();
    }
}

/* ---------- routing ---------- */

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // A cache can only answer GETs, and anything else here is a mutation we
    // must not replay.
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    const sameOrigin = url.origin === self.location.origin;

    if (sameOrigin && BYPASS_PREFIXES.some((prefix) => url.pathname.startsWith(prefix))) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkOnlyWithOfflinePage(request));

        return;
    }

    // Vite fingerprints these, so a given URL's bytes can never change.
    if (sameOrigin && url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, ASSET_CACHE));

        return;
    }

    if (sameOrigin && STATIC_FILE.test(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request, ASSET_CACHE));

        return;
    }

    if (FONT_ORIGINS.includes(url.origin)) {
        event.respondWith(staleWhileRevalidate(request, FONT_CACHE));

        return;
    }

    // Everything else — API calls, anything unrecognised — is left to the
    // network untouched. Silence is the safe default for a worker.
});
