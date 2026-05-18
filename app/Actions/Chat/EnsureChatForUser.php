<?php

namespace App\Actions\Chat;

use App\Models\Chat;
use App\Services\PocketBase\PocketBaseClient;

class EnsureChatForUser
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(string $userId): Chat
    {
        $superToken = $this->client->superuserToken();

        $existing = $this->client->listRecords('cg_chats', $superToken, [
            'filter' => "user='{$userId}'",
            'expand' => 'user',
            'perPage' => 1,
        ]);

        if (! empty($existing['items'])) {
            return Chat::fromRecord($existing['items'][0]);
        }

        $created = $this->client->createRecord('cg_chats', [
            'user' => $userId,
            'unread_for_customer' => 0,
            'unread_for_admin' => 0,
        ], $superToken);

        $expanded = $this->client->getRecord('cg_chats', (string) $created['id'], $superToken, 'user');

        return Chat::fromRecord($expanded);
    }
}
