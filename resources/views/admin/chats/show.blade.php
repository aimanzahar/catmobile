@extends('layouts.app', ['title' => $chat->userName ?? 'Chat', 'activeSection' => $activeSection])

@section('content')
    @include('chat._thread', [
        'pollUrl' => $pollUrl,
        'postUrl' => $postUrl,
        'isAdmin' => true,
        'partnerName' => $chat->userName ?? $chat->userEmail ?? 'Customer',
        'backUrl' => route('admin.chats.index'),
    ])
@endsection
