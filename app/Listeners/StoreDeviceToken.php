<?php

namespace App\Listeners;

use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StoreDeviceToken
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(object $event): void
    {
        $token = $event->token ?? null;
        if (! is_string($token) || $token === '') {
            return;
        }

        $enrollmentId = isset($event->id) ? (string) $event->id : '';
        $userId = '';
        if (str_starts_with($enrollmentId, 'user-')) {
            $userId = substr($enrollmentId, 5);
        }

        if ($userId === '') {
            Log::info('StoreDeviceToken: no user id in enrollment, skipping', ['id' => $enrollmentId]);
            return;
        }

        $platform = $event->platform ?? 'android';
        if (! in_array($platform, ['android', 'ios'], true)) {
            $platform = 'android';
        }

        $superToken = $this->client->superuserToken();
        $now = Carbon::now()->toIso8601String();

        $existing = $this->client->listRecords('cg_device_tokens', $superToken, [
            'filter' => "token='".addslashes($token)."'",
            'perPage' => 1,
        ]);

        if (! empty($existing['items'])) {
            $this->client->updateRecord('cg_device_tokens', (string) $existing['items'][0]['id'], [
                'user' => $userId,
                'last_seen_at' => $now,
                'platform' => $platform,
            ], $superToken);
        } else {
            $this->client->createRecord('cg_device_tokens', [
                'user' => $userId,
                'token' => $token,
                'platform' => $platform,
                'last_seen_at' => $now,
            ], $superToken);
        }
    }
}
