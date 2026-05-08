<?php

namespace App\Http\Controllers;

use App\Actions\Profile\UpdateProfile;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

class ProfileController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->route('dashboard', ['section' => 'profile']);
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile): RedirectResponse
    {
        $attributes = $request->validated();
        $avatar = $request->file('avatar') ?? $request->input('avatar');

        if ($avatar instanceof UploadedFile) {
            $attributes['avatar'] = $avatar;
        }

        $updateProfile->handle($request->user(), $attributes);

        return redirect()->route('dashboard')
            ->with('status', 'Profile updated successfully.');
    }
}
