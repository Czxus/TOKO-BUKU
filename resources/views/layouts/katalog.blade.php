<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Katalog Buku · {{ config('app.name', 'Toko Buku') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-krem text-tinta">
        <header class="bg-hijau text-krem">
            <div class="mx-auto max-w-6xl px-4 lg:px-6 h-16 flex items-center justify-between gap-4">
                <a href="{{ route('katalog') }}" wire:navigate class="flex items-center gap-2.5">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F2B45A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/><path d="M9 8h7"/></svg>
                    <span class="font-serif text-xl font-bold">Toko Buku</span>
                </a>

                <nav class="flex items-center gap-2 text-sm font-medium">
                    @auth
                        <a href="{{ route('dashboard') }}" wire:navigate class="rounded-lg px-4 h-10 inline-flex items-center bg-amber-buku text-tinta font-semibold hover:opacity-90">Dasbor</a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 h-10 inline-flex items-center hover:bg-hijau-tua">Masuk</a>
                        <a href="{{ route('register') }}" wire:navigate class="rounded-lg px-4 h-10 inline-flex items-center bg-amber-buku text-tinta font-semibold hover:opacity-90">Daftar</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 lg:px-6 py-6 lg:py-8">
            {{ $slot }}
        </main>
    </body>
</html>
