<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FFF8F0">
    <title>Service unavailable</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-warm-50 text-gray-800 antialiased">
    <main class="flex min-h-screen min-h-[100dvh] items-center justify-center px-5 py-10">
        <section class="w-full max-w-sm rounded-2xl border border-black/5 bg-white p-6 text-center shadow-xl shadow-brand-500/10">
            <img src="{{ asset('images/logo.jpeg') }}" alt="PurrfectCat Groom" class="mx-auto h-16 w-16 rounded-2xl object-cover shadow-md">
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.2em] text-brand-600">Service unavailable</p>
            <h1 class="mt-2 text-2xl font-extrabold text-gray-900">Could not reach the booking service</h1>
            <p class="mt-3 text-sm leading-6 text-gray-500">
                The app could not connect to the server. Check your internet connection, then try again.
            </p>
            <a href="{{ url()->current() }}" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-md shadow-brand-500/20">
                Try again
            </a>
        </section>
    </main>
</body>
</html>