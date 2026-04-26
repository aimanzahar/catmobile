<?php

namespace App\Models;

use App\Auth\PocketBaseGuard;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    public ?string $pocketbase_token = null;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $verified = false,
        public readonly ?string $created = null,
        public readonly ?string $updated = null,
        public readonly ?string $avatar = null,
    ) {}

    public static function fromRecord(array $record, ?string $token = null): self
    {
        $rawAvatar = $record['avatar'] ?? null;
        $avatar = is_string($rawAvatar) && $rawAvatar !== '' ? $rawAvatar : null;

        $user = new self(
            id: (string) ($record['id'] ?? ''),
            name: (string) ($record['name'] ?? ''),
            email: (string) ($record['email'] ?? ''),
            verified: (bool) ($record['verified'] ?? false),
            created: isset($record['created']) ? (string) $record['created'] : null,
            updated: isset($record['updated']) ? (string) $record['updated'] : null,
            avatar: $avatar,
        );
        $user->pocketbase_token = $token;

        return $user;
    }

    public function avatarUrl(?string $thumb = '100x100'): ?string
    {
        if ($this->avatar === null || $this->avatar === '') {
            return null;
        }

        return app(PocketBaseClient::class)->fileUrl(
            PocketBaseGuard::USERS_COLLECTION,
            $this->id,
            $this->avatar,
            $thumb,
        );
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): string
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordHash(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // no-op; remember tokens handled by PocketBase sessions
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }
}
