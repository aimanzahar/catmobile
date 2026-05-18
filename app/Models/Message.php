<?php

namespace App\Models;

use Carbon\Carbon;

class Message
{
    public function __construct(
        public readonly string $id,
        public readonly string $chatId,
        public readonly string $senderId,
        public readonly string $senderRole,
        public readonly string $body,
        public readonly bool $readByCustomer = false,
        public readonly bool $readByAdmin = false,
        public readonly ?Carbon $created = null,
    ) {}

    public static function fromRecord(array $record): self
    {
        return new self(
            id: (string) ($record['id'] ?? ''),
            chatId: (string) ($record['chat'] ?? ''),
            senderId: (string) ($record['sender'] ?? ''),
            senderRole: (string) ($record['sender_role'] ?? 'customer'),
            body: (string) ($record['body'] ?? ''),
            readByCustomer: (bool) ($record['read_by_customer'] ?? false),
            readByAdmin: (bool) ($record['read_by_admin'] ?? false),
            created: isset($record['created']) && $record['created'] !== ''
                ? Carbon::parse($record['created'])
                : null,
        );
    }
}
