@php
    $tools = app(\App\Platform\Support\ToolRegistry::class)->enabled();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'ICVault' }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=League+Gothic&family=League+Spartan:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen flex max-[820px]:flex-col text-white">
        <div class="w-[240px] shrink-0 bg-bg-secondary border-r border-border flex flex-col p-[26px_16px] gap-6 sticky top-0 h-screen
                    max-[820px]:w-full max-[820px]:h-auto max-[820px]:flex-row max-[820px]:items-center max-[820px]:static max-[820px]:overflow-x-auto">
            <div class="flex flex-col gap-0.5 px-2">
                <div class="font-display text-xl font-bold tracking-wide">IC<span class="text-red">Vault</span></div>
                <div class="text-[9.5px] tracking-[0.14em] uppercase text-text-muted">Personal Platform</div>
            </div>

            {{-- Overview --}}
            <div class="flex flex-col gap-1 max-[820px]:flex-row">
                <div class="text-[9px] font-bold tracking-[0.18em] uppercase text-white/25 px-3 mb-1 max-[820px]:hidden">Overview</div>
                <a href="{{ route('hub') }}" wire:navigate class="nav-item {{ request()->routeIs('hub') ? 'active' : '' }}">
                    <span class="w-4 text-center opacity-90">⌂</span> Hub
                </a>
            </div>

            {{-- Installed tools, straight from config/tools.php --}}
            <div class="flex flex-col gap-1 max-[820px]:flex-row">
                <div class="text-[9px] font-bold tracking-[0.18em] uppercase text-white/25 px-3 mb-1 max-[820px]:hidden">Installed Tools</div>
                @foreach ($tools as $tool)
                    <a href="{{ $tool->url() }}" wire:navigate class="nav-item {{ $tool->isCurrent() ? 'active' : '' }}">
                        <span class="w-4 text-center opacity-90">{{ $tool->icon }}</span> {{ $tool->name }}
                    </a>
                @endforeach
            </div>

            {{-- System --}}
            <div class="flex flex-col gap-1 max-[820px]:flex-row">
                <div class="text-[9px] font-bold tracking-[0.18em] uppercase text-white/25 px-3 mb-1 max-[820px]:hidden">System</div>
                <a href="{{ route('settings') }}" wire:navigate class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <span class="w-4 text-center opacity-90">⚙</span> Settings
                </a>
            </div>

            <div class="mt-auto pt-3 px-2 border-t border-border max-[820px]:hidden">
                <div class="text-[10px] text-white/20 tracking-[0.08em] mb-2">ICVault</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-[10px] text-text-muted hover:text-white tracking-[0.08em] uppercase">Log Out</button>
                </form>
            </div>
        </div>

        <div class="flex-1 max-w-[1080px] mx-auto w-full px-12 py-10 pb-16 max-[820px]:px-5 max-[820px]:py-7 max-[820px]:pb-[50px]">
            {{ $slot }}
        </div>

        <x-toast />

        @livewireScripts
    </body>
</html>
