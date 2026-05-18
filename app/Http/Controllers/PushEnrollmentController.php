<?php

namespace App\Http\Controllers;

use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PushEnrollmentController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function enroll(Request $request): JsonResponse
    {
        if (! class_exists(\Native\Mobile\Facades\PushNotifications::class)) {
            return response()->json(['ok' => false, 'reason' => 'push-unavailable']);
        }

        $user = $request->user();
        if ($user === null) {
            return response()->json(['ok' => false, 'reason' => 'unauthenticated'], 401);
        }

        try {
            \Native\Mobile\Facades\PushNotifications::enroll()
                ->id('user-'.$user->id)
                ->event(\App\Events\PushTokenGenerated::class)
                ->remember();
        } catch (\Throwable $e) {
            Log::warning('PushNotifications enroll failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'reason' => 'enroll-failed']);
        }

        return response()->json(['ok' => true]);
    }

    public function storeToken(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['ok' => false], 401);
        }

        $data = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'in:android,ios'],
        ]);

        $superToken = $this->client->superuserToken();

        $existing = $this->client->listRecords('cg_device_tokens', $superToken, [
            'filter' => "token='".addslashes($data['token'])."'",
            'perPage' => 1,
        ]);

        $now = Carbon::now()->toIso8601String();

        if (! empty($existing['items'])) {
            $this->client->updateRecord('cg_device_tokens', (string) $existing['items'][0]['id'], [
                'user' => $user->id,
                'last_seen_at' => $now,
                'platform' => $data['platform'] ?? ($existing['items'][0]['platform'] ?? 'android'),
            ], $superToken);
        } else {
            $this->client->createRecord('cg_device_tokens', [
                'user' => $user->id,
                'token' => $data['token'],
                'platform' => $data['platform'] ?? 'android',
                'last_seen_at' => $now,
            ], $superToken);
        }

        return response()->json(['ok' => true]);
    }
}
