<div>
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Concept Vault</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Field Guides</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Step-by-step walkthroughs of a single topic. Each guide is hand-built, so it can show the thing
            rather than describe it.
        </div>
    </div>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-4">
        @foreach ($guides as $slug => $guide)
            {{-- Plain link, not wire:navigate: a guide ships its own inline script,
                 which an SPA body swap would not re-run. --}}
            <a href="{{ route('visualizer.guide', $slug) }}"
               style="--accent: {{ $guide['accent'] }};"
               class="card group relative overflow-hidden p-6 no-underline text-white
                      transition-[transform,border-color,box-shadow] duration-200
                      hover:-translate-y-[3px] hover:border-[color-mix(in_srgb,var(--accent)_38%,transparent)]
                      hover:shadow-[0_8px_24px_rgba(0,0,0,0.4)]">

                <div class="flex items-start justify-between gap-3 mb-2.5">
                    <div class="text-[9px] font-bold tracking-[0.2em] uppercase text-[var(--accent)]">
                        {{ $guide['eyebrow'] }}
                    </div>
                    @isset($guide['logo'])
                        <img src="{{ asset($guide['logo']) }}" alt=""
                             class="w-8 h-8 object-contain shrink-0 -mt-1 opacity-90 group-hover:opacity-100 transition-opacity" />
                    @endisset
                </div>

                <div class="font-display text-[20px] font-bold tracking-[0.5px] leading-tight">{{ $guide['title'] }}</div>
                <div class="text-[11px] tracking-[0.14em] uppercase text-text-muted mt-1 mb-3.5">{{ $guide['subtitle'] }}</div>

                <div class="text-[11.5px] text-text-muted leading-[1.55] mb-5">{{ $guide['blurb'] }}</div>

                <div class="flex items-center justify-between text-[10.5px] text-text-muted">
                    <span>{{ $guide['steps'] }} steps</span>
                    <span class="text-[13px] opacity-60 group-hover:opacity-100 group-hover:translate-x-[3px] transition-[transform,opacity] duration-200">→</span>
                </div>
            </a>
        @endforeach
    </div>
</div>
