<?php

namespace App\Actions\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Notification;
use App\Services\PocketBase\PocketBaseClient;

class CreateNotification
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(
        string $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null,
        ?string $relatedId = null,
        bool $sendPush = true,
    ): Notification {
        $superToken = $this->client->superuserToken();

        $record = $this->client->createRecord('cg_notifications', array_filter([
            'user' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'related_id' => $relatedId,
        ], static fn ($v) => $v !== null && $v !== ''), $superToken);

        $notification = Notification::fromRecord($record);

        if ($sendPush && class_exists(SendPushNotification::class)) {
            try {
                SendPushNotification::dispatch(
                    userId: $userId,
                    title: $title,
                    body: $body ?? '',
                    data: array_filter([
                        'type' => $type,
                        'link' => $link,
                        'notification_id' => $notification->id,
                    ], static fn ($v) => $v !== null && $v !== ''),
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to dispatch push notification', [
                    'user' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }
}
