<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function store(LoginRequest $request, AuthenticateUser $authenticateUser): JsonResponse
    {
        try {
            $result = $authenticateUser->handle($request->validated());
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => $exception->errors(),
            ], 401);
        }

        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        // PocketBase tokens are stateless JWTs; logout is client-side (drop the token).
        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
