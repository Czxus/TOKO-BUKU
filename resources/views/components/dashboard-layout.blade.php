@props(['title' => 'Dasbor', 'subtitle' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} · {{ config('app.name', 'Toko Buku') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-krem text-tinta">
        <div class="min-h-screen lg:flex">
            <livewire:layout.sidebar />

            <main class="flex-1 min-w-0 p-5 lg:p-8 space-y-6">
                <header class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="font-serif text-2xl lg:text-3xl font-semibold">{{ $title }}</h1>
                        @if ($subtitle)
                            <p class="text-sm text-gray-600 mt-1">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        {{ $actions ?? '' }}
                        <div class="h-11 w-11 rounded-full bg-amber-buku grid place-items-center text-sm font-semibold text-tinta" aria-hidden="true">
                            {{ strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    </div>
                </header>

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
