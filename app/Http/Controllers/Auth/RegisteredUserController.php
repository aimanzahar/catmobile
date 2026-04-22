<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUser;
use App\Auth\PocketBaseGuard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, RegisterUser $registerUser): RedirectResponse
    {
        $result = $registerUser->handle($request->validated());

        $guard = Auth::guard('web');
        if ($guard instanceof PocketBaseGuard) {
            $guard->login($result['user'], $result['token']);
        }

        $request->session()->regenerate();
        $request->session()->put(PocketBaseGuard::SESSION_TOKEN_KEY, $result['token']);

        return redirect()->intended(route('dashboard'));
    }
}
