@props(['label'])

<div {{ $attributes->class(['card', 'p-[18px_20px]']) }}>
    <div class="text-[9.5px] font-semibold tracking-[0.16em] uppercase text-text-muted mb-2">{{ $label }}</div>
    <div class="font-display text-[26px] font-bold">{{ $slot }}</div>
</div>
