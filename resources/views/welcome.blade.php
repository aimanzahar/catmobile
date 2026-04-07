<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FFF8F0">
    <meta name="description" content="Professional cat grooming services — Book online, track your pet's status in real-time, and enjoy convenient pet taxi service.">

    <title>{{ config('app.name', 'Cat Grooming') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-warm-50 text-gray-800 antialiased" x-data="{ mobileMenu: false }">

    {{-- ========== FLOATING PAW DECORATIONS ========== --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
        <span class="absolute top-20 left-6 text-4xl animate-float-paw">🐾</span>
        <span class="absolute top-40 right-8 text-3xl animate-float-paw-reverse" style="animation-delay: 1s;">🐾</span>
        <span class="absolute top-[60%] left-10 text-2xl animate-float-paw" style="animation-delay: 2.5s;">🐾</span>
        <span class="absolute top-[80%] right-12 text-3xl animate-float-paw-reverse" style="animation-delay: 3.5s;">🐾</span>
    </div>

    {{-- ========== NAVIGATION ========== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-b border-gray-100/50 safe-top" x-data="{ scrolled: false }"
         @scroll.window="scrolled = window.scrollY > 20"
         :class="scrolled ? 'shadow-md' : ''">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                {{-- Logo --}}
                <a href="#" class="flex items-center gap-2">
                    <span class="text-2xl">🐱</span>
                    <span class="text-lg font-bold text-brand-700">CatGroom</span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                    <a href="#services" class="hover:text-brand-600 transition-colors">Services</a>
                    <a href="#how-it-works" class="hover:text-brand-600 transition-colors">How It Works</a>
                    <a href="#features" class="hover:text-brand-600 transition-colors">Features</a>
                    <a href="#faq" class="hover:text-brand-600 transition-colors">FAQ</a>
                    <a href="#contact" class="hover:text-brand-600 transition-colors">Contact</a>
                </div>

                {{-- CTA + Mobile Toggle --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-brand-500 to-accent-500 text-white text-sm font-semibold rounded-full shadow-md hover:shadow-lg transition-all hover:scale-105">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="rounded-full border border-brand-200 px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-brand-500 to-accent-500 text-white text-sm font-semibold rounded-full shadow-md hover:shadow-lg transition-all hover:scale-105">
                            Register
                        </a>
                    @endauth
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg hover:bg-brand-100 transition-colors" aria-label="Toggle menu">
                        <svg x-show="!mobileMenu" class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenu" x-cloak class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden glass-card border-t border-white/30 px-4 pb-4">
            <div class="flex flex-col gap-1 pt-2">
                <a @click="mobileMenu = false" href="#services" class="px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-100 transition-colors">Services</a>
                <a @click="mobileMenu = false" href="#how-it-works" class="px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-100 transition-colors">How It Works</a>
                <a @click="mobileMenu = false" href="#features" class="px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-100 transition-colors">Features</a>
                <a @click="mobileMenu = false" href="#faq" class="px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-100 transition-colors">FAQ</a>
                <a @click="mobileMenu = false" href="#contact" class="px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-100 transition-colors">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" @click="mobileMenu = false" class="mt-2 flex items-center justify-center px-5 py-3 bg-gradient-to-r from-brand-500 to-accent-500 text-white font-semibold rounded-full shadow-md">
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center rounded-full border border-brand-200 px-5 py-3 font-semibold text-brand-700">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" @click="mobileMenu = false" class="mt-2 flex items-center justify-center px-5 py-3 border border-brand-200 text-brand-700 font-semibold rounded-full">
                        Login
                    </a>
                    <a href="{{ route('register') }}" @click="mobileMenu = false" class="mt-2 flex items-center justify-center px-5 py-3 bg-gradient-to-r from-brand-500 to-accent-500 text-white font-semibold rounded-full shadow-md">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ========== HERO SECTION ========== --}}
    <section class="relative min-h-screen flex items-center justify-center px-4 pt-20 pb-32 overflow-hidden">
        {{-- Background Gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-warm-50 to-accent-400/10 z-0"></div>
        <div class="absolute top-0 right-0 w-80 h-80 bg-brand-200/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 z-0"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-accent-400/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3 z-0"></div>

        <div class="relative z-10 text-center max-w-lg mx-auto">
            {{-- Cat Illustration --}}
            <div class="mb-6 animate-fade-in-up">
                <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gradient-to-br from-brand-100 to-brand-200 shadow-xl">
                    <span class="text-7xl leading-none" role="img" aria-label="Cat">🐱</span>
                </div>
            </div>

            {{-- Badge --}}
            <div class="animate-fade-in-up" style="animation-delay: 0.15s;">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-white/80 rounded-full text-xs font-semibold text-brand-700 shadow-sm border border-brand-100 backdrop-blur-sm">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    Now Accepting Bookings
                </span>
            </div>

            {{-- Headline --}}
            <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight animate-fade-in-up" style="animation-delay: 0.25s;">
                Purrfect Care for
                <span class="bg-gradient-to-r from-brand-500 to-accent-500 bg-clip-text text-transparent">
                    Your Feline
                </span>
            </h1>

            {{-- Subheadline --}}
            <p class="mt-4 text-lg text-gray-600 leading-relaxed animate-fade-in-up" style="animation-delay: 0.35s;">
                Professional cat grooming with online booking, real-time pet tracking, and convenient pet taxi service — all in one app.
            </p>

            {{-- CTA Buttons --}}
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 animate-fade-in-up" style="animation-delay: 0.45s;">
                <a href="#services" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-brand-500 to-brand-600 text-white text-base font-bold rounded-full shadow-lg hover:shadow-xl transition-all hover:scale-105 animate-cta-pulse">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Book Appointment Now
                </a>
                <a href="#how-it-works" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-gray-700 text-base font-semibold rounded-full shadow-md border border-gray-200 hover:border-brand-300 hover:bg-brand-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    See How It Works
                </a>
            </div>

            {{-- Trust Metrics --}}
            <div class="mt-10 flex items-center justify-center gap-4 sm:gap-6 text-sm text-gray-500 animate-fade-in-up flex-wrap" style="animation-delay: 0.55s;">
                <div class="flex items-center gap-1.5">
                    <span class="text-yellow-400 text-base">★★★★★</span>
                    <span class="font-medium">4.9/5</span>
                </div>
                <div class="w-px h-4 bg-gray-300 hidden sm:block"></div>
                <div class="font-medium">500+ Happy Cats</div>
                <div class="w-px h-4 bg-gray-300 hidden sm:block"></div>
                <div class="font-medium">Est. 2024</div>
            </div>
        </div>
    </section>

    {{-- ========== SERVICES SECTION ========== --}}
    <section id="services" class="relative py-20 px-4">
        <div class="max-w-6xl mx-auto">
            {{-- Section Header --}}
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">Our Services</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    Grooming Packages
                </h2>
                <p class="mt-3 text-gray-500 max-w-md mx-auto">
                    Choose the perfect pampering experience for your beloved cat
                </p>
            </div>

            {{-- Service Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 stagger-children">
                @foreach($services as $service)
                <div class="group relative bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:border-brand-200 hover:-translate-y-1 transition-all duration-300 animate-fade-in-up">
                    {{-- Popular Badge --}}
                    @if($service->slug === 'full-grooming')
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-brand-500 to-accent-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-full shadow-md">
                            ⭐ Most Popular
                        </span>
                    </div>
                    @endif

                    {{-- Icon --}}
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        @if($service->icon === 'scissors')
                            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/></svg>
                        @elseif($service->icon === 'sparkles')
                            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        @elseif($service->icon === 'heart')
                            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        @elseif($service->icon === 'truck')
                            <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        @else
                            <span class="text-2xl">✨</span>
                        @endif
                    </div>

                    {{-- Content --}}
                    <h3 class="text-lg font-bold text-gray-900">{{ $service->name }}</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed line-clamp-3">{{ $service->description }}</p>

                    {{-- Details --}}
                    <div class="mt-4 flex items-center gap-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $service->duration_minutes }} min
                        </span>
                    </div>

                    {{-- Price & CTA --}}
                    <div class="mt-5 flex items-center justify-between">
                        <div>
                            <span class="text-2xl font-extrabold text-brand-600">RM{{ number_format($service->price, 0) }}</span>
                        </div>
                        <a href="#" class="inline-flex items-center gap-1 px-4 py-2 bg-brand-50 text-brand-600 text-sm font-semibold rounded-full hover:bg-brand-100 transition-colors">
                            Book
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== HOW IT WORKS ========== --}}
    <section id="how-it-works" class="py-20 px-4 bg-white">
        <div class="max-w-4xl mx-auto">
            {{-- Section Header --}}
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">Simple Process</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    How It Works
                </h2>
                <p class="mt-3 text-gray-500 max-w-md mx-auto">
                    Book your cat's grooming session in 4 easy steps
                </p>
            </div>

            {{-- Steps --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 stagger-children">
                @php
                    $steps = [
                        ['num' => '01', 'icon' => '🔍', 'title' => 'Browse Services', 'desc' => 'Explore our grooming packages and choose the perfect one for your cat.'],
                        ['num' => '02', 'icon' => '📅', 'title' => 'Pick a Slot', 'desc' => 'Select your preferred date and time slot from available options.'],
                        ['num' => '03', 'icon' => '🚗', 'title' => 'Drop Off or Taxi', 'desc' => 'Bring your cat or use our pet taxi service for hassle-free pickup.'],
                        ['num' => '04', 'icon' => '✨', 'title' => 'Collect & Enjoy', 'desc' => 'Get notified when your cat is ready. Pick up your freshly groomed feline!'],
                    ];
                @endphp

                @foreach($steps as $step)
                <div class="relative text-center animate-fade-in-up">
                    {{-- Step Number --}}
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 mb-4 relative">
                        <span class="text-3xl">{{ $step['icon'] }}</span>
                        <span class="absolute -top-2 -right-2 w-7 h-7 bg-brand-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-md">
                            {{ $step['num'] }}
                        </span>
                    </div>

                    {{-- Connector Line (hidden on mobile, shown on desktop between items) --}}
                    @if(!$loop->last)
                    <div class="hidden lg:block absolute top-8 left-[60%] w-[80%] border-t-2 border-dashed border-brand-200"></div>
                    @endif

                    <h3 class="text-base font-bold text-gray-900">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== FEATURES SECTION ========== --}}
    <section id="features" class="py-20 px-4 bg-gradient-to-b from-warm-50 to-white">
        <div class="max-w-5xl mx-auto">
            {{-- Section Header --}}
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">Why Choose Us</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    Everything You Need
                </h2>
                <p class="mt-3 text-gray-500 max-w-md mx-auto">
                    A complete digital grooming experience for you and your cat
                </p>
            </div>

            {{-- Features Grid --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 stagger-children">
                @php
                    $features = [
                        ['icon' => '📱', 'title' => 'Easy Mobile Booking', 'desc' => 'Book anytime, anywhere with our intuitive mobile app.', 'color' => 'from-blue-50 to-blue-100'],
                        ['icon' => '📍', 'title' => 'Real-Time Tracking', 'desc' => 'Track your pet\'s grooming status live in the app.', 'color' => 'from-green-50 to-green-100'],
                        ['icon' => '💳', 'title' => 'Online Payments', 'desc' => 'Pay securely online or choose cash on arrival.', 'color' => 'from-purple-50 to-purple-100'],
                        ['icon' => '🔔', 'title' => 'Push Notifications', 'desc' => 'Get instant alerts for bookings, updates, and when your pet is ready.', 'color' => 'from-yellow-50 to-yellow-100'],
                        ['icon' => '🐈', 'title' => 'Pet Profiles', 'desc' => 'Save your cat\'s details — breed, weight, and special notes.', 'color' => 'from-pink-50 to-pink-100'],
                        ['icon' => '🚕', 'title' => 'Pet Taxi Service', 'desc' => 'Door-to-door pickup and drop-off for your convenience.', 'color' => 'from-orange-50 to-orange-100'],
                    ];
                @endphp

                @foreach($features as $feature)
                <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-brand-200 transition-all duration-300 animate-fade-in-up">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $feature['color'] }} flex items-center justify-center mb-3">
                        <span class="text-2xl">{{ $feature['icon'] }}</span>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">{{ $feature['title'] }}</h3>
                    <p class="mt-1.5 text-xs sm:text-sm text-gray-500 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== GALLERY SECTION ========== --}}
    <section id="gallery" class="py-20 px-4 bg-white">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">Gallery</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    Happy Cats, Happy Owners
                </h2>
                <p class="mt-3 text-gray-500 max-w-md mx-auto">
                    See the beautiful results of our grooming services
                </p>
            </div>

            {{-- Gallery Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                @php
                    $galleryItems = [
                        ['emoji' => '😺', 'label' => 'After Basic Grooming', 'bg' => 'from-amber-100 to-orange-100'],
                        ['emoji' => '😸', 'label' => 'Spa Day Bliss', 'bg' => 'from-pink-100 to-rose-100'],
                        ['emoji' => '😻', 'label' => 'Full Makeover', 'bg' => 'from-violet-100 to-purple-100'],
                        ['emoji' => '🙀', 'label' => 'Before & After', 'bg' => 'from-sky-100 to-blue-100'],
                        ['emoji' => '😽', 'label' => 'Relaxed & Clean', 'bg' => 'from-emerald-100 to-teal-100'],
                        ['emoji' => '😼', 'label' => 'Ready to Go Home', 'bg' => 'from-yellow-100 to-amber-100'],
                    ];
                @endphp

                @foreach($galleryItems as $item)
                <div class="group relative aspect-square rounded-2xl bg-gradient-to-br {{ $item['bg'] }} overflow-hidden cursor-pointer hover:shadow-lg transition-all duration-300">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-6xl sm:text-7xl group-hover:scale-110 transition-transform duration-300">{{ $item['emoji'] }}</span>
                    </div>
                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <p class="text-white text-xs sm:text-sm font-semibold">{{ $item['label'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== TESTIMONIALS ========== --}}
    <section class="py-20 px-4 bg-gradient-to-b from-warm-50 to-white">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">Testimonials</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    What Cat Parents Say
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 stagger-children">
                @php
                    $testimonials = [
                        ['name' => 'Sarah A.', 'text' => 'My cat Mimi always comes back looking amazing! The online booking makes it so easy to schedule. Absolutely love the pet taxi service too!', 'rating' => 5, 'avatar' => '👩'],
                        ['name' => 'Ahmad R.', 'text' => 'Best grooming service in town. The real-time tracking feature gives me peace of mind knowing my cat is in good hands.', 'rating' => 5, 'avatar' => '👨'],
                        ['name' => 'Lisa T.', 'text' => 'The spa package is worth every ringgit! My Persian cat looks and smells incredible after every visit. Highly recommend!', 'rating' => 5, 'avatar' => '👩‍🦰'],
                    ];
                @endphp

                @foreach($testimonials as $testimonial)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 animate-fade-in-up">
                    <div class="flex items-center gap-0.5 mb-3">
                        @for($i = 0; $i < $testimonial['rating']; $i++)
                            <span class="text-yellow-400 text-sm">★</span>
                        @endfor
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed italic">"{{ $testimonial['text'] }}"</p>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="text-2xl">{{ $testimonial['avatar'] }}</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $testimonial['name'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== FAQ SECTION ========== --}}
    <section id="faq" class="py-20 px-4 bg-white">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">FAQ</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    Frequently Asked Questions
                </h2>
            </div>

            <div class="space-y-3" x-data="{ openFaq: null }">
                @php
                    $faqs = [
                        ['q' => 'What grooming services do you offer?', 'a' => 'We offer three grooming packages: Basic Grooming (bath, blow dry, nail trim, ear cleaning), Full Grooming (everything in Basic plus haircut, teeth brushing, de-shedding), and our premium Spa Package (everything in Full plus aromatherapy bath, deep conditioning, paw massage).'],
                        ['q' => 'How does the pet taxi service work?', 'a' => 'Our pet taxi provides door-to-door pickup and drop-off. When booking, simply add the taxi service, provide your address, and our trained drivers will safely transport your cat to and from our grooming center.'],
                        ['q' => 'Can I track my cat during grooming?', 'a' => 'Yes! Our app provides real-time status updates. You\'ll see when your cat arrives, when grooming starts, progress updates, and a notification when your pet is ready for pickup.'],
                        ['q' => 'What payment methods do you accept?', 'a' => 'We accept online payments through the app for a seamless experience, as well as cash on arrival at our grooming center. Payment receipts are available in your booking history.'],
                        ['q' => 'How far in advance should I book?', 'a' => 'We recommend booking at least 2-3 days in advance, especially for weekends. However, same-day appointments may be available depending on slot availability.'],
                        ['q' => 'Is my cat safe during the taxi ride?', 'a' => 'Absolutely. Our drivers are trained in pet handling, and we use secure, comfortable pet carriers with proper ventilation. Your cat\'s safety is our top priority.'],
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                <div class="bg-warm-50 rounded-2xl border border-gray-100 overflow-hidden transition-all duration-200"
                     :class="openFaq === {{ $index }} ? 'shadow-md border-brand-200' : ''">
                    <button @click="openFaq = openFaq === {{ $index }} ? null : {{ $index }}"
                            class="w-full flex items-center justify-between px-5 py-4 text-left">
                        <span class="text-sm font-semibold text-gray-800 pr-4">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 text-brand-500 shrink-0 transition-transform duration-200"
                             :class="openFaq === {{ $index }} ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === {{ $index }}"
                         x-collapse
                         x-cloak>
                        <div class="px-5 pb-4 text-sm text-gray-600 leading-relaxed">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== CONTACT / ABOUT SECTION ========== --}}
    <section id="contact" class="py-20 px-4 bg-gradient-to-b from-warm-50 to-brand-50">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-14">
                <span class="inline-block px-4 py-1.5 bg-brand-100 text-brand-700 text-xs font-bold uppercase tracking-wider rounded-full">Contact Us</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">
                    Get In Touch
                </h2>
                <p class="mt-3 text-gray-500 max-w-md mx-auto">
                    Have questions? We'd love to hear from you
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                {{-- Location --}}
                <div class="bg-white rounded-2xl p-6 text-center shadow-sm border border-gray-100">
                    <div class="w-12 h-12 rounded-xl bg-brand-100 flex items-center justify-center mx-auto mb-3">
                        <span class="text-2xl">📍</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Visit Us</h3>
                    <p class="mt-1 text-xs text-gray-500 leading-relaxed">123 Jalan Kucing<br>Kuala Lumpur, 50000<br>Malaysia</p>
                </div>

                {{-- Phone --}}
                <div class="bg-white rounded-2xl p-6 text-center shadow-sm border border-gray-100">
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mx-auto mb-3">
                        <span class="text-2xl">📞</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Call Us</h3>
                    <p class="mt-1 text-xs text-gray-500">+60 12-345 6789</p>
                    <p class="text-xs text-gray-400 mt-1">Mon-Sat, 9AM - 7PM</p>
                </div>

                {{-- Email --}}
                <div class="bg-white rounded-2xl p-6 text-center shadow-sm border border-gray-100">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mx-auto mb-3">
                        <span class="text-2xl">✉️</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Email Us</h3>
                    <p class="mt-1 text-xs text-gray-500">hello&#64;catgroom.my</p>
                    <p class="text-xs text-gray-400 mt-1">We reply within 24 hours</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== CTA BANNER ========== --}}
    <section class="py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-500 via-brand-600 to-accent-500 p-8 sm:p-12 text-center text-white shadow-xl">
                {{-- Decorative Elements --}}
                <div class="absolute top-0 left-0 w-40 h-40 bg-white/10 rounded-full blur-2xl -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-60 h-60 bg-white/10 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>

                <div class="relative z-10">
                    <span class="text-5xl mb-4 inline-block">🐱</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold">
                        Ready to Pamper Your Cat?
                    </h2>
                    <p class="mt-3 text-white/80 max-w-md mx-auto text-sm sm:text-base">
                        Book your first grooming session today and give your feline friend the care they deserve.
                    </p>
                    <a href="#services" class="mt-6 inline-flex items-center gap-2 px-8 py-4 bg-white text-brand-600 font-bold rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                        Book Now — It's Easy!
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FOOTER ========== --}}
    <footer class="bg-gray-900 text-white pt-16 pb-28 sm:pb-8 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8">
                {{-- Brand --}}
                <div class="col-span-2 sm:col-span-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-2xl">🐱</span>
                        <span class="text-lg font-bold">CatGroom</span>
                    </div>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Professional cat grooming services with love and care.
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="text-sm font-semibold mb-3 text-gray-300">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#services" class="hover:text-brand-400 transition-colors">Services</a></li>
                        <li><a href="#how-it-works" class="hover:text-brand-400 transition-colors">How It Works</a></li>
                        <li><a href="#faq" class="hover:text-brand-400 transition-colors">FAQ</a></li>
                        <li><a href="#contact" class="hover:text-brand-400 transition-colors">Contact</a></li>
                    </ul>
                </div>

                {{-- Services --}}
                <div>
                    <h4 class="text-sm font-semibold mb-3 text-gray-300">Services</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Basic Grooming</li>
                        <li>Full Grooming</li>
                        <li>Spa Package</li>
                        <li>Pet Taxi</li>
                    </ul>
                </div>

                {{-- Hours --}}
                <div>
                    <h4 class="text-sm font-semibold mb-3 text-gray-300">Opening Hours</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Mon-Fri: 9AM - 7PM</li>
                        <li>Saturday: 9AM - 5PM</li>
                        <li>Sunday: Closed</li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    &copy; {{ date('Y') }} CatGroom. All rights reserved. Built as a Final Year Project.
                </p>
                <div class="flex items-center gap-4 text-gray-400">
                    <a href="#" class="hover:text-brand-400 transition-colors" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="hover:text-brand-400 transition-colors" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="#" class="hover:text-brand-400 transition-colors" aria-label="WhatsApp">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ========== STICKY BOTTOM CTA (Mobile Only) ========== --}}
    <div class="fixed bottom-0 left-0 right-0 z-50 sm:hidden safe-bottom" x-data="{ show: false }" @scroll.window="show = window.scrollY > 600" x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full">
        <div class="bg-white/90 backdrop-blur-lg border-t border-gray-200 px-4 py-3">
            <a href="#services" class="flex items-center justify-center gap-2 w-full py-3.5 bg-gradient-to-r from-brand-500 to-brand-600 text-white font-bold rounded-2xl shadow-lg active:scale-[0.98] transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Book Grooming Now
            </a>
        </div>
    </div>

    {{-- Alpine.js x-cloak style --}}
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
