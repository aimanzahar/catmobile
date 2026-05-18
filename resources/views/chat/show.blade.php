@extends('layouts.app', ['title' => 'Chat with the shop', 'activeSection' => $activeSection])

@section('content')
    @include('chat._thread', [
        'pollUrl' => $pollUrl,
        'postUrl' => $postUrl,
        'isAdmin' => false,
        'partnerName' => 'PurrfectCat Groom',
        'backUrl' => null,
    ])
@endsection
