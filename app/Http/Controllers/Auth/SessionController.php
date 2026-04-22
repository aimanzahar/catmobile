<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Auth\PocketBaseGuard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuthenticateUser $authenticateUser): RedirectResponse
    {
        try {
            $result = $authenticateUser->handle($request->validated());
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        $guard = Auth::guard('web');
        if ($guard instanceof PocketBaseGuard) {
            $guard->login($result['user'], $result['token']);
        }

        $request->session()->regenerate();
        // Re-store the token because regenerate() rotates the session id but keeps the data,
        // yet we want the login() call above to survive regeneration.
        $request->session()->put(PocketBaseGuard::SESSION_TOKEN_KEY, $result['token']);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $guard = Auth::guard('web');
        if ($guard instanceof PocketBaseGuard) {
            $guard->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
