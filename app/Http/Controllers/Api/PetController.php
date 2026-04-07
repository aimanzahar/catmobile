<?php

namespace App\Http\Controllers\Api;

use App\Actions\Pets\CreatePet;
use App\Actions\Pets\UpdatePet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pet\StorePetRequest;
use App\Http\Requests\Pet\UpdatePetRequest;
use App\Http\Resources\PetResource;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return PetResource::collection($request->user()->pets()->orderBy('name')->get());
    }

    public function store(StorePetRequest $request, CreatePet $createPet): JsonResponse
    {
        $pet = $createPet->handle($request->user(), $request->validated());

        return response()->json(new PetResource($pet), 201);
    }

    public function update(UpdatePetRequest $request, Pet $pet, UpdatePet $updatePet): PetResource
    {
        return new PetResource($updatePet->handle($this->resolvePet($request, $pet), $request->validated()));
    }

    public function destroy(Request $request, Pet $pet): JsonResponse
    {
        $this->resolvePet($request, $pet)->delete();

        return response()->json([
            'message' => 'Pet removed successfully.',
        ]);
    }

    private function resolvePet(Request $request, Pet $pet): Pet
    {
        return $request->user()->pets()->whereKey($pet->id)->firstOrFail();
    }
}
