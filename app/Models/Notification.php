<?php

namespace App\Models;

use Carbon\Carbon;

class Notification
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly ?string $link = null,
        public readonly ?string $relatedId = null,
        public readonly ?Carbon $readAt = null,
        public readonly ?Carbon $created = null,
    ) {}

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public static function fromRecord(array $record): self
    {
        $readRaw = $record['read_at'] ?? null;
        $createdRaw = $record['created'] ?? null;

        return new self(
            id: (string) ($record['id'] ?? ''),
            userId: (string) ($record['user'] ?? ''),
            type: (string) ($record['type'] ?? ''),
            title: (string) ($record['title'] ?? ''),
            body: isset($record['body']) && $record['body'] !== '' ? (string) $record['body'] : null,
            link: isset($record['link']) && $record['link'] !== '' ? (string) $record['link'] : null,
            relatedId: isset($record['related_id']) && $record['related_id'] !== '' ? (string) $record['related_id'] : null,
            readAt: $readRaw ? Carbon::parse($readRaw) : null,
            created: $createdRaw ? Carbon::parse($createdRaw) : null,
        );
    }
}
