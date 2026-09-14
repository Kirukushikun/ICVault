/* =============================================================
   Progressive Web App wiring: service worker registration, and the state
   behind the custom install button.

   Why the install state lives here rather than in the Blade component:
   `beforeinstallprompt` fires once, early, and the app navigates with
   `wire:navigate`, which swaps the whole body. An Alpine component holding
   the event would be destroyed on the first navigation and the button would
   never come back. This file is loaded from <head> with Vite's
   navigate-track attribute, so it is evaluated once per hard load and its
   module scope survives every soft navigation after that.
   ============================================================= */

/** Single source of truth for install state, deliberately outside Alpine. */
const pwa = {
    /** The deferred BeforeInstallPromptEvent, or null once used/never fired. */
    deferred: null,
    installed: false,
};

/* ---------- environment ---------- */

/** Already installed and launched from the home screen — nothing to offer. */
function isStandalone() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.matchMedia('(display-mode: window-controls-overlay)').matches ||
        // Safari's own flag; it does not report a standalone display-mode.
        window.navigator.standalone === true
    );
}

/**
 * iOS Safari never fires `beforeinstallprompt`, so it needs the instructional
 * path instead. Two wrinkles: iPadOS 13+ reports itself as a Mac, and the
 * other iOS browsers are WebKit underneath but none of them expose
 * "Add to Home Screen" — telling a Chrome-on-iOS reader to look for it would
 * send them hunting for a menu item that isn't there.
 */
function isIosSafari() {
    const ua = window.navigator.userAgent;

    const isIos =
        /iPad|iPhone|iPod/.test(ua) ||
        (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

    return isIos && !/CriOS|FxiOS|EdgiOS|OPiOS/i.test(ua);
}

/* Remembering a dismissal is the difference between a hint and a nag. */
const IOS_HINT_DISMISSED = 'icvault:ios-install-dismissed';

function iosHintDismissed() {
    try {
        return window.localStorage.getItem(IOS_HINT_DISMISSED) === '1';
    } catch {
        // Private mode can throw on access; treat it as "not dismissed".
        return false;
    }
}

function rememberIosDismissal() {
    try {
        window.localStorage.setItem(IOS_HINT_DISMISSED, '1');
    } catch {
        /* Nothing to do — the hint simply returns next visit. */
    }
}

/* ---------- install prompt capture ---------- */

window.addEventListener('beforeinstallprompt', (event) => {
    // Without this the browser shows its own mini-infobar and the custom
    // button becomes a second, competing affordance.
    event.preventDefault();

    pwa.deferred = event;
    window.dispatchEvent(new CustomEvent('pwa:installable'));
});

window.addEventListener('appinstalled', () => {
    pwa.deferred = null;
    pwa.installed = true;

    window.dispatchEvent(new CustomEvent('pwa:installed'));
    window.dispatchEvent(
        new CustomEvent('toast', { detail: { message: '✓ ICVault installed' } })
    );
});

/* ---------- the component behind <x-install-prompt /> ---------- */

document.addEventListener('alpine:init', () => {
    window.Alpine.data('installPrompt', () => ({
        canInstall: false,
        showIosHint: false,
        busy: false,

        init() {
            this.sync();

            // Re-read module state rather than trusting local state: after a
            // wire:navigate this component is brand new, but `pwa` is not.
            const resync = () => this.sync();

            window.addEventListener('pwa:installable', resync);
            window.addEventListener('pwa:installed', resync);
        },

        sync() {
            if (isStandalone() || pwa.installed) {
                this.canInstall = false;
                this.showIosHint = false;

                return;
            }

            this.canInstall = pwa.deferred !== null;

            // Only ever the fallback: if a real prompt is available, use it.
            this.showIosHint = !this.canInstall && isIosSafari() && !iosHintDismissed();
        },

        async install() {
            if (!pwa.deferred || this.busy) {
                return;
            }

            this.busy = true;

            try {
                pwa.deferred.prompt();

                const { outcome } = await pwa.deferred.userChoice;

                if (outcome === 'dismissed') {
                    window.dispatchEvent(
                        new CustomEvent('toast', { detail: { message: 'Install cancelled' } })
                    );
                }
            } finally {
                // The event is single-use whatever the reader chose. If they
                // declined, the browser decides when to offer another one, and
                // the listener above will pick it up.
                pwa.deferred = null;
                this.canInstall = false;
                this.busy = false;
            }
        },

        dismissIosHint() {
            this.showIosHint = false;
            rememberIosDismissal();
        },
    }));
});

/* ---------- service worker ---------- */

if ('serviceWorker' in navigator) {
    // After load: registration competes with the page's own resources
    // otherwise, and nothing here is needed for first paint.
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/service-worker.js', {
                scope: '/',
                // The worker script itself must never come from the HTTP
                // cache, or an update can go unnoticed for as long as its
                // max-age.
                updateViaCache: 'none',
            })
            .catch((error) => {
                // A failed registration costs nothing but offline support, so
                // it is reported and swallowed rather than thrown.
                console.warn('[ICVault] Service worker registration failed:', error);
            });
    });
}
