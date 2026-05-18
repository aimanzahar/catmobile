<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FFF8F0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'PurrfectCat Groom') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen min-h-[100dvh] bg-warm-50 text-gray-800 antialiased">
    <div class="min-h-screen min-h-[100dvh] bg-[radial-gradient(circle_at_top_right,_rgba(251,113,133,0.08),_transparent_40%),linear-gradient(180deg,_rgba(255,248,240,1)_0%,_rgba(255,251,245,1)_100%)]">

        @php
            $authedUser = auth()->user();
            $isAdmin = $authedUser && method_exists($authedUser, 'isAdmin') && $authedUser->isAdmin();
        @endphp

        {{-- ── Minimal top bar ── --}}
        <header class="safe-top sticky top-0 z-40 border-b border-black/5 bg-white/90 backdrop-blur-xl">
            <div class="flex items-center justify-between px-5 pb-3">
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('landing') }}" class="flex items-center gap-2 text-base font-extrabold text-brand-700">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="PurrfectCat Groom" class="h-7 w-7 rounded-md object-cover">
                    <span>
                        PurrfectCat Groom
                        @if ($isAdmin)
                            <span class="ml-1 rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold text-brand-700 align-middle">ADMIN</span>
                        @endif
                    </span>
                </a>
                @guest
                    <div class="flex items-center gap-2 text-sm font-semibold">
                        <a href="{{ route('login') }}" class="px-3 py-1.5 text-gray-500">Login</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-brand-600 px-4 py-1.5 text-white">Register</a>
                    </div>
                @endguest
                @auth
                    <a href="{{ route('notifications.index') }}"
                       x-data="notificationBell()"
                       x-init="init()"
                       class="relative flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-500 hover:text-brand-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span x-show="unread > 0" x-cloak
                              class="absolute -top-0.5 -right-0.5 flex min-w-[18px] h-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                              x-text="unread > 99 ? '99+' : unread"></span>
                    </a>
                @endauth
            </div>
        </header>

        {{-- ── Main content ── --}}
        <main class="px-5 py-5 @auth has-tab-bar @endauth">
            @if (session('status'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

        {{-- ── Bottom tab bar (authenticated only) ── --}}
        @auth
            @if ($isAdmin)
                <nav class="bottom-tab-bar">
                    <a href="{{ route('admin.dashboard') }}"
                       class="tab-item {{ ($activeSection ?? '') === 'admin-dashboard' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('admin.bookings.index') }}"
                       class="tab-item {{ ($activeSection ?? '') === 'admin-bookings' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        <span>Bookings</span>
                    </a>
                    <a href="{{ route('admin.chats.index') }}"
                       class="tab-item {{ ($activeSection ?? '') === 'admin-chats' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                        <span>Chats</span>
                    </a>
                    <a href="{{ route('profile.edit') }}"
                       class="tab-item {{ ($activeSection ?? '') === 'profile' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span>Profile</span>
                    </a>
                </nav>
            @else
                <nav class="bottom-tab-bar">
                    <a href="{{ route('dashboard', ['section' => 'overview']) }}"
                       class="tab-item {{ ($activeSection ?? 'overview') === 'overview' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('dashboard', ['section' => 'pets']) }}"
                       class="tab-item {{ ($activeSection ?? '') === 'pets' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3.25a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665 8.97 8.97 0 00.654 3.375z" />
                        </svg>
                        <span>Pets</span>
                    </a>
                    <a href="{{ route('chat.show') }}"
                       class="tab-item {{ ($activeSection ?? '') === 'chat' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                        <span>Chat</span>
                    </a>
                    <a href="{{ route('dashboard', ['section' => 'profile']) }}"
                       class="tab-item {{ ($activeSection ?? '') === 'profile' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span>Profile</span>
                    </a>
                </nav>
            @endif

            {{-- Native push enrollment (only inside NativePHP shell) --}}
            <script>
                (function () {
                    if (!window.AndroidPOST && !window.AndroidBridge) return;
                    if (sessionStorage.getItem('pushEnrolled') === '1') return;

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (!csrf) return;

                    fetch('{{ route('native.push.enroll') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    }).then(() => sessionStorage.setItem('pushEnrolled', '1')).catch(() => {});
                })();
            </script>
        @endauth
    </div>
</body>
</html>
