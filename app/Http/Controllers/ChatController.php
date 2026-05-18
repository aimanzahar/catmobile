<?php

namespace App\Http\Controllers;

use App\Actions\Chat\EnsureChatForUser;
use App\Actions\Chat\MarkChatAsRead;
use App\Actions\Chat\SendMessage;
use App\Http\Requests\Chat\StoreMessageRequest;
use App\Models\Message;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly EnsureChatForUser $ensureChat,
        private readonly MarkChatAsRead $markRead,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();
        $chat = $this->ensureChat->handle($user->id);

        return view('chat.show', [
            'chat' => $chat,
            'isAdmin' => false,
            'pollUrl' => route('chat.messages'),
            'postUrl' => route('chat.send'),
            'activeSection' => 'chat',
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();
        $superToken = $this->client->superuserToken();

        $chat = $this->ensureChat->handle($user->id);

        $resp = $this->client->listRecords('cg_messages', $superToken, [
            'filter' => "chat='{$chat->id}'",
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
                'is_mine' => $m->senderRole === 'customer',
            ])
            ->values();

        $this->markRead->handle($chat->id, viewerRole: 'customer');

        return response()->json([
            'chat_id' => $chat->id,
            'messages' => $messages,
        ]);
    }

    public function send(StoreMessageRequest $request, SendMessage $send): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $body = (string) $request->validated('body');

        $chat = $this->ensureChat->handle($user->id);

        $send->handle(
            sender: $user,
            chatId: $chat->id,
            body: $body,
            senderRole: 'customer',
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('chat.show');
    }
}
