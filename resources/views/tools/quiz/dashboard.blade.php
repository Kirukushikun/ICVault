<div>
    <div class="mb-7">
        <div class="text-[10px] font-semibold tracking-[0.25em] uppercase text-red mb-1.5">Brain Maintenance</div>
        <div class="font-display text-[28px] font-bold tracking-wide">Good evening, IC</div>
        <div class="text-[12.5px] text-text-muted mt-1.5 font-light max-w-[560px]">
            Open something, answer a few things, close it. Here's where your knowledge stands today.
        </div>
    </div>

    <div class="grid grid-cols-3 max-[820px]:grid-cols-1 gap-3.5 mb-5">
        <x-stat-card label="Current Streak">12 <small class="text-[13px] text-text-muted font-normal">days</small></x-stat-card>
        <x-stat-card label="Question Pool">184 <small class="text-[13px] text-text-muted font-normal">cards</small></x-stat-card>
        <x-stat-card label="Avg. Recall">78<small class="text-[13px] text-text-muted font-normal">%</small></x-stat-card>
    </div>

    <div class="card p-[22px_24px] mb-5">
        <div class="flex justify-between items-center mb-3.5">
            <div class="text-[13px] font-semibold">Today's Quota</div>
            <div class="text-xs text-text-muted"><strong class="text-white">{{ $completedCount }}</strong> / {{ $quota }} answered</div>
        </div>
        <div class="w-full h-2 rounded-full bg-white/6 overflow-hidden">
            <div class="h-full rounded-full" style="width:{{ $quota ? min(100, $completedCount / $quota * 100) : 0 }}%; background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))"></div>
        </div>
        <a href="{{ route('quiz.session') }}" wire:navigate class="mt-[18px] inline-flex items-center gap-2 text-white px-[22px] py-2.5 rounded-[10px] text-xs font-semibold tracking-[0.06em] uppercase transition-transform hover:-translate-y-0.5" style="background:linear-gradient(90deg,var(--color-red-dim),var(--color-red))">
            Continue Session →
        </a>
    </div>

    @if ($tip)
        <div class="card p-[18px_22px] mb-5 flex gap-3.5 items-start" style="border-color:rgba(195,7,63,0.25); background:linear-gradient(135deg,rgba(195,7,63,0.08),var(--color-card-bg) 60%)">
            <div class="text-xl leading-none">💡</div>
            <div>
                <div class="text-[9.5px] font-bold tracking-[0.18em] uppercase text-red mb-1">Did You Know</div>
                <div class="text-[13px] leading-[1.5] text-white/85 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">
                    {!! $tip->body !!}
                </div>
            </div>
        </div>
    @endif

    <x-section-label>Categories</x-section-label>
    <div class="grid gap-3" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
        @foreach ([
            ['name' => 'Laravel', 'count' => 52, 'pct' => 82, 'gradient' => 'linear-gradient(90deg,#1a5f7a,#2e9cca)'],
            ['name' => 'Vue / Blade', 'count' => 38, 'pct' => 64, 'gradient' => 'linear-gradient(90deg,#5c3a7a,#9b5fcf)'],
            ['name' => 'Git', 'count' => 21, 'pct' => 91, 'gradient' => 'linear-gradient(90deg,var(--color-red-dim),var(--color-red))'],
            ['name' => 'SQL', 'count' => 29, 'pct' => 47, 'gradient' => 'linear-gradient(90deg,#333,#666)'],
            ['name' => 'JS Fundamentals', 'count' => 44, 'pct' => 73, 'gradient' => 'linear-gradient(90deg,#1a5f7a,#2e9cca)'],
        ] as $category)
            <div class="card p-[16px_18px] cursor-pointer transition-transform hover:-translate-y-0.5">
                <div class="flex justify-between items-center mb-2.5">
                    <span class="text-[13px] font-semibold">{{ $category['name'] }}</span>
                    <span class="text-[10px] text-text-muted">{{ $category['count'] }} cards</span>
                </div>
                <div class="w-full h-[5px] rounded-full bg-white/6 overflow-hidden mb-1.5">
                    <div class="h-full rounded-full" style="width:{{ $category['pct'] }}%; background:{{ $category['gradient'] }}"></div>
                </div>
                <div class="text-[10px] text-text-muted">{{ $category['pct'] }}% mastery</div>
            </div>
        @endforeach
    </div>
</div>
