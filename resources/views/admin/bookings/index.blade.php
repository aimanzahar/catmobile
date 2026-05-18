@extends('layouts.app', ['title' => 'Bookings', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Bookings</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $bookings->count() }} {{ $statusFilter ? ucfirst($statusFilter) : 'all' }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @php
                $filters = [
                    '' => 'All',
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'in_progress' => 'In progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ];
            @endphp
            @foreach ($filters as $value => $label)
                <a href="{{ route('admin.bookings.index', $value === '' ? [] : ['status' => $value]) }}"
                   class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusFilter === $value ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 bg-white text-gray-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($bookings->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-400">
                No bookings found
            </div>
        @else
            <div class="space-y-3">
                @foreach ($bookings as $b)
                    <div class="rounded-2xl bg-white border border-gray-100 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-sm font-bold text-gray-900 truncate">{{ $b->pet?->name ?? 'Pet' }}</h2>
                                    @include('admin.bookings._status_chip', ['status' => $b->status])
                                </div>
                                <div class="mt-1 text-xs text-gray-600">
                                    {{ $b->userName ?? 'Customer' }}
                                    @if ($b->userEmail)
                                        <span class="text-gray-400">· {{ $b->userEmail }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $b->service?->name ?? 'Service' }}
                                    @if ($b->timeSlot?->date)
                                        · {{ $b->timeSlot->date->format('D, M j') }}
                                    @endif
                                    @if ($b->timeSlot?->start_time)
                                        at {{ $b->timeSlot->start_time }}
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('admin.bookings.show', $b->id) }}"
                               class="rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-600">
                                Manage
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
