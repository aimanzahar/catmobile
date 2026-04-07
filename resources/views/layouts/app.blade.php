<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FFF8F0">
    <title>{{ $title ?? config('app.name', 'Cat Grooming') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-warm-50 text-gray-800 antialiased">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,_rgba(251,113,133,0.12),_transparent_28%),linear-gradient(180deg,_rgba(255,248,240,1)_0%,_rgba(255,251,245,1)_100%)]">
        <nav class="border-b border-brand-100 bg-white/90 backdrop-blur-xl">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-2 text-lg font-extrabold text-brand-700">
                    <span class="text-2xl">🐱</span>
                    <span>CatGroom</span>
                </a>

                <div class="flex items-center gap-2 text-sm font-semibold">
                    <a href="{{ route('landing') }}" class="rounded-full px-4 py-2 text-gray-600 hover:bg-brand-50 hover:text-brand-700">Home</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full px-4 py-2 text-gray-600 hover:bg-brand-50 hover:text-brand-700">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full bg-gradient-to-r from-brand-500 to-accent-500 px-4 py-2 text-white shadow-sm transition hover:shadow-md">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-gray-600 hover:bg-brand-50 hover:text-brand-700">Login</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-gradient-to-r from-brand-500 to-accent-500 px-4 py-2 text-white shadow-sm transition hover:shadow-md">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
