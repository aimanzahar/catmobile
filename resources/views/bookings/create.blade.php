@extends('layouts.app', ['title' => 'Book ' . $service->name, 'activeSection' => $activeSection])

@section('content')
    <div x-data="bookingForm()" class="space-y-5">
        <div>
            <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-brand-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to services
            </a>
            <h1 class="mt-2 text-xl font-extrabold text-gray-900">Book {{ $service->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Fill in the details below</p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('bookings.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="service_slug" value="{{ $service->slug }}">

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100 text-xl">
                        @switch($service->icon)
                            @case('scissors') ✂️ @break
                            @case('sparkles') ✨ @break
                            @case('heart') 💖 @break
                            @case('truck') 🚐 @break
                            @default 🐱
                        @endswitch
                    </div>
                    <div class="flex-1">
                        <h2 class="text-sm font-bold text-gray-900">{{ $service->name }}</h2>
                        <p class="text-xs text-gray-500">{{ $service->duration_minutes }} min · RM{{ number_format($service->price, 2) }}</p>
                    </div>
                </div>
                @if ($service->description)
                    <p class="mt-3 text-xs text-gray-500 leading-relaxed">{{ $service->description }}</p>
                @endif
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Pick your cat</h2>
                <p class="mt-0.5 text-xs text-gray-500">Choose which cat needs grooming</p>

                @if ($pets->isEmpty())
                    <div class="mt-4 rounded-xl bg-brand-50 border border-brand-100 px-4 py-3 text-xs text-brand-700">
                        You haven't added any cats yet. Add one below — we'll save it to your profile.
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <input name="new_pet_name" type="text" required value="{{ old('new_pet_name') }}" class="input-mobile" placeholder="Cat's name">
                        <input name="new_pet_breed" type="text" value="{{ old('new_pet_breed') }}" class="input-mobile" placeholder="Breed (optional)">
                        <input name="new_pet_age" type="number" min="0" max="30" value="{{ old('new_pet_age') }}" class="input-mobile" placeholder="Age (years)">
                    </div>
                    <textarea name="new_pet_notes" rows="2" class="input-mobile mt-3" placeholder="Special notes (allergies, behavior...)">{{ old('new_pet_notes') }}</textarea>
                @else
                    <div class="mt-3 grid gap-2">
                        @foreach ($pets as $pet)
                            <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-3 py-2.5 cursor-pointer hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                <input type="radio" name="pet_id" value="{{ $pet->id }}" required @checked(old('pet_id') === $pet->id || $loop->first) class="h-4 w-4 accent-brand-600">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-100 text-base">🐱</div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">{{ $pet->name }}</div>
                                    <div class="text-[11px] text-gray-500 truncate">{{ $pet->breed ?: 'Breed not set' }}{{ $pet->age ? ' · '.$pet->age.'y' : '' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Pick a date</h2>
                <p class="mt-0.5 text-xs text-gray-500">Available up to {{ config('booking.max_advance_days', 30) }} days ahead</p>

                <input type="date" name="booking_date" required
                       min="{{ $minDate }}" max="{{ $maxDate }}"
                       x-model="date" @change="loadSlots()"
                       value="{{ old('booking_date') }}"
                       class="input-mobile mt-3">
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Pick a time</h2>
                <p class="mt-0.5 text-xs text-gray-500" x-show="!date">Select a date first</p>
                <p class="mt-0.5 text-xs text-gray-500" x-show="date && loading">Loading available times...</p>
                <p class="mt-0.5 text-xs text-red-500" x-show="date && !loading && slots.length === 0" x-cloak>No slots available for this date</p>

                <div class="mt-3 grid grid-cols-3 gap-2" x-show="date && !loading && slots.length > 0" x-cloak>
                    <template x-for="slot in slots" :key="slot">
                        <label class="flex items-center justify-center rounded-xl border border-gray-200 px-2 py-3 cursor-pointer text-sm font-semibold text-gray-700 has-[:checked]:bg-brand-500 has-[:checked]:text-white has-[:checked]:border-brand-500">
                            <input type="radio" name="start_time" :value="slot" x-model="startTime" required class="hidden">
                            <span x-text="slot"></span>
                        </label>
                    </template>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="taxi_enabled" value="1" x-model="taxi" class="mt-1 h-4 w-4 accent-brand-600">
                    <div class="flex-1">
                        <div class="text-sm font-bold text-gray-900">Add Pet Taxi pickup</div>
                        <div class="text-xs text-gray-500">Door-to-door pickup and drop-off — RM{{ number_format($taxiFee, 2) }}</div>
                    </div>
                </label>
                <textarea name="pickup_address" rows="2" x-show="taxi" x-cloak
                          class="input-mobile mt-3"
                          placeholder="Your pickup address...">{{ old('pickup_address') }}</textarea>
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <label class="text-sm font-bold text-gray-900">Notes (optional)</label>
                <textarea name="notes" rows="2" class="input-mobile mt-2" placeholder="Anything we should know?">{{ old('notes') }}</textarea>
            </section>

            <section class="rounded-2xl bg-white p-4 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500">Total</div>
                        <div class="text-2xl font-extrabold text-brand-600">
                            RM<span x-text="total.toFixed(2)">{{ number_format($service->price, 2) }}</span>
                        </div>
                    </div>
                    <div class="text-right text-[11px] text-gray-400">
                        Service RM{{ number_format($service->price, 2) }}<br>
                        <span x-show="taxi" x-cloak>+ Taxi RM{{ number_format($taxiFee, 2) }}</span>
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full rounded-2xl bg-gradient-to-r from-brand-500 to-brand-600 text-white font-bold py-3.5 shadow-md active:scale-[0.98] transition-transform">
                    Continue to Payment →
                </button>
            </section>
        </form>
    </div>

    <script>
        function bookingForm() {
            return {
                date: @json(old('booking_date', '')),
                slots: [],
                startTime: @json(old('start_time', '')),
                loading: false,
                taxi: {{ old('taxi_enabled') ? 'true' : 'false' }},
                servicePrice: {{ $service->price }},
                taxiFee: {{ $taxiFee }},
                get total() {
                    return this.servicePrice + (this.taxi ? this.taxiFee : 0);
                },
                init() {
                    if (this.date) this.loadSlots();
                },
                async loadSlots() {
                    if (!this.date) { this.slots = []; return; }
                    this.loading = true;
                    try {
                        const res = await fetch(`/api/time-slots?date=${this.date}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        if (!res.ok) throw new Error('failed');
                        const data = await res.json();
                        this.slots = data.slots || [];
                        if (this.startTime && !this.slots.includes(this.startTime)) {
                            this.startTime = '';
                        }
                    } catch (e) {
                        this.slots = [];
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
@endsection
