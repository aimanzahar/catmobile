@extends('layouts.app', ['title' => 'Payment', 'activeSection' => $activeSection])

@section('content')
    <div x-data="{ processing: false, channel: '' }" class="space-y-5 pb-10">
        <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider opacity-80">PurrfectPay</div>
                    <div class="text-base font-extrabold">Demo Gateway</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] opacity-80">Order</div>
                    <div class="text-sm font-bold">BK-{{ str_pad(substr($booking->id, -6), 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
            <p class="mt-3 text-[11px] opacity-90">All transactions on this page are simulated. No real money is charged.</p>
        </div>

        @include('bookings.partials.order-summary', ['booking' => $booking])

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('bookings.payment.process', $booking->id) }}" @submit="processing = true" class="space-y-5">
            @csrf

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Online Banking (FPX)</h2>
                <p class="mt-0.5 text-xs text-gray-500">Choose your bank</p>

                <div class="mt-3 grid gap-2">
                    @foreach ($channels['fpx'] ?? [] as $bank)
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-2.5 cursor-pointer hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" name="payment_channel" value="{{ $bank['code'] }}" x-model="channel" required class="h-4 w-4 accent-brand-600">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-base">🏦</div>
                            <span class="text-sm font-bold text-gray-900">{{ $bank['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">E-Wallets</h2>
                <p class="mt-0.5 text-xs text-gray-500">Quick &amp; cashless</p>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach ($channels['ewallet'] ?? [] as $wallet)
                        <label class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 px-3 py-3 cursor-pointer text-center hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                            <input type="radio" name="payment_channel" value="{{ $wallet['code'] }}" x-model="channel" required class="hidden">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-lg">📱</div>
                            <span class="text-xs font-bold text-gray-900">{{ $wallet['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            <div class="space-y-2">
                <button type="submit"
                        :disabled="!channel || processing"
                        class="w-full rounded-2xl bg-gradient-to-r from-brand-500 to-brand-600 text-white font-bold py-3.5 shadow-md disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 transition-transform">
                    <span x-show="!processing">Pay RM{{ number_format($booking->total_price, 2) }}</span>
                    <span x-show="processing" x-cloak class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        Processing...
                    </span>
                </button>
                <a href="{{ route('bookings.payment.cancel', $booking->id) }}"
                   class="block w-full text-center rounded-2xl bg-gray-100 text-gray-700 font-semibold py-3 hover:bg-gray-200">
                    Cancel
                </a>
            </div>
        </form>

        <p class="text-center text-[11px] text-gray-400">
            Powered by PurrfectPay · This is a demo gateway for prototyping
        </p>
    </div>
@endsection
