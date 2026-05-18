@extends('layouts.app', ['title' => 'Admin Dashboard', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $totalBookings }} bookings in the system</p>
        </div>

        <section class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-white border border-amber-100 p-4">
                <div class="text-xs font-semibold text-amber-600">Pending</div>
                <div class="mt-1 text-2xl font-extrabold text-amber-700">{{ $counts['pending'] }}</div>
            </div>
            <div class="rounded-2xl bg-white border border-blue-100 p-4">
                <div class="text-xs font-semibold text-blue-600">Confirmed</div>
                <div class="mt-1 text-2xl font-extrabold text-blue-700">{{ $counts['confirmed'] }}</div>
            </div>
            <div class="rounded-2xl bg-white border border-purple-100 p-4">
                <div class="text-xs font-semibold text-purple-600">In progress</div>
                <div class="mt-1 text-2xl font-extrabold text-purple-700">{{ $counts['in_progress'] }}</div>
            </div>
            <div class="rounded-2xl bg-white border border-green-100 p-4">
                <div class="text-xs font-semibold text-green-600">Completed</div>
                <div class="mt-1 text-2xl font-extrabold text-green-700">{{ $counts['completed'] }}</div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-900">Today's bookings</h2>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs font-semibold text-brand-600">View all →</a>
            </div>
            @if ($todayBookings->isEmpty())
                <p class="mt-3 rounded-xl border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400">
                    No bookings scheduled for today
                </p>
            @else
                <div class="mt-3 space-y-2">
                    @foreach ($todayBookings as $b)
                        <a href="{{ route('admin.bookings.show', $b->id) }}"
                           class="block rounded-xl border border-gray-100 p-3 hover:border-brand-300">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-bold text-gray-900 truncate">
                                        {{ $b->timeSlot?->start_time ?? '—' }} · {{ $b->pet?->name ?? 'Pet' }}
                                    </div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $b->userName ?? 'Customer' }} · {{ $b->service?->name ?? 'Service' }}
                                    </div>
                                </div>
                                @include('admin.bookings._status_chip', ['status' => $b->status])
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
