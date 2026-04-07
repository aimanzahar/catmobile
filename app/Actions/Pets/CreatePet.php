<?php

namespace App\Actions\Pets;

use App\Models\Pet;
use App\Models\User;

class CreatePet
{
    public function handle(User $user, array $attributes): Pet
    {
        return $user->pets()->create($attributes);
    }
}
