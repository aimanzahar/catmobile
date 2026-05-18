<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $superToken = $this->client->superuserToken();

        $resp = $this->client->listRecords('cg_notifications', $superToken, [
            'filter' => "user='{$user->id}'",
            'sort' => '-id',
            'perPage' => 100,
        ]);

        $notifications = collect($resp['items'] ?? [])
            ->map(fn (array $r) => Notification::fromRecord($r))
            ->values();

        return view('notifications.index', [
            'notifications' => $notifications,
            'activeSection' => 'notifications',
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $superToken = $this->client->superuserToken();

        try {
            $record = $this->client->getRecord('cg_notifications', $notification, $superToken);
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        if (($record['user'] ?? null) !== $user->id) {
            throw new NotFoundHttpException();
        }

        $this->client->updateRecord('cg_notifications', $notification, [
            'read_at' => Carbon::now()->toIso8601String(),
        ], $superToken);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        $link = $record['link'] ?? null;
        if ($link && str_starts_with($link, '/')) {
            return redirect($link);
        }

        return redirect()->route('notifications.index');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $superToken = $this->client->superuserToken();

        $resp = $this->client->listRecords('cg_notifications', $superToken, [
            'filter' => "user='{$user->id}' && read_at=''",
            'perPage' => 200,
        ]);

        $now = Carbon::now()->toIso8601String();
        foreach ($resp['items'] ?? [] as $row) {
            $this->client->updateRecord('cg_notifications', (string) $row['id'], [
                'read_at' => $now,
            ], $superToken);
        }

        return response()->json(['ok' => true]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $superToken = $this->client->superuserToken();

        $resp = $this->client->listRecords('cg_notifications', $superToken, [
            'filter' => "user='{$user->id}' && read_at=''",
            'perPage' => 1,
        ]);

        $count = (int) ($resp['totalItems'] ?? 0);

        return response()->json(['unread' => $count]);
    }
}
