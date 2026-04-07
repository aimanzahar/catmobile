@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="auth-screen">
        {{-- Brand --}}
        <div class="text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-brand-500 to-accent-500 text-4xl shadow-lg shadow-brand-500/20">🐾</div>
            <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-gray-900">Create account</h1>
            <p class="mt-2 text-sm text-gray-500">Start managing your cat's grooming care</p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="name" class="mb-1.5 block text-sm font-semibold text-gray-700">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="input-mobile" placeholder="Your name">
                @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
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
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-gray-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="input-mobile" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary-mobile mt-2">Create account</button>
        </form>

        {{-- Footer link --}}
        <p class="mt-8 text-center text-sm text-gray-500">
            Already have an account? <a href="{{ route('login') }}" class="font-semibold text-brand-700">Login</a>
        </p>
    </div>
@endsection
