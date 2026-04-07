<?php

namespace App\Http\Controllers;

use App\Actions\Pets\CreatePet;
use App\Actions\Pets\UpdatePet;
use App\Http\Requests\Pet\StorePetRequest;
use App\Http\Requests\Pet\UpdatePetRequest;
use App\Models\Pet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard', ['section' => 'pets']);
    }

    public function store(StorePetRequest $request, CreatePet $createPet): RedirectResponse
    {
        $createPet->handle($request->user(), $request->validated());

        return redirect()->route('dashboard')
            ->with('status', 'Pet added successfully.');
    }

    public function update(UpdatePetRequest $request, Pet $pet, UpdatePet $updatePet): RedirectResponse
    {
        $updatePet->handle($this->resolvePet($request, $pet), $request->validated());

        return redirect()->route('dashboard')
            ->with('status', 'Pet updated successfully.');
    }

    public function destroy(Request $request, Pet $pet): RedirectResponse
    {
        $this->resolvePet($request, $pet)->delete();

        return redirect()->route('dashboard')
            ->with('status', 'Pet removed successfully.');
    }

    private function resolvePet(Request $request, Pet $pet): Pet
    {
        return $request->user()->pets()->whereKey($pet->id)->firstOrFail();
    }
}
