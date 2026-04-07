@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="grid gap-8 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-brand-100">
                <span class="inline-flex rounded-full bg-brand-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.25em] text-brand-700">Create account</span>
                <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-gray-900">Start your cat's care hub.</h1>
                <p class="mt-4 max-w-xl text-base leading-7 text-gray-600">
                    Register once, then manage your pets, track bookings, and keep your grooming details in one calm, cozy dashboard.
                </p>
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl bg-warm-50 p-5">
                        <div class="text-2xl">📅</div>
                        <h2 class="mt-3 font-bold text-gray-900">Booking-ready</h2>
                        <p class="mt-2 text-sm text-gray-600">Save your details now and add your first pet right after signup.</p>
                    </div>
                    <div class="rounded-3xl bg-warm-50 p-5">
                        <div class="text-2xl">🐾</div>
                        <h2 class="mt-3 font-bold text-gray-900">Pet profiles</h2>
                        <p class="mt-2 text-sm text-gray-600">Track each cat's profile, grooming needs, and upcoming visits.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-brand-100">
                <h2 class="text-2xl font-bold text-gray-900">Register</h2>
                <p class="mt-2 text-sm text-gray-500">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-brand-700">Login here</a>.</p>

                <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
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
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-gray-700">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-brand-500 to-accent-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">Create account</button>
                </form>
            </section>
        </div>
    </div>
@endsection
