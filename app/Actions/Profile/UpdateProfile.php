<?php

namespace App\Actions\Profile;

use App\Models\User;

class UpdateProfile
{
    public function handle(User $user, array $attributes): User
    {
        $user->fill($attributes);
        $user->save();

        return $user->refresh();
    }
}
