<?php

namespace App\Actions\Chat;

use App\Services\PocketBase\PocketBaseClient;

class MarkChatAsRead
{
    public function __construct(private readonly PocketBaseClient $client) {}

    /**
     * Runs on every chat poll, so skip the remote write when the caller
     * already knows there is nothing unread.
     */
    public function handle(string $chatId, string $viewerRole, ?int $currentUnread = null): void
    {
        if (! in_array($viewerRole, ['customer', 'admin'], true) || $currentUnread === 0) {
            return;
        }

        $this->client->updateRecord('cg_chats', $chatId, [
            $viewerRole === 'customer' ? 'unread_for_customer' : 'unread_for_admin' => 0,
        ], $this->client->superuserToken());
    }
}
