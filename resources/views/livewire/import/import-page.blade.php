<div x-data="{ tab: 'notes' }">
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Pipeline</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Import &amp; Session Logs</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Feed in Obsidian notes or a quick session dump — the AI pipeline turns it into new quiz questions.
        </div>
    </div>

    <div class="flex gap-2 mb-4.5">
        <button @click="tab = 'notes'" class="text-[11px] font-semibold tracking-[0.08em] uppercase px-4 py-2 rounded-full border-[1.5px] transition-all"
                :class="tab === 'notes' ? 'text-white border-transparent' : 'text-text-muted border-border'"
                :style="tab === 'notes' ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : ''">
            Obsidian Notes
        </button>
        <button @click="tab = 'session'" class="text-[11px] font-semibold tracking-[0.08em] uppercase px-4 py-2 rounded-full border-[1.5px] transition-all"
                :class="tab === 'session' ? 'text-white border-transparent' : 'text-text-muted border-border'"
                :style="tab === 'session' ? 'background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))' : ''">
            Session Log
        </button>
    </div>

    <div x-show="tab === 'notes'">
        <div @click="window.dispatchEvent(new CustomEvent('toast', {detail:{message:'✓ Nothing persisted yet — wired up in Stage 3'}}))"
             class="p-10 text-center border-[1.5px] border-dashed border-white/15 rounded-2xl bg-white/1.5 mb-4.5 cursor-pointer hover:border-red/35 transition-colors">
            <div class="text-2xl mb-2.5">📥</div>
            <div class="text-[13px] font-medium mb-1">Drop .md files here, or click to browse</div>
            <div class="text-[11px] text-text-muted">Existing Obsidian vault notes — parsed and converted into question candidates</div>
        </div>
    </div>

    <div x-show="tab === 'session'">
        <textarea class="w-full min-h-[160px] bg-white/3 border-[1.5px] border-border rounded-xl px-4.5 py-4 text-white font-mono text-[12.5px] leading-[1.6] resize-y mb-4.5 focus:outline-none focus:border-red"
                  placeholder="What did you run into today?

e.g. x-slot goes inside the component tag, not outside
e.g. this middleware runs before the controller, not after"></textarea>
    </div>

    <div class="flex items-center gap-2.5 my-5.5 flex-wrap text-[11px] text-text-muted">
        <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-green/30 bg-green/8 text-[#4fcf95]">✓ Parsed</div>
        <span class="text-white/20">→</span>
        <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-green/30 bg-green/8 text-[#4fcf95]">✓ AI Converted</div>
        <span class="text-white/20">→</span>
        <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border border-border bg-white/4">Queued to Pool</div>
    </div>

    <button @click="window.dispatchEvent(new CustomEvent('toast', {detail:{message:'✓ Nothing persisted yet — wired up in Stage 3'}}))"
            class="px-[22px] py-2.5 rounded-[10px] text-white text-xs font-semibold tracking-[0.05em] uppercase hover:-translate-y-0.5 transition-transform"
            style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
        Generate Questions
    </button>

    <x-section-label class="mt-6.5">Generated This Session</x-section-label>
    <div class="flex flex-col gap-2.5">
        <x-generated-item difficulty="easy" tag="Laravel">What's the difference between <code>dispatch()</code> and <code>dispatchSync()</code>?</x-generated-item>
        <x-generated-item difficulty="medium" tag="Blade">Fill in the blank: <code>x-slot</code> goes ____ the component tag.</x-generated-item>
        <x-generated-item difficulty="hard" tag="Laravel">Write the middleware registration line for a route-specific group.</x-generated-item>
    </div>
</div>
