@props(['difficulty'])

@php
    $colors = [
        'easy' => 'bg-green/15 text-[#4fcf95]',
        'medium' => 'bg-amber/15 text-[#e0a94a]',
        'hard' => 'bg-red/15 text-[#ec5c86]',
    ];
@endphp

<span {{ $attributes->class(['inline-block text-[9px] font-bold tracking-[0.14em] uppercase px-2.5 py-1 rounded-full', $colors[$difficulty] ?? $colors['medium']]) }}>
    {{ $slot->isEmpty() ? ucfirst($difficulty) : $slot }}
</span>
