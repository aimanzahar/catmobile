@extends('layouts.app', ['title' => 'Dashboard', 'activeSection' => $activeSection])

@section('content')
    <div x-data="{ tab: '{{ $activeSection }}', editingPet: null, showAddPet: false }" class="space-y-5">

        {{-- ── Greeting header ── --}}
        <div class="flex items-center gap-3.5">
            <div class="avatar-circle">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div>
                <h1 class="text-lg font-extrabold text-gray-900">Hi, {{ Str::before($user->name, ' ') }}!</h1>
                <p class="text-xs text-gray-500">Manage your cats & bookings</p>
            </div>
        </div>

        {{-- ── Stat pills (horizontal scroll) ── --}}
        <div class="stat-scroll">
            <div class="stat-pill">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-100 text-base">🐱</div>
                <div>
                    <div class="text-lg font-extrabold text-gray-900">{{ $pets->count() }}</div>
                    <div class="text-[0.6875rem] text-gray-500">Pets</div>
                </div>
            </div>
            <div class="stat-pill">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-100 text-base">📅</div>
                <div>
                    <div class="text-lg font-extrabold text-gray-900">{{ $upcoming_bookings->count() }}</div>
                    <div class="text-[0.6875rem] text-gray-500">Upcoming</div>
                </div>
            </div>
            <div class="stat-pill">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-base">✅</div>
                <div>
                    <div class="text-lg font-extrabold text-gray-900">{{ $booking_history->count() }}</div>
                    <div class="text-[0.6875rem] text-gray-500">History</div>
                </div>
            </div>
        </div>

        {{-- ── Segmented control tabs ── --}}
        <div class="segmented-control">
            <button :class="tab === 'overview' && 'active'" @click="tab = 'overview'">Overview</button>
            <button :class="tab === 'pets' && 'active'" @click="tab = 'pets'">Pets</button>
            <button :class="tab === 'profile' && 'active'" @click="tab = 'profile'">Profile</button>
        </div>

        {{-- ════════════════════════════════════════════
             TAB: Overview
             ════════════════════════════════════════════ --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-5">

            {{-- Upcoming bookings --}}
            <section>
                <h2 class="text-base font-bold text-gray-900">Upcoming Bookings</h2>
                <p class="mt-0.5 text-xs text-gray-500">Your next grooming visits</p>

                @if ($upcoming_bookings->isEmpty())
                    <div class="mt-3 rounded-xl border border-dashed border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-400">
                        No upcoming bookings yet
                    </div>
                @else
                    <div class="mt-3 space-y-3">
                        @foreach ($upcoming_bookings as $booking)
                            <div class="card-accent-left">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-gray-900">{{ $booking->service->name }}</h3>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            {{ $booking->pet->name }} · {{ $booking->timeSlot->date->format('d M Y') }} · {{ \Illuminate\Support\Str::of($booking->timeSlot->start_time)->limit(5, '') }}
                                        </p>
                                    </div>
                                    <span class="flex-shrink-0 rounded-full bg-brand-100 px-2.5 py-1 text-[0.625rem] font-bold uppercase text-brand-700">{{ str_replace('_', ' ', $booking->status) }}</span>
                                </div>
                                @if ($booking->taxiRequest)
                                    <p class="mt-2 text-xs text-gray-400">🚕 Taxi: {{ ucfirst($booking->taxiRequest->status) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Booking history --}}
            <section>
                <h2 class="text-base font-bold text-gray-900">History</h2>
                <p class="mt-0.5 text-xs text-gray-500">Past appointments</p>

                @if ($booking_history->isEmpty())
                    <div class="mt-3 rounded-xl border border-dashed border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-400">
                        No booking history yet
                    </div>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach ($booking_history as $booking)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-white p-3.5 border border-gray-100">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $booking->service->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $booking->pet->name }} · {{ $booking->timeSlot->date->format('d M Y') }}</p>
                                </div>
                                <span class="flex-shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-[0.625rem] font-bold uppercase text-gray-500">{{ str_replace('_', ' ', $booking->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- ════════════════════════════════════════════
             TAB: Pets
             ════════════════════════════════════════════ --}}
        <div x-show="tab === 'pets'" x-cloak class="space-y-5">

            {{-- Header + add button --}}
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">My Pets</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Manage your cat profiles</p>
                </div>
                <button @click="showAddPet = !showAddPet" class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600 text-white shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" :class="showAddPet && 'rotate-45'" style="transition: transform 0.2s">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                </button>
            </div>

            {{-- Add pet form (collapsible) --}}
            <div x-show="showAddPet" x-collapse x-cloak>
                <form method="POST" action="{{ route('pets.store') }}" class="space-y-3 rounded-2xl bg-white p-4 border border-gray-100">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <input name="name" type="text" required class="input-mobile" placeholder="Pet name">
                        <input name="breed" type="text" class="input-mobile" placeholder="Breed">
                        <input name="age" type="number" min="0" class="input-mobile" placeholder="Age">
                        <input name="weight" type="number" min="0" step="0.01" class="input-mobile" placeholder="Weight (kg)">
                    </div>
                    <textarea name="special_notes" rows="2" class="input-mobile" placeholder="Special notes..."></textarea>
                    <button type="submit" class="btn-primary-mobile">Add pet</button>
                </form>
            </div>

            {{-- Pet list --}}
            @if ($pets->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-8 text-center text-sm text-gray-400">
                    No pets added yet — tap + to add one
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($pets as $pet)
                        <div class="rounded-2xl bg-white border border-gray-100 overflow-hidden">
                            {{-- Pet header (tap to expand) --}}
                            <button @click="editingPet === '{{ $pet->id }}' ? editingPet = null : editingPet = '{{ $pet->id }}'"
                                    class="flex w-full items-center justify-between gap-3 p-4 text-left">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-lg flex-shrink-0">🐱</div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $pet->name }}</h3>
                                        <p class="text-xs text-gray-500 truncate">{{ $pet->breed ?: 'Breed not set' }} · {{ $pet->age ? $pet->age . 'y' : '' }} {{ $pet->weight ? '· ' . $pet->weight . 'kg' : '' }}</p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                     class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200"
                                     :class="editingPet === '{{ $pet->id }}' && 'rotate-180'">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            {{-- Expandable edit form --}}
                            <div x-show="editingPet === '{{ $pet->id }}'" x-collapse x-cloak class="border-t border-gray-100 px-4 pb-4 pt-3">
                                <form method="POST" action="{{ route('pets.update', $pet->id) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-2 gap-3">
                                        <input name="name" type="text" value="{{ $pet->name }}" required class="input-mobile" placeholder="Name">
                                        <input name="breed" type="text" value="{{ $pet->breed }}" class="input-mobile" placeholder="Breed">
                                        <input name="age" type="number" min="0" value="{{ $pet->age }}" class="input-mobile" placeholder="Age">
                                        <input name="weight" type="number" min="0" step="0.01" value="{{ $pet->weight }}" class="input-mobile" placeholder="Weight">
                                    </div>
                                    <textarea name="special_notes" rows="2" class="input-mobile" placeholder="Special notes...">{{ $pet->special_notes }}</textarea>
                                    <div class="flex gap-3">
                                        <button type="submit" class="flex-1 rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('pets.destroy', $pet->id) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl bg-red-50 py-2.5 text-sm font-semibold text-red-600">Delete pet</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════
             TAB: Profile
             ════════════════════════════════════════════ --}}
        <div x-show="tab === 'profile'" x-cloak class="space-y-5">

            {{-- Profile form --}}
            <section class="rounded-2xl bg-white p-5 border border-gray-100">
                <h2 class="text-base font-bold text-gray-900">Profile Settings</h2>
                <p class="mt-0.5 text-xs text-gray-500">Update your account details</p>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Name</label>
                        <input name="name" type="text" value="{{ old('name', $user->name) }}" required class="input-mobile">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Email</label>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="input-mobile">
                    </div>
                    <button type="submit" class="btn-primary-mobile">Update profile</button>
                </form>
            </section>

            {{-- Logout --}}
            <section class="rounded-2xl bg-white p-5 border border-gray-100">
                <h2 class="text-base font-bold text-gray-900">Account</h2>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-red-50 py-3 text-sm font-bold text-red-600">Logout</button>
                </form>
            </section>
        </div>
    </div>
@endsection
