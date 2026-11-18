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

    /**
     * In-app notification for every admin. Never throws: a failed notification
     * must not break the booking or chat action that triggered it.
     */
    public function notifyAdmins(string $type, string $title, ?string $body = null, ?string $link = null, ?string $relatedId = null): void
    {
        try {
            $admins = $this->client->listRecords('cg_users', $this->client->superuserToken(), [
                'filter' => "role='admin'",
                'perPage' => 50,
            ]);

            foreach ($admins['items'] ?? [] as $admin) {
                $this->handle((string) $admin['id'], $type, $title, $body, $link, $relatedId, sendPush: false);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to notify admins', [
                'type' => $type,
                'related' => $relatedId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
