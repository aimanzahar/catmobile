@extends('layouts.app', ['title' => 'Booking details', 'activeSection' => $activeSection])

@php
    $statusColors = [
        'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700'],
        'confirmed' => ['bg' => 'bg-brand-100', 'text' => 'text-brand-700'],
        'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
        'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-500'],
    ];
    $statusStyle = $statusColors[$booking->status] ?? $statusColors['pending'];

    $paymentColors = [
        'paid' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
        'unpaid' => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
        'refunded' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-500'],
    ];
    $paymentStyle = $paymentColors[$booking->payment_status] ?? $paymentColors['unpaid'];

    $orderRef = 'BK-' . str_pad(substr($booking->id, -6), 6, '0', STR_PAD_LEFT);
    $isCancellable = in_array($booking->status, ['pending', 'confirmed'], true);
    $isPayable = $booking->payment_status === 'unpaid' && $booking->status !== 'cancelled';
@endphp

@section('content')
    <div class="space-y-5 pb-10">
        {{-- Back link --}}
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
            Back
        </a>

        {{-- Header --}}
        <div class="rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 p-5 text-white">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-wider opacity-80">{{ $orderRef }}</p>
                    <h1 class="mt-1 text-xl font-extrabold truncate">{{ $booking->service?->name ?? 'Booking' }}</h1>
                    @if ($booking->timeSlot && $booking->timeSlot->date)
                        <p class="mt-0.5 text-sm opacity-90">
                            {{ $booking->timeSlot->date->format('l, d M Y') }}
                            · {{ \Illuminate\Support\Str::of($booking->timeSlot->start_time)->limit(5, '') }}
                        </p>
                    @endif
                </div>
                <span class="flex-shrink-0 rounded-full bg-white/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider">
                    {{ str_replace('_', ' ', $booking->status) }}
                </span>
            </div>
        </div>

        {{-- Service --}}
        @if ($booking->service)
            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Service</h2>
                <div class="mt-2 flex items-start gap-3">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-brand-100 text-xl">
                        {{ $booking->service->icon ?: '✂️' }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">{{ $booking->service->name }}</h3>
                        @if ($booking->service->description)
                            <p class="mt-0.5 text-xs text-gray-500">{{ $booking->service->description }}</p>
                        @endif
                        @if ($booking->service->duration_minutes > 0)
                            <p class="mt-1 text-[11px] text-gray-400">⏱ {{ $booking->service->duration_minutes }} min</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- Pet --}}
        @if ($booking->pet)
            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">For your pet</h2>
                <div class="mt-2 flex items-center gap-3">
                    @if ($booking->pet->imageUrl())
                        <img src="{{ $booking->pet->imageUrl('100x100') }}" alt="{{ $booking->pet->name }}" class="h-11 w-11 rounded-xl object-cover flex-shrink-0">
                    @else
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-brand-100 text-lg">🐱</div>
                    @endif
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $booking->pet->name }}</h3>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $booking->pet->breed ?: 'Breed not set' }}
                            {{ $booking->pet->age ? '· ' . $booking->pet->age . 'y' : '' }}
                            {{ $booking->pet->weight ? '· ' . $booking->pet->weight . 'kg' : '' }}
                        </p>
                    </div>
                </div>
            </section>
        @endif

        {{-- Schedule --}}
        @if ($booking->timeSlot && $booking->timeSlot->date)
            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Schedule</h2>
                <dl class="mt-2 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Date</dt>
                        <dd class="font-semibold text-gray-900">{{ $booking->timeSlot->date->format('d M Y') }}</dd>
                    </div>
                    @if ($booking->timeSlot->start_time)
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">Start</dt>
                            <dd class="font-semibold text-gray-900">{{ \Illuminate\Support\Str::of($booking->timeSlot->start_time)->limit(5, '') }}</dd>
                        </div>
                    @endif
                    @if ($booking->timeSlot->end_time)
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500">End</dt>
                            <dd class="font-semibold text-gray-900">{{ \Illuminate\Support\Str::of($booking->timeSlot->end_time)->limit(5, '') }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        @endif

        {{-- Taxi --}}
        @if ($booking->taxiRequest)
            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Pet Taxi</h2>
                <div class="mt-2 space-y-2 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-gray-500">Pickup</span>
                        <span class="text-right font-semibold text-gray-900">{{ $booking->taxiRequest->pickup_address }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                            {{ $booking->taxiRequest->status }}
                        </span>
                    </div>
                    @if ($booking->taxiRequest->scheduled_at)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Scheduled</span>
                            <span class="font-semibold text-gray-900">{{ $booking->taxiRequest->scheduled_at->format('d M Y · H:i') }}</span>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Notes --}}
        @if ($booking->notes)
            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Notes</h2>
                <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $booking->notes }}</p>
            </section>
        @endif

        {{-- Payment --}}
        <section class="rounded-2xl bg-white p-4 border border-gray-100">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Payment</h2>
            <dl class="mt-2 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $booking->payment_method)) }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="rounded-full {{ $paymentStyle['bg'] }} px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $paymentStyle['text'] }}">
                            {{ $booking->payment_status }}
                        </span>
                    </dd>
                </div>
            </dl>
            <div class="mt-4 border-t border-dashed border-gray-200 pt-3 flex items-center justify-between">
                <span class="text-sm text-gray-500">Total</span>
                <span class="text-xl font-extrabold text-brand-600">RM{{ number_format($booking->total_price, 2) }}</span>
            </div>
        </section>

        {{-- Actions --}}
        @if ($isPayable || $isCancellable)
            <div class="space-y-2">
                @if ($isPayable)
                    <a href="{{ route('bookings.payment', $booking->id) }}"
                       class="block w-full text-center rounded-2xl bg-gradient-to-r from-brand-500 to-brand-600 text-white font-bold py-3.5 shadow-md">
                        💳 Pay now
                    </a>
                @endif
                @if ($isCancellable)
                    <form method="POST" action="{{ route('dashboard.bookings.cancel', $booking->id) }}"
                          onsubmit="return confirm('Cancel this booking?');">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl bg-red-50 py-3 text-sm font-bold text-red-600 hover:bg-red-100">
                            Cancel booking
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
@endsection
