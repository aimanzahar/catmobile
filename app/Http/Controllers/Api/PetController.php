<?php

namespace App\Http\Controllers\Api;

use App\Actions\Pets\CreatePet;
use App\Actions\Pets\UpdatePet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\StorePetRequest;
use App\Http\Requests\Pet\UpdatePetRequest;
use App\Http\Resources\PetResource;
use App\Models\Pet;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PetController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $response = $this->client->listRecords('cg_pets', $user->pocketbase_token, [
            'filter' => "user='{$user->id}'",
            'sort' => 'name',
            'perPage' => 200,
        ]);

        $pets = collect($response['items'] ?? [])->map(fn (array $record) => Pet::fromRecord($record));

        return PetResource::collection($pets);
    }

    public function store(StorePetRequest $request, CreatePet $createPet): JsonResponse
    {
        $pet = $createPet->handle($request->user(), $request->validated());

        return response()->json(new PetResource($pet), 201);
    }

    public function update(UpdatePetRequest $request, string $pet, UpdatePet $updatePet): PetResource
    {
        $owned = $this->loadOwnedPet($request, $pet);

        return new PetResource($updatePet->handle($request->user(), $owned, $request->validated()));
    }

    public function destroy(Request $request, string $pet): JsonResponse
    {
        $owned = $this->loadOwnedPet($request, $pet);
        $this->client->deleteRecord('cg_pets', $owned->id, $request->user()->pocketbase_token);

        return response()->json([
            'message' => 'Pet removed successfully.',
        ]);
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
