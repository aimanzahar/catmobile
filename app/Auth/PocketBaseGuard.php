<?php

namespace App\Auth;

use App\Models\User;
use App\Services\PocketBase\Exceptions\PocketBaseAuthException;
use App\Services\PocketBase\Exceptions\PocketBaseException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Session\Store as SessionStore;

class PocketBaseGuard implements Guard
{
    public const USERS_COLLECTION = 'cg_users';

    public const SESSION_TOKEN_KEY = 'pocketbase_token';

    private ?User $user = null;

    private bool $resolved = false;

    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly Request $request,
        private readonly ?SessionStore $session,
        private readonly string $source,
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?User
    {
        if ($this->resolved) {
            return $this->user;
        }

        $token = $this->resolveToken();
        if ($token === null || $token === '') {
            $this->resolved = true;

            return null;
        }

        try {
            $refreshed = $this->client->authRefresh(self::USERS_COLLECTION, $token);
        } catch (PocketBaseAuthException) {
            $this->clearSessionToken();
            $this->resolved = true;

            return null;
        } catch (PocketBaseException) {
            $this->resolved = true;

            return null;
        }

        $newToken = $refreshed['token'] ?? $token;
        $this->user = User::fromRecord($refreshed['record'] ?? [], $newToken);

        if ($this->source === 'session' && $newToken !== $token) {
            $this->session?->put(self::SESSION_TOKEN_KEY, $newToken);
        }

        $this->resolved = true;

        return $this->user;
    }

    public function id(): ?string
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        if (! isset($credentials['email'], $credentials['password'])) {
            return false;
        }

        try {
            $this->client->authWithPassword(self::USERS_COLLECTION, $credentials['email'], $credentials['password']);

            return true;
        } catch (PocketBaseAuthException) {
            return false;
        }
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): self
    {
        if ($user instanceof User) {
            $this->user = $user;
            $this->resolved = true;
        }

        return $this;
    }

    /**
     * Persist the authenticated user and PocketBase token for the session.
     */
    public function login(User $user, string $token): void
    {
        $user->pocketbase_token = $token;
        $this->user = $user;
        $this->resolved = true;

        if ($this->source === 'session') {
            $this->session?->put(self::SESSION_TOKEN_KEY, $token);
        }
    }

    public function logout(): void
    {
        $this->user = null;
        $this->resolved = true;

        $this->clearSessionToken();
    }

    private function resolveToken(): ?string
    {
        if ($this->source === 'session') {
            return $this->session?->get(self::SESSION_TOKEN_KEY);
        }

        return $this->request->bearerToken();
    }

    private function clearSessionToken(): void
    {
        if ($this->source === 'session') {
            $this->session?->forget(self::SESSION_TOKEN_KEY);
        }
    }
}
