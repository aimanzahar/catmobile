<?php

namespace App\Jobs;

use App\Services\Push\FcmService;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $userId,
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    public function handle(PocketBaseClient $client, FcmService $fcm): void
    {
        if (! $fcm->isConfigured()) {
            Log::info('SendPushNotification skipped: FCM not configured', ['user' => $this->userId]);
            return;
        }

        $superToken = $client->superuserToken();

        $resp = $client->listRecords('cg_device_tokens', $superToken, [
            'filter' => "user='{$this->userId}'",
            'perPage' => 50,
        ]);

        $tokens = $resp['items'] ?? [];

        if (empty($tokens)) {
            return;
        }

        foreach ($tokens as $row) {
            $token = (string) ($row['token'] ?? '');
            if ($token === '') {
                continue;
            }

            try {
                $fcm->sendToToken($token, $this->title, $this->body, $this->data);
            } catch (\Throwable $e) {
                Log::warning('FCM send failed for token; cleaning up', [
                    'token_id' => $row['id'] ?? null,
                    'error' => $e->getMessage(),
                ]);

                if ($fcm->isInvalidTokenError($e)) {
                    try {
                        $client->deleteRecord('cg_device_tokens', (string) $row['id'], $superToken);
                    } catch (\Throwable) {
                        // ignore
                    }
                }
            }
        }
    }
}
