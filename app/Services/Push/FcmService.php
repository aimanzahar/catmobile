<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Log;

class FcmService
{
    private bool $available;

    public function __construct()
    {
        $credentialsPath = (string) config('services.fcm.credentials_path', '');

        $this->available = $credentialsPath !== ''
            && file_exists(base_path($credentialsPath))
            && class_exists(\Kreait\Firebase\Factory::class);
    }

    public function isConfigured(): bool
    {
        return $this->available;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): void
    {
        if (! $this->available) {
            return;
        }

        $credentialsPath = base_path((string) config('services.fcm.credentials_path'));

        $messaging = (new \Kreait\Firebase\Factory)
            ->withServiceAccount($credentialsPath)
            ->createMessaging();

        $stringData = [];
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $stringData[(string) $key] = (string) $value;
            }
        }

        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)
            ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $body))
            ->withData($stringData);

        $messaging->send($message);
    }

    public function isInvalidTokenError(\Throwable $e): bool
    {
        if (class_exists(\Kreait\Firebase\Exception\Messaging\NotFound::class)
            && $e instanceof \Kreait\Firebase\Exception\Messaging\NotFound) {
            return true;
        }

        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'registration-token-not-registered')
            || str_contains($msg, 'invalid registration')
            || str_contains($msg, 'not found');
    }
}
