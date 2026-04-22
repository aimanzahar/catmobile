<?php

namespace App\Models;

class Pet
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $breed = null,
        public readonly ?int $age = null,
        public readonly int|float|null $weight = null,
        public readonly ?string $special_notes = null,
        public readonly ?string $image = null,
        public readonly ?string $user = null,
    ) {}

    public static function fromRecord(array $record): self
    {
        return new self(
            id: (string) ($record['id'] ?? ''),
            name: (string) ($record['name'] ?? ''),
            breed: isset($record['breed']) && $record['breed'] !== '' ? (string) $record['breed'] : null,
            age: isset($record['age']) && $record['age'] !== '' ? (int) $record['age'] : null,
            weight: isset($record['weight']) && $record['weight'] !== '' ? (float) $record['weight'] : null,
            special_notes: isset($record['special_notes']) && $record['special_notes'] !== '' ? (string) $record['special_notes'] : null,
            image: isset($record['image']) && $record['image'] !== '' ? (string) $record['image'] : null,
            user: isset($record['user']) && $record['user'] !== '' ? (string) $record['user'] : null,
        );
    }
}
