{{--
    The custom install affordance. Two mutually exclusive faces:

      • Chromium/Edge/Android — a real button, shown only once the browser has
        told us the app is installable by firing `beforeinstallprompt`.
      • iOS Safari — a short instruction, because Safari has no programmatic
        install and the only route is the Share sheet.

    Both stay hidden when the app is already running standalone. The state
    lives in resources/js/app.js; this file is only its face.

    Anchored bottom-right so it never lands on <x-toast />, which owns
    bottom-centre.
--}}
<div x-data="installPrompt" x-cloak class="fixed bottom-7 right-7 z-40 max-[560px]:left-5 max-[560px]:right-5 max-[560px]:bottom-5">

    {{-- Chromium and friends --}}
    <button
        type="button"
        x-show="canInstall"
        x-transition
        x-on:click="install()"
        :disabled="busy"
        aria-label="Install ICVault as an app"
        class="flex items-center gap-2.5 px-[22px] py-2.5 rounded-[10px] text-white
               text-xs font-semibold tracking-[0.06em] uppercase
               shadow-[0_8px_24px_rgba(0,0,0,0.45)]
               transition-transform hover:-translate-y-0.5
               disabled:opacity-60 disabled:pointer-events-none
               max-[560px]:w-full max-[560px]:justify-center"
        style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))"
    >
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3v12" />
            <path d="M7 11l5 5 5-5" />
            <path d="M4 21h16" />
        </svg>
        <span x-text="busy ? 'Installing…' : 'Install App'">Install App</span>
    </button>

    {{-- iOS Safari --}}
    <div
        x-show="showIosHint"
        x-transition
        role="note"
        class="card p-[14px_16px] max-w-[320px] max-[560px]:max-w-none
               shadow-[0_8px_24px_rgba(0,0,0,0.45)]"
    >
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-red" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M12 3v12" />
                <path d="M8 7l4-4 4 4" />
                <path d="M6 12H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-1" />
            </svg>

            <div class="flex-1">
                <div class="text-[9.5px] font-bold tracking-[0.18em] uppercase text-red mb-1">
                    Add to Home Screen
                </div>
                <div class="text-[12px] leading-[1.55] text-white/80">
                    Tap the <b class="font-semibold text-white">Share</b> icon in Safari's toolbar,
                    then choose <b class="font-semibold text-white">Add to Home Screen</b>.
                </div>
            </div>

            <button
                type="button"
                x-on:click="dismissIosHint()"
                aria-label="Dismiss install instructions"
                class="shrink-0 -mt-1 -mr-1 w-6 h-6 rounded-[6px] text-text-muted
                       hover:text-white hover:bg-white/5 transition-colors"
            >&times;</button>
        </div>
    </div>

</div>
