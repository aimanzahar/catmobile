@extends('layouts.app', ['title' => 'Dashboard', 'activeSection' => $activeSection])

@section('content')
    <div x-data="{ tab: '{{ $activeSection }}', editingPet: null, showAddPet: false }" class="space-y-5">

        {{-- ── Greeting header ── --}}
        <div class="flex items-center gap-3.5">
            @if ($user->avatarUrl())
                <img src="{{ $user->avatarUrl('100x100') }}" alt="Your avatar" class="h-12 w-12 rounded-full object-cover ring-2 ring-brand-200">
            @else
                <div class="avatar-circle">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
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

            {{-- Book a grooming CTA --}}
            <a href="{{ route('bookings.index') }}"
               class="flex items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-brand-500 to-brand-600 p-4 text-white shadow-md active:scale-[0.99] transition-transform">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold">Book a grooming</div>
                        <div class="text-[11px] opacity-90">Schedule your cat's next pampering session</div>
                    </div>
                </div>
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

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
                                <a href="{{ route('bookings.show', $booking->id) }}" class="block -m-px p-px active:opacity-80 transition-opacity">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-gray-900">{{ $booking->service->name }}</h3>
                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $booking->pet->name }} · {{ $booking->timeSlot->date->format('d M Y') }} · {{ \Illuminate\Support\Str::of($booking->timeSlot->start_time)->limit(5, '') }}
                                            </p>
                                        </div>
                                        <div class="flex flex-shrink-0 items-center gap-1.5">
                                            <span class="rounded-full bg-brand-100 px-2.5 py-1 text-[0.625rem] font-bold uppercase text-brand-700">{{ str_replace('_', ' ', $booking->status) }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-gray-300">
                                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    @if ($booking->taxiRequest)
                                        <p class="mt-2 text-xs text-gray-400">🚕 Taxi: {{ ucfirst($booking->taxiRequest->status) }}</p>
                                    @endif
                                </a>
                                @if (in_array($booking->status, ['pending', 'confirmed'], true) || $booking->payment_status === 'unpaid')
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($booking->payment_status === 'unpaid' && $booking->status !== 'cancelled')
                                            <a href="{{ route('bookings.payment', $booking->id) }}"
                                               class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-3 py-1.5 text-[11px] font-bold text-brand-700 hover:bg-brand-100">
                                                💳 Pay now
                                            </a>
                                        @endif
                                        @if (in_array($booking->status, ['pending', 'confirmed'], true))
                                            <form method="POST" action="{{ route('dashboard.bookings.cancel', $booking->id) }}"
                                                  onsubmit="return confirm('Cancel this booking?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-[11px] font-bold text-red-600 hover:bg-red-100">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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
                            <a href="{{ route('bookings.show', $booking->id) }}"
                               class="flex items-center justify-between gap-3 rounded-xl bg-white p-3.5 border border-gray-100 active:opacity-80 transition-opacity">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $booking->service->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $booking->pet->name }} · {{ $booking->timeSlot->date->format('d M Y') }}</p>
                                </div>
                                <div class="flex flex-shrink-0 items-center gap-1.5">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[0.625rem] font-bold uppercase text-gray-500">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-gray-300">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </a>
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
                <form method="POST" action="{{ route('pets.store') }}" enctype="multipart/form-data"
                      x-data="{ preview: null }"
                      class="space-y-3 rounded-2xl bg-white p-4 border border-gray-100">
                    @csrf
                    <label data-native-picker class="relative flex items-center gap-3 cursor-pointer"
                           @native-picker-selected="preview = '/_native/local-file?path=' + encodeURIComponent($event.detail.path)">
                        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-2xl overflow-hidden">
                            <template x-if="preview"><img :src="preview" class="h-full w-full object-cover"></template>
                            <template x-if="!preview"><span>📷</span></template>
                        </div>
                        <div class="text-xs">
                            <div class="font-bold text-gray-900">Add cat photo</div>
                            <div class="text-gray-500">JPG/PNG up to 5 MB · optional</div>
                        </div>
                        <input name="image" type="file" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        <input type="hidden" name="image_native_path" data-native-path>
                    </label>
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
                                    @if ($pet->imageUrl())
                                        <img src="{{ $pet->imageUrl('100x100') }}" alt="{{ $pet->name }}" class="h-10 w-10 rounded-xl object-cover flex-shrink-0">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-lg flex-shrink-0">🐱</div>
                                    @endif
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
                                <form method="POST" action="{{ route('pets.update', $pet->id) }}" enctype="multipart/form-data"
                                      x-data="{ preview: '{{ $pet->imageUrl() }}' }"
                                      class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <label data-native-picker class="relative flex items-center gap-3 cursor-pointer"
                                           @native-picker-selected="preview = '/_native/local-file?path=' + encodeURIComponent($event.detail.path)">
                                        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-2xl overflow-hidden">
                                            <template x-if="preview"><img :src="preview" class="h-full w-full object-cover"></template>
                                            <template x-if="!preview"><span>🐱</span></template>
                                        </div>
                                        <div class="text-xs">
                                            <div class="font-bold text-gray-900">Change cat photo</div>
                                            <div class="text-gray-500">JPG/PNG up to 5 MB</div>
                                        </div>
                                        <input name="image" type="file" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : '{{ $pet->imageUrl() }}'">
                                        <input type="hidden" name="image_native_path" data-native-path>
                                    </label>
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

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                      x-data="{ preview: '{{ $user->avatarUrl() }}' }"
                      class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <label data-native-picker class="relative flex items-center gap-4 cursor-pointer"
                           @native-picker-selected="preview = '/_native/local-file?path=' + encodeURIComponent($event.detail.path)">
                        <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 text-xl font-extrabold text-brand-700 overflow-hidden ring-2 ring-brand-200">
                            <template x-if="preview"><img :src="preview" class="h-full w-full object-cover"></template>
                            <template x-if="!preview"><span>{{ strtoupper(substr($user->name, 0, 1)) }}</span></template>
                        </div>
                        <div class="text-xs">
                            <div class="font-bold text-gray-900">Profile photo</div>
                            <div class="text-gray-500">Tap to {{ $user->avatar ? 'change' : 'upload' }} · JPG/PNG up to 5 MB</div>
                        </div>
                        <input name="avatar" type="file" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : '{{ $user->avatarUrl() }}'">
                        <input type="hidden" name="avatar_native_path" data-native-path>
                    </label>
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
