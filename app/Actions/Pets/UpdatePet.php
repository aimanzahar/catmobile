<?php

namespace App\Actions\Pets;

use App\Models\Pet;

class UpdatePet
{
    public function handle(Pet $pet, array $attributes): Pet
    {
        $pet->update($attributes);

        return $pet->refresh();
    }
}
