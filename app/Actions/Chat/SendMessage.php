<?php

namespace App\Actions\Chat;

use App\Actions\Notifications\CreateNotification;
use App\Models\Message;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendMessage
{
    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly CreateNotification $createNotification,
    ) {}

    public function handle(User $sender, string $chatId, string $body, string $senderRole): Message
    {
        $body = trim($body);
        if ($body === '') {
            throw new RuntimeException('Message body cannot be empty.');
        }

        $superToken = $this->client->superuserToken();

        $chat = $this->client->getRecord('cg_chats', $chatId, $superToken, 'user');

        if ($senderRole === 'customer' && ($chat['user'] ?? null) !== $sender->id) {
            throw new RuntimeException('Customer cannot post into another user\'s chat.');
        }

        $messageRecord = $this->client->createRecord('cg_messages', [
            'chat' => $chatId,
            'sender' => $sender->id,
            'sender_role' => $senderRole,
            'body' => $body,
            'read_by_customer' => $senderRole === 'customer',
            'read_by_admin' => $senderRole === 'admin',
        ], $superToken);

        $preview = mb_substr($body, 0, 120);
        $update = [
            'last_message_at' => Carbon::now()->toIso8601String(),
            'last_message_preview' => $preview,
        ];

        if ($senderRole === 'customer') {
            $update['unread_for_admin'] = (int) ($chat['unread_for_admin'] ?? 0) + 1;
        } else {
            $update['unread_for_customer'] = (int) ($chat['unread_for_customer'] ?? 0) + 1;
        }

        $this->client->updateRecord('cg_chats', $chatId, $update, $superToken);

        if ($senderRole === 'admin') {
            $customerId = (string) ($chat['user'] ?? '');
            if ($customerId !== '') {
                try {
                    $this->createNotification->handle(
                        userId: $customerId,
                        type: 'chat_message',
                        title: 'New message from the shop',
                        body: $preview,
                        link: '/chat',
                        relatedId: $chatId,
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to dispatch chat notification', [
                        'chat' => $chatId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return Message::fromRecord($messageRecord);
    }
}
