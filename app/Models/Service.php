<?php

namespace App\Models;

class Service
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly float $price = 0.0,
        public readonly int $duration_minutes = 0,
        public readonly ?string $icon = null,
        public readonly ?string $image = null,
        public readonly bool $is_active = true,
        public readonly int $sort_order = 0,
    ) {}

    public static function fromRecord(array $record): self
    {
        return new self(
            id: (string) ($record['id'] ?? ''),
            name: (string) ($record['name'] ?? ''),
            slug: (string) ($record['slug'] ?? ''),
            description: $record['description'] ?? null,
            price: (float) ($record['price'] ?? 0),
            duration_minutes: (int) ($record['duration_minutes'] ?? 0),
            icon: $record['icon'] ?? null,
            image: $record['image'] ?? null,
            is_active: (bool) ($record['is_active'] ?? false),
            sort_order: (int) ($record['sort_order'] ?? 0),
        );
    }
}
