<?php

namespace App\Actions\Chat;

use App\Services\PocketBase\PocketBaseClient;

class MarkChatAsRead
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(string $chatId, string $viewerRole): void
    {
        if (! in_array($viewerRole, ['customer', 'admin'], true)) {
            return;
        }

        $superToken = $this->client->superuserToken();

        $this->client->updateRecord('cg_chats', $chatId, [
            $viewerRole === 'customer' ? 'unread_for_customer' : 'unread_for_admin' => 0,
        ], $superToken);

        $unreadField = $viewerRole === 'customer' ? 'read_by_customer' : 'read_by_admin';

        $unread = $this->client->listRecords('cg_messages', $superToken, [
            'filter' => "chat='{$chatId}' && {$unreadField}=false",
            'perPage' => 200,
        ]);

        foreach ($unread['items'] ?? [] as $row) {
            $this->client->updateRecord('cg_messages', (string) $row['id'], [
                $unreadField => true,
            ], $superToken);
        }
    }
}
