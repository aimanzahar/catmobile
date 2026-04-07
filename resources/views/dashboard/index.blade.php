@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-brand-100">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-brand-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.25em] text-brand-700">My Dashboard</span>
                    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-gray-900">Hello, {{ $user->name }}.</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">Manage your cat profiles, watch your booking timeline, and keep your account details tidy from one customer dashboard.</p>
                </div>
                <div class="grid grid-cols-3 gap-3 text-sm">
                    <div class="rounded-3xl bg-warm-50 px-4 py-4 text-center">
                        <div class="text-2xl font-extrabold text-brand-700">{{ $pets->count() }}</div>
                        <div class="mt-1 text-gray-500">Pets</div>
                    </div>
                    <div class="rounded-3xl bg-warm-50 px-4 py-4 text-center">
                        <div class="text-2xl font-extrabold text-brand-700">{{ $upcoming_bookings->count() }}</div>
                        <div class="mt-1 text-gray-500">Upcoming</div>
                    </div>
                    <div class="rounded-3xl bg-warm-50 px-4 py-4 text-center">
                        <div class="text-2xl font-extrabold text-brand-700">{{ $booking_history->count() }}</div>
                        <div class="mt-1 text-gray-500">History</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-wrap gap-2 text-sm font-semibold">
            <a href="{{ route('dashboard', ['section' => 'overview']) }}#overview" class="rounded-full px-4 py-2 {{ $activeSection === 'overview' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 ring-1 ring-brand-100' }}">Overview</a>
            <a href="{{ route('dashboard', ['section' => 'pets']) }}#pets" class="rounded-full px-4 py-2 {{ $activeSection === 'pets' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 ring-1 ring-brand-100' }}">Pets</a>
            <a href="{{ route('dashboard', ['section' => 'profile']) }}#profile" class="rounded-full px-4 py-2 {{ $activeSection === 'profile' ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 ring-1 ring-brand-100' }}">Profile</a>
        </div>

        <section id="overview" class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
            <div class="space-y-6">
                <article class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-brand-100">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Upcoming Bookings</h2>
                            <p class="mt-1 text-sm text-gray-500">Your next confirmed or pending grooming visits.</p>
                        </div>
                    </div>

                    @if ($upcoming_bookings->isEmpty())
                        <div class="mt-6 rounded-3xl border border-dashed border-brand-200 bg-warm-50 px-5 py-8 text-sm text-gray-600">No upcoming bookings yet</div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($upcoming_bookings as $booking)
                                <div class="rounded-3xl bg-warm-50 p-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $booking->service->name }}</h3>
                                            <p class="mt-1 text-sm text-gray-600">For {{ $booking->pet->name }} on {{ $booking->timeSlot->date->format('d M Y') }} at {{ \Illuminate\Support\Str::of($booking->timeSlot->start_time)->limit(5, '') }}</p>
                                        </div>
                                        <span class="inline-flex rounded-full bg-brand-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-700">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    </div>
                                    @if ($booking->taxiRequest)
                                        <p class="mt-3 text-sm text-gray-500">Taxi status: {{ ucfirst($booking->taxiRequest->status) }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>

                <article class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-brand-100">
                    <h2 class="text-2xl font-bold text-gray-900">Booking History</h2>
                    <p class="mt-1 text-sm text-gray-500">Completed or past appointments stay here for reference.</p>

                    @if ($booking_history->isEmpty())
                        <div class="mt-6 rounded-3xl border border-dashed border-brand-200 bg-warm-50 px-5 py-8 text-sm text-gray-600">No booking history yet</div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($booking_history as $booking)
                                <div class="rounded-3xl border border-gray-100 p-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $booking->service->name }}</h3>
                                            <p class="mt-1 text-sm text-gray-600">{{ $booking->pet->name }} · {{ $booking->timeSlot->date->format('d M Y') }}</p>
                                        </div>
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-gray-600">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            </div>

            <div class="space-y-6">
                <article id="pets" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-brand-100">
                    <h2 class="text-2xl font-bold text-gray-900">My Pets</h2>
                    <p class="mt-1 text-sm text-gray-500">Add and update the cats you bring in for grooming.</p>

                    <form method="POST" action="{{ route('pets.store') }}" class="mt-6 grid gap-4">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Pet name</label>
                                <input name="name" type="text" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Breed</label>
                                <input name="breed" type="text" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Age</label>
                                <input name="age" type="number" min="0" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Weight (kg)</label>
                                <input name="weight" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Special notes</label>
                            <textarea name="special_notes" rows="3" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"></textarea>
                        </div>
                        <button type="submit" class="rounded-2xl bg-gradient-to-r from-brand-500 to-accent-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">Add pet</button>
                    </form>

                    @if ($pets->isEmpty())
                        <div class="mt-6 rounded-3xl border border-dashed border-brand-200 bg-warm-50 px-5 py-8 text-sm text-gray-600">No pets added yet</div>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($pets as $pet)
                                <div class="rounded-3xl border border-gray-100 p-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $pet->name }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ $pet->breed ?: 'Breed not set' }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid gap-3">
                                        <form method="POST" action="{{ route('pets.update', $pet) }}" class="grid gap-3">
                                            @csrf
                                            @method('PATCH')
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <input name="name" type="text" value="{{ $pet->name }}" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                <input name="breed" type="text" value="{{ $pet->breed }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                <input name="age" type="number" min="0" value="{{ $pet->age }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                <input name="weight" type="number" min="0" step="0.01" value="{{ $pet->weight }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            </div>
                                            <textarea name="special_notes" rows="2" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ $pet->special_notes }}</textarea>
                                            <div class="flex flex-wrap gap-3">
                                                <button type="submit" class="rounded-2xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Save changes</button>
                                            </div>
                                        </form>

                                        <div class="flex flex-wrap gap-3">
                                            <form method="POST" action="{{ route('pets.destroy', $pet) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-2xl bg-red-50 px-4 py-2 text-sm font-semibold text-red-600">Delete pet</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>

                <article id="profile" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-brand-100">
                    <h2 class="text-2xl font-bold text-gray-900">Profile Settings</h2>
                    <p class="mt-1 text-sm text-gray-500">Keep your account details up to date.</p>

                    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 grid gap-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Name</label>
                            <input name="name" type="text" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Email</label>
                            <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="rounded-2xl bg-gray-900 px-4 py-3 text-sm font-bold text-white">Update profile</button>
                    </form>
                </article>
            </div>
        </section>
    </div>
@endsection
