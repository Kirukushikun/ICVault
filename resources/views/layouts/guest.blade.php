<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="icon" href="{{ asset('images/ic-icon.ico') }}" sizes="any">

        @include('partials.pwa-head')

        <title>{{ $title ?? 'ICVault' }}</title>

        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=League+Spartan:wght@400;600;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen flex items-center justify-center text-white p-6">
        {{ $slot }}

        <x-toast />
        <x-install-prompt />

        @livewireScripts
    </body>
</html>
