@props(['difficulty', 'tag'])

@php
    $dotColors = [
        'easy' => '#4fcf95',
        'medium' => '#e0a94a',
        'hard' => '#ec5c86',
    ];
@endphp

<div {{ $attributes->class(['card', 'p-[14px_16px] flex items-center gap-3']) }}>
    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $dotColors[$difficulty] ?? $dotColors['medium'] }}"></span>
    <span class="text-[12.5px] flex-1 [&_code]:font-mono [&_code]:text-[11.5px] [&_code]:bg-white/8 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded">{{ $slot }}</span>
    <span class="text-[9.5px] text-text-muted px-2.5 py-1 rounded-full border border-border">{{ $tag }}</span>
</div>
