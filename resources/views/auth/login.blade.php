@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="grid gap-8 lg:grid-cols-[0.95fr,1.05fr]">
            <section class="order-2 rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-brand-100 lg:order-1">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Welcome back</h1>
                <p class="mt-3 text-sm leading-6 text-gray-600">Log in to view your bookings, keep your pet profiles updated, and stay on top of your grooming schedule.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-gray-700">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-gray-700">Password</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-3 text-sm text-gray-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Keep me signed in on this device
                    </label>
                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-brand-500 to-accent-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">Login</button>
                </form>

                <p class="mt-6 text-sm text-gray-500">Need an account? <a href="{{ route('register') }}" class="font-semibold text-brand-700">Register here</a>.</p>
            </section>

            <section class="order-1 rounded-[2rem] bg-gradient-to-br from-brand-500 to-accent-500 p-8 text-white shadow-sm lg:order-2">
                <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.25em]">Dashboard access</span>
                <h2 class="mt-4 text-4xl font-extrabold tracking-tight">Everything your cat needs, in one place.</h2>
                <div class="mt-8 space-y-4 text-sm text-white/90">
                    <div class="rounded-3xl bg-white/10 p-5 backdrop-blur-sm">
                        <div class="text-xl">🗂️</div>
                        <p class="mt-2">See upcoming appointments and review your grooming history without hunting through messages.</p>
                    </div>
                    <div class="rounded-3xl bg-white/10 p-5 backdrop-blur-sm">
                        <div class="text-xl">🐈</div>
                        <p class="mt-2">Manage multiple pet profiles and keep special care notes ready for the grooming team.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
