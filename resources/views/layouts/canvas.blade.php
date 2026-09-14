{{--
    Shared full-bleed layout for Concept Vault guides and Lab Vault projects.
    Both bring their own typography and palette, so the page stays out of
    their way below the bar — but it keeps a platform header so an item reads
    as part of ICVault rather than a loose document someone linked to.

    The caller passes crumbLabel/crumbRoute/backLabel (which tool this is)
    and, optionally, itemTitle/itemAccent (which item within it).

    Links here are deliberately plain (no wire:navigate): an item ships its
    own stylesheet and inline script, and an SPA-style body swap would not
    re-run that script, leaving its interactive parts blank.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" href="{{ asset('images/ic-icon.ico') }}" sizes="any">

        @include('partials.pwa-head')

        <title>{{ $title ?? 'ICVault' }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=League+Gothic&family=League+Spartan:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen">
        <header class="sticky top-0 z-50 flex items-center gap-3 px-5 py-3
                       bg-[#161617]/85 backdrop-blur-md border-b border-border">
            <a href="{{ route('hub') }}" class="flex items-center gap-2 no-underline text-white shrink-0">
                <img src="{{ asset('images/ic-logo.png') }}" alt="" class="w-6 h-6 object-contain" />
                <span class="font-display text-[15px] font-bold tracking-wide leading-none">IC<span class="text-red">Vault</span></span>
            </a>

            <span class="text-white/20 text-[11px] shrink-0">/</span>

            <a href="{{ route($crumbRoute) }}"
               class="text-[12px] text-text-muted hover:text-white no-underline transition-colors shrink-0">
                {{ $crumbLabel }}
            </a>

            @isset($itemTitle)
                <span class="text-white/20 text-[11px] shrink-0 max-[560px]:hidden">/</span>
                <span class="text-[12px] font-medium truncate max-[560px]:hidden"
                      style="color: {{ $itemAccent ?? '#ffffff' }}">{{ $itemTitle }}</span>
            @endisset

            <a href="{{ route($crumbRoute) }}"
               class="ml-auto shrink-0 flex items-center gap-2 px-3.5 py-1.5 rounded-lg
                      border border-border bg-white/3 no-underline
                      text-[11px] font-semibold tracking-[0.08em] uppercase text-text-muted
                      hover:text-white hover:border-white/25 transition-colors">
                ← {{ $backLabel }}
            </a>
        </header>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
