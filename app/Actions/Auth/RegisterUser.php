<?php

namespace App\Actions\Auth;

use App\Auth\PocketBaseGuard;
use App\Models\User;
use App\Services\PocketBase\Exceptions\PocketBaseValidationException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Validation\ValidationException;

class RegisterUser
{
    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly AuthenticateUser $authenticate,
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function handle(array $attributes): array
    {
        try {
            $this->client->createRecord(PocketBaseGuard::USERS_COLLECTION, [
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'passwordConfirm' => $attributes['password'],
                'name' => $attributes['name'],
                'emailVisibility' => true,
            ]);
        } catch (PocketBaseValidationException $exception) {
            $errors = $this->translateErrors($exception->errors());
            throw ValidationException::withMessages($errors);
        }

        return $this->authenticate->handle([
            'email' => $attributes['email'],
            'password' => $attributes['password'],
        ]);
    }

    private function translateErrors(array $errors): array
    {
        $messages = [];
        foreach ($errors as $field => $msgs) {
            if ($field === 'email') {
                $messages['email'] = $msgs;
            } elseif ($field === 'password') {
                $messages['password'] = $msgs;
            } elseif ($field === 'name') {
                $messages['name'] = $msgs;
            } else {
                $messages[$field] = $msgs;
            }
        }

        return $messages ?: ['email' => ['Unable to register. Please try again.']];
    }
}
