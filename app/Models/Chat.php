<?php

namespace App\Models;

use Carbon\Carbon;

class Chat
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly ?string $userName = null,
        public readonly ?string $userEmail = null,
        public readonly ?string $userAvatar = null,
        public readonly ?Carbon $lastMessageAt = null,
        public readonly ?string $lastMessagePreview = null,
        public readonly int $unreadForCustomer = 0,
        public readonly int $unreadForAdmin = 0,
        public readonly ?Carbon $created = null,
    ) {}

    public static function fromRecord(array $record): self
    {
        $userExpand = $record['expand']['user'] ?? null;
        $userId = is_array($userExpand) ? (string) ($userExpand['id'] ?? '') : (string) ($record['user'] ?? '');

        return new self(
            id: (string) ($record['id'] ?? ''),
            userId: $userId,
            userName: is_array($userExpand) ? ($userExpand['name'] ?? null) : null,
            userEmail: is_array($userExpand) ? ($userExpand['email'] ?? null) : null,
            userAvatar: is_array($userExpand) && isset($userExpand['avatar']) && $userExpand['avatar'] !== ''
                ? (string) $userExpand['avatar']
                : null,
            lastMessageAt: isset($record['last_message_at']) && $record['last_message_at'] !== ''
                ? Carbon::parse($record['last_message_at'])
                : null,
            lastMessagePreview: isset($record['last_message_preview']) && $record['last_message_preview'] !== ''
                ? (string) $record['last_message_preview']
                : null,
            unreadForCustomer: (int) ($record['unread_for_customer'] ?? 0),
            unreadForAdmin: (int) ($record['unread_for_admin'] ?? 0),
            created: isset($record['created']) && $record['created'] !== ''
                ? Carbon::parse($record['created'])
                : null,
        );
    }
}
