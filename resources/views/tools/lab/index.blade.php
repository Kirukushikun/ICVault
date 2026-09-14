<div>
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Lab Vault</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Projects</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Loose mini-systems parked here — static pages and localStorage prototypes, some destined
            for a real backing store.
        </div>
    </div>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
        @foreach ($projects as $slug => $project)
            {{-- Plain link, not wire:navigate: a project ships its own inline script,
                 which an SPA body swap would not re-run. --}}
            <a href="{{ route('lab.project', $slug) }}"
               style="--accent: {{ $project['accent'] }};"
               class="card group relative overflow-hidden p-6 no-underline text-white
                      transition-[transform,border-color,box-shadow] duration-200
                      hover:-translate-y-[3px] hover:border-[color-mix(in_srgb,var(--accent)_38%,transparent)]
                      hover:shadow-[0_8px_24px_rgba(0,0,0,0.4)]">

                <div class="flex items-start justify-between gap-3 mb-2.5">
                    <div class="text-[9px] font-bold tracking-[0.2em] uppercase text-[var(--accent)]">
                        {{ $project['eyebrow'] }}
                    </div>
                    <span class="text-[8.5px] font-bold tracking-[0.1em] uppercase px-2 py-[3px] rounded-full
                                 {{ $project['storage'] === 'database' ? 'bg-green/15 text-green' : 'bg-amber/15 text-amber' }}">
                        {{ $project['storage'] === 'database' ? 'Database' : 'Local Only' }}
                    </span>
                </div>

                <div class="font-display text-[20px] font-bold tracking-[0.5px] leading-tight">{{ $project['title'] }}</div>
                <div class="text-[11px] tracking-[0.14em] uppercase text-text-muted mt-1 mb-3.5">{{ $project['subtitle'] }}</div>

                <div class="text-[11.5px] text-text-muted leading-[1.55] mb-5">{{ $project['blurb'] }}</div>

                <div class="flex items-center justify-end text-[10.5px] text-text-muted">
                    <span class="text-[13px] opacity-60 group-hover:opacity-100 group-hover:translate-x-[3px] transition-[transform,opacity] duration-200">→</span>
                </div>
            </a>
        @endforeach
    </div>
</div>
