<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Chat\MarkChatAsRead;
use App\Actions\Chat\SendMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StoreMessageRequest;
use App\Models\Chat;
use App\Models\Message;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatController extends Controller
{
    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly MarkChatAsRead $markRead,
    ) {}

    public function index(): View
    {
        $superToken = $this->client->superuserToken();

        $resp = $this->client->listRecords('cg_chats', $superToken, [
            'sort' => '-last_message_at',
            'expand' => 'user',
            'perPage' => 100,
        ]);

        $chats = collect($resp['items'] ?? [])
            ->map(fn (array $r) => Chat::fromRecord($r));

        return view('admin.chats.index', [
            'chats' => $chats,
            'activeSection' => 'admin-chats',
        ]);
    }

    public function show(string $chat): View
    {
        $superToken = $this->client->superuserToken();

        try {
            $record = $this->client->getRecord('cg_chats', $chat, $superToken, 'user');
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        return view('admin.chats.show', [
            'chat' => Chat::fromRecord($record),
            'isAdmin' => true,
            'pollUrl' => route('admin.chats.messages', $chat),
            'postUrl' => route('admin.chats.send', $chat),
            'activeSection' => 'admin-chats',
        ]);
    }

    public function messages(string $chat): JsonResponse
    {
        $superToken = $this->client->superuserToken();

        $resp = $this->client->listRecords('cg_messages', $superToken, [
            'filter' => "chat='{$chat}'",
            'sort' => 'id',
            'perPage' => 200,
        ]);

        $messages = collect($resp['items'] ?? [])
            ->map(fn (array $r) => Message::fromRecord($r))
            ->map(fn (Message $m) => [
                'id' => $m->id,
                'sender_role' => $m->senderRole,
                'body' => $m->body,
                'created' => $m->created?->toIso8601String(),
                'is_mine' => $m->senderRole === 'admin',
            ])
            ->values();

        $this->markRead->handle($chat, viewerRole: 'admin');

        return response()->json([
            'chat_id' => $chat,
            'messages' => $messages,
        ]);
    }

    public function send(StoreMessageRequest $request, string $chat, SendMessage $send): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $body = (string) $request->validated('body');

        $send->handle(
            sender: $user,
            chatId: $chat,
            body: $body,
            senderRole: 'admin',
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('admin.chats.show', $chat);
    }
}
