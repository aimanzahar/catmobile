@extends('layouts.app', ['title' => 'Chats', 'activeSection' => $activeSection])

@section('content')
    <div class="space-y-4">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Customer chats</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $chats->count() }} threads</p>
        </div>

        @if ($chats->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-400">
                No customer chats yet
            </div>
        @else
            <div class="space-y-2">
                @foreach ($chats as $chat)
                    <a href="{{ route('admin.chats.show', $chat->id) }}"
                       class="flex items-center gap-3 rounded-2xl bg-white border border-gray-100 p-4 hover:border-brand-300">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold">
                            {{ strtoupper(substr($chat->userName ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-bold text-gray-900 truncate">{{ $chat->userName ?? $chat->userEmail ?? 'Customer' }}</div>
                                @if ($chat->lastMessageAt)
                                    <div class="text-[11px] text-gray-400 flex-shrink-0">{{ $chat->lastMessageAt->diffForHumans() }}</div>
                                @endif
                            </div>
                            <div class="mt-0.5 text-xs text-gray-500 truncate">
                                {{ $chat->lastMessagePreview ?? 'No messages yet' }}
                            </div>
                        </div>
                        @if ($chat->unreadForAdmin > 0)
                            <span class="flex-shrink-0 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white">
                                {{ $chat->unreadForAdmin > 99 ? '99+' : $chat->unreadForAdmin }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
