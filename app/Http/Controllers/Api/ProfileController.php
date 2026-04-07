<?php

namespace App\Http\Controllers\Api;

use App\Actions\Profile\UpdateProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile): UserResource
    {
        return new UserResource($updateProfile->handle($request->user(), $request->validated()));
    }
}
