<div
    x-data="{ show: false, message: '' }"
    x-on:toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 2200)"
    x-show="show"
    x-transition
    x-cloak
    class="fixed bottom-7 left-1/2 -translate-x-1/2 bg-[#1a1a1d]/95 border border-green/35 text-[#4fcf95] text-xs font-semibold tracking-[0.06em] px-5 py-2.5 rounded-full shadow-[0_8px_24px_rgba(0,0,0,0.5)] z-50"
    x-text="message"
></div>
