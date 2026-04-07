@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="auth-screen">
        {{-- Brand --}}
        <div class="text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-500 to-accent-500 text-4xl shadow-lg shadow-brand-500/20">🐱</div>
            <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-gray-900">Welcome back</h1>
            <p class="mt-2 text-sm text-gray-500">Log in to manage your bookings & pets</p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       class="input-mobile" placeholder="you@example.com">
                @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                       class="input-mobile" placeholder="••••••••">
                @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2.5 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-brand-600 focus:ring-brand-500">
                Keep me signed in
            </label>
            <button type="submit" class="btn-primary-mobile mt-2">Login</button>
        </form>

        {{-- Footer link --}}
        <p class="mt-8 text-center text-sm text-gray-500">
            Don't have an account? <a href="{{ route('register') }}" class="font-semibold text-brand-700">Register</a>
        </p>
    </div>
@endsection
