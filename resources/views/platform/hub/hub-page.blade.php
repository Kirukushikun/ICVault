@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = str(auth()->user()->name)->explode(' ')->first();
@endphp

<div>
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Personal Platform</div>
        <div class="font-display text-[28px] font-bold tracking-wide">{{ $greeting }}, {{ $firstName }}</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            One base, many tools. Open whichever one you need — nothing here has to know about anything else.
        </div>
    </div>

    <x-section-label>Your Tools</x-section-label>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-4 mb-8">
        @foreach ($tools as $tool)
            <a href="{{ $tool->url() }}" wire:navigate
               style="{{ $tool->accentStyle() }}"
               class="card group relative overflow-hidden p-[22px_22px_20px] no-underline text-white
                      transition-[transform,border-color,box-shadow] duration-200
                      hover:-translate-y-[3px] hover:border-[var(--accent-border)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.4)]">

                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200
                            bg-[linear-gradient(135deg,var(--accent-glow),transparent_60%)]"></div>

                <div class="relative">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-[42px] h-[42px] rounded-xl flex items-center justify-center text-[18px] shrink-0
                                    bg-[var(--accent-bg)] text-[var(--accent)]">
                            {{ $tool->icon }}
                        </div>

                        @if ($tool->status === 'beta')
                            <span class="text-[8.5px] font-bold tracking-[0.1em] uppercase px-2 py-[3px] rounded-full bg-amber/15 text-amber">Beta</span>
                        @else
                            <span class="text-[8.5px] font-bold tracking-[0.1em] uppercase px-2 py-[3px] rounded-full bg-green/15 text-green">Active</span>
                        @endif
                    </div>

                    <div class="font-display text-[16.5px] font-bold tracking-[0.4px] mb-1.5">{{ $tool->name }}</div>
                    <div class="text-[11.5px] text-text-muted leading-[1.55] mb-4 min-h-[34px]">{{ $tool->tagline }}</div>

                    <div class="flex items-center justify-between text-[10.5px] text-text-muted">
                        <span>{{ $tool->summary() ?? 'Ready' }}</span>
                        <span class="text-[13px] opacity-60 group-hover:opacity-100 group-hover:translate-x-[3px] transition-[transform,opacity] duration-200">→</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <x-section-label>Recent Activity</x-section-label>

    @if (empty($activity))
        <div class="card p-7 text-center">
            <div class="text-[13px] text-white/70 font-medium mb-1">Nothing yet</div>
            <div class="text-[11.5px] text-text-muted">Activity from your tools shows up here as you use them.</div>
        </div>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($activity as $item)
                <div class="card flex items-center gap-3 px-4 py-3">
                    <span class="w-[7px] h-[7px] rounded-full shrink-0" style="background: {{ $item->tool->accent }}"></span>
                    <span class="text-[12px] flex-1">{{ $item->text }}</span>
                    <span class="text-[9px] font-bold tracking-[0.08em] uppercase px-2 py-[2px] rounded-full text-text-muted border border-border shrink-0">
                        {{ $item->tool->name }}
                    </span>
                    <span class="text-[10.5px] text-text-muted shrink-0">{{ $item->at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
