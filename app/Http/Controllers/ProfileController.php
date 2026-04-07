<?php

namespace App\Http\Controllers;

use App\Actions\Profile\UpdateProfile;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->route('dashboard', ['section' => 'profile']);
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile): RedirectResponse
    {
        $updateProfile->handle($request->user(), $request->validated());

        return redirect()->route('dashboard')
            ->with('status', 'Profile updated successfully.');
    }
}
