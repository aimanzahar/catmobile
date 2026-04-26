@extends('layouts.app', ['title' => 'Booking confirmed', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-5 pb-10">
        <div class="rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 p-6 text-white text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="mt-3 text-xl font-extrabold">Booking confirmed!</h1>
            <p class="mt-1 text-sm opacity-90">We can't wait to pamper your cat 🐱</p>
            <div class="mt-3 inline-block rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-wider">
                Order BK-{{ str_pad(substr($booking->id, -6), 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        @include('bookings.partials.order-summary', ['booking' => $booking])

        <div class="rounded-2xl bg-white p-4 border border-gray-100">
            <h3 class="text-sm font-bold text-gray-900">What happens next?</h3>
            <ol class="mt-3 space-y-2 text-xs text-gray-600">
                <li class="flex gap-2"><span class="font-bold text-brand-600">1.</span> We'll send you a reminder before your appointment.</li>
                @if ($booking->taxiRequest)
                    <li class="flex gap-2"><span class="font-bold text-brand-600">2.</span> Our pet taxi driver will pick up your cat at the scheduled time.</li>
                    <li class="flex gap-2"><span class="font-bold text-brand-600">3.</span> Sit back — we'll groom &amp; return your cat home.</li>
                @else
                    <li class="flex gap-2"><span class="font-bold text-brand-600">2.</span> Drop off your cat 5 minutes before your slot.</li>
                    <li class="flex gap-2"><span class="font-bold text-brand-600">3.</span> Pick up your freshly groomed feline when ready!</li>
                @endif
            </ol>
        </div>

        <div class="space-y-2">
            <a href="{{ route('dashboard') }}"
               class="block w-full text-center rounded-2xl bg-gradient-to-r from-brand-500 to-brand-600 text-white font-bold py-3.5 shadow-md">
                View on dashboard
            </a>
            <a href="{{ route('bookings.index') }}"
               class="block w-full text-center rounded-2xl bg-gray-100 text-gray-700 font-semibold py-3 hover:bg-gray-200">
                Book another
            </a>
        </div>
    </div>
@endsection
