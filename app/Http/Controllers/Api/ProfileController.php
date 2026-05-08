<?php

namespace App\Http\Controllers\Api;

use App\Actions\Profile\UpdateProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\UploadedFile;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile): UserResource
    {
        $attributes = $request->validated();
        $avatar = $request->file('avatar') ?? $request->input('avatar');

        if ($avatar instanceof UploadedFile) {
            $attributes['avatar'] = $avatar;
        }

        return new UserResource($updateProfile->handle($request->user(), $attributes));
    }
}
