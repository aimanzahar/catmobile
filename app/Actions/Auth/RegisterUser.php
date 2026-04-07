<?php

namespace App\Actions\Auth;

use App\Models\User;

class RegisterUser
{
    public function handle(array $attributes): User
    {
        return User::create($attributes);
    }
}
