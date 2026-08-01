{{--
    Bare full-bleed layout for Concept Visualizer guides. Guides bring their own
    typography and palette, so the shell deliberately stays out of the way —
    only a floating exit control is added on top.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" href="{{ asset('images/ic-icon.ico') }}" sizes="any">

        <title>{{ $title ?? 'ICVault' }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=League+Gothic&family=League+Spartan:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen">
        <a href="{{ route('visualizer.index') }}" wire:navigate
           class="fixed top-5 left-5 z-50 flex items-center gap-2 px-3.5 py-2 rounded-lg
                  bg-black/55 border border-white/10 backdrop-blur
                  text-[11px] font-semibold tracking-[0.08em] uppercase text-white/60
                  hover:text-white hover:border-white/25 transition-colors">
            ← Guides
        </a>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
