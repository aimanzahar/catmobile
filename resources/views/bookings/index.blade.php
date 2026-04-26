@extends('layouts.app', ['title' => 'Book a grooming', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Book a grooming</h1>
            <p class="mt-1 text-sm text-gray-500">Pick a service to get started</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            @foreach ($services as $service)
                <a href="{{ route('bookings.create', $service->slug) }}"
                   class="group relative rounded-2xl bg-white p-4 border border-gray-100 hover:border-brand-300 hover:shadow-md transition-all">
                    @if ($service->slug === 'full-grooming')
                        <div class="absolute -top-2 right-4">
                            <span class="inline-flex items-center px-2 py-0.5 bg-gradient-to-r from-brand-500 to-accent-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-full shadow-sm">
                                Most Popular
                            </span>
                        </div>
                    @endif
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-brand-100 text-xl">
                                @switch($service->icon)
                                    @case('scissors') ✂️ @break
                                    @case('sparkles') ✨ @break
                                    @case('heart') 💖 @break
                                    @case('truck') 🚐 @break
                                    @default 🐱
                                @endswitch
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-gray-900">{{ $service->name }}</h3>
                                <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">{{ $service->description }}</p>
                                <p class="mt-1 text-[11px] text-gray-400">⏱ {{ $service->duration_minutes }} min</p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-lg font-extrabold text-brand-600">RM{{ number_format($service->price, 0) }}</div>
                            <div class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-brand-600">
                                Book
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
