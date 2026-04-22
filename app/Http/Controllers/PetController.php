<?php

namespace App\Http\Controllers;

use App\Actions\Pets\CreatePet;
use App\Actions\Pets\UpdatePet;
use App\Http\Requests\Pet\StorePetRequest;
use App\Http\Requests\Pet\UpdatePetRequest;
use App\Models\Pet;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PetController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

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

    public function update(UpdatePetRequest $request, string $pet, UpdatePet $updatePet): RedirectResponse
    {
        $owned = $this->loadOwnedPet($request, $pet);
        $updatePet->handle($request->user(), $owned, $request->validated());

        return redirect()->route('dashboard')
            ->with('status', 'Pet updated successfully.');
    }

    public function destroy(Request $request, string $pet): RedirectResponse
    {
        $owned = $this->loadOwnedPet($request, $pet);
        $this->client->deleteRecord('cg_pets', $owned->id, $request->user()->pocketbase_token);

        return redirect()->route('dashboard')
            ->with('status', 'Pet removed successfully.');
    }

    private function loadOwnedPet(Request $request, string $petId): Pet
    {
        $user = $request->user();

        try {
            $record = $this->client->getRecord('cg_pets', $petId, $user->pocketbase_token);
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        if (($record['user'] ?? null) !== $user->id) {
            throw new NotFoundHttpException();
        }

        return Pet::fromRecord($record);
    }
}
