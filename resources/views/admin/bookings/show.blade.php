@extends('layouts.app', ['title' => 'Booking', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-5">
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-brand-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to bookings
            </a>
            <div class="mt-2 flex items-center gap-2">
                <h1 class="text-xl font-extrabold text-gray-900">{{ $booking->pet?->name ?? 'Pet' }}</h1>
                @include('admin.bookings._status_chip', ['status' => $booking->status])
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ $booking->service?->name ?? 'Service' }}</p>
        </div>

        <section class="rounded-2xl bg-white p-4 border border-gray-100 space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-gray-400">Customer</h2>
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold">
                    {{ strtoupper(substr($booking->userName ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-900 truncate">{{ $booking->userName ?? '—' }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ $booking->userEmail ?? '—' }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 border border-gray-100 space-y-2">
            <h2 class="text-xs font-bold uppercase tracking-wide text-gray-400">Pet</h2>
            <div class="flex items-center gap-3">
                @if ($booking->pet?->imageUrl())
                    <img src="{{ $booking->pet->imageUrl('200x200') }}" alt="{{ $booking->pet->name }}" class="h-14 w-14 rounded-xl object-cover">
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-brand-100 text-2xl">🐱</div>
                @endif
                <div class="min-w-0">
                    <div class="text-sm font-bold text-gray-900 truncate">{{ $booking->pet?->name }}</div>
                    <div class="text-xs text-gray-500 truncate">
                        {{ $booking->pet?->breed ?: 'Breed not set' }}
                        @if ($booking->pet?->age) · {{ $booking->pet->age }}y @endif
                        @if ($booking->pet?->weight) · {{ $booking->pet->weight }}kg @endif
                    </div>
                    @if ($booking->pet?->special_notes)
                        <div class="mt-1 text-xs text-gray-600">{{ $booking->pet->special_notes }}</div>
                    @endif
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 border border-gray-100 space-y-2">
            <h2 class="text-xs font-bold uppercase tracking-wide text-gray-400">Schedule</h2>
            <div class="text-sm text-gray-700">
                {{ $booking->timeSlot?->date?->format('l, F j, Y') ?? '—' }}
                @if ($booking->timeSlot?->start_time)
                    <span class="ml-1 font-bold">at {{ $booking->timeSlot->start_time }}</span>
                @endif
            </div>
            <div class="text-xs text-gray-500">
                Total RM{{ number_format($booking->total_price, 2) }} ·
                {{ ucfirst($booking->payment_method) }} ·
                {{ ucfirst($booking->payment_status) }}
            </div>
            @if ($booking->notes)
                <div class="mt-2 rounded-lg bg-warm-50 px-3 py-2 text-xs text-gray-600">
                    <span class="font-bold">Notes:</span> {{ $booking->notes }}
                </div>
            @endif
            @if ($booking->taxiRequest)
                <div class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <span class="font-bold">Pet Taxi:</span> {{ $booking->taxiRequest->pickup_address ?? '—' }}
                </div>
            @endif
        </section>

        <section class="rounded-2xl bg-white p-4 border border-gray-100">
            <h2 class="text-xs font-bold uppercase tracking-wide text-gray-400">Update status</h2>
            <p class="mt-1 text-xs text-gray-500">Customer is notified automatically on each change.</p>

            <div class="mt-3 grid grid-cols-2 gap-2">
                @php
                    $actions = [
                        'confirmed' => ['Confirm', 'bg-blue-500 hover:bg-blue-600'],
                        'in_progress' => ['Start grooming', 'bg-purple-500 hover:bg-purple-600'],
                        'completed' => ['Mark completed', 'bg-green-500 hover:bg-green-600'],
                        'cancelled' => ['Cancel', 'bg-red-500 hover:bg-red-600'],
                    ];
                @endphp
                @foreach ($actions as $value => [$label, $cls])
                    <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $value }}">
                        <button type="submit"
                                class="w-full rounded-xl px-3 py-3 text-sm font-bold text-white {{ $cls }} {{ $booking->status === $value ? 'opacity-50 cursor-not-allowed' : '' }}"
                                @disabled($booking->status === $value)>
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
