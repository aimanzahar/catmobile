@extends('layouts.app', ['title' => 'Notifications', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-gray-900">Notifications</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $notifications->count() }} total</p>
            </div>
            @php $hasUnread = $notifications->contains(fn ($n) => ! $n->isRead()); @endphp
            @if ($hasUnread)
                <button x-data="{ marking: false }"
                        @click="marking = true; fetch('{{ route('notifications.readAll') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }).then(() => location.reload())"
                        :disabled="marking"
                        class="rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600">
                    Mark all read
                </button>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-400">
                You're all caught up
            </div>
        @else
            <div class="space-y-2">
                @foreach ($notifications as $n)
                    @php
                        $iconBg = match ($n->type) {
                            'booking_confirmed' => 'bg-blue-100 text-blue-600',
                            'booking_in_progress' => 'bg-purple-100 text-purple-600',
                            'booking_completed' => 'bg-green-100 text-green-600',
                            'booking_cancelled' => 'bg-gray-100 text-gray-600',
                            'chat_message' => 'bg-brand-100 text-brand-600',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $icon = match ($n->type) {
                            'booking_completed' => '🎉',
                            'booking_in_progress' => '✂️',
                            'chat_message' => '💬',
                            'booking_cancelled' => '✕',
                            default => '🔔',
                        };
                    @endphp
                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-start gap-3 rounded-2xl border p-4 text-left {{ $n->isRead() ? 'border-gray-100 bg-white opacity-60' : 'border-brand-100 bg-brand-50' }}">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-base {{ $iconBg }}">
                                {{ $icon }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ $n->title }}</h3>
                                    @if (! $n->isRead())
                                        <span class="h-2 w-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                    @endif
                                </div>
                                @if ($n->body)
                                    <p class="mt-0.5 text-xs text-gray-600 line-clamp-2">{{ $n->body }}</p>
                                @endif
                                @if ($n->created)
                                    <p class="mt-1 text-[11px] text-gray-400">{{ $n->created->diffForHumans() }}</p>
                                @endif
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        @endif
    </div>
@endsection
