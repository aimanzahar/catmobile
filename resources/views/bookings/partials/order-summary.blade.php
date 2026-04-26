<div class="rounded-2xl bg-white p-4 border border-gray-100">
    <h3 class="text-sm font-bold text-gray-900">Order summary</h3>
    <dl class="mt-3 space-y-2 text-sm">
        <div class="flex items-center justify-between">
            <dt class="text-gray-500">Service</dt>
            <dd class="font-semibold text-gray-900">{{ $booking->service?->name ?? '—' }}</dd>
        </div>
        <div class="flex items-center justify-between">
            <dt class="text-gray-500">Pet</dt>
            <dd class="font-semibold text-gray-900">{{ $booking->pet?->name ?? '—' }}</dd>
        </div>
        @if ($booking->timeSlot && $booking->timeSlot->date)
            <div class="flex items-center justify-between">
                <dt class="text-gray-500">Date &amp; time</dt>
                <dd class="font-semibold text-gray-900">
                    {{ $booking->timeSlot->date->format('d M Y') }} · {{ $booking->timeSlot->start_time }}
                </dd>
            </div>
        @endif
        @if ($booking->taxiRequest)
            <div class="flex items-center justify-between">
                <dt class="text-gray-500">Pet Taxi</dt>
                <dd class="font-semibold text-gray-900 text-right max-w-[60%] truncate">
                    {{ $booking->taxiRequest->pickup_address }}
                </dd>
            </div>
        @endif
    </dl>
    <div class="mt-4 border-t border-dashed border-gray-200 pt-3 flex items-center justify-between">
        <span class="text-sm text-gray-500">Total</span>
        <span class="text-xl font-extrabold text-brand-600">RM{{ number_format($booking->total_price, 2) }}</span>
    </div>
</div>
