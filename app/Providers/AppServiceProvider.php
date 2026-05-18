<?php

namespace App\Providers;

use App\Auth\PocketBaseGuard;
use App\Listeners\StoreDeviceToken;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PocketBaseClient::class);
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Auth::extend('pocketbase-session', function ($app) {
            return new PocketBaseGuard(
                $app->make(PocketBaseClient::class),
                $app->make('request'),
                $app->make('session.store'),
                'session',
            );
        });

        Auth::extend('pocketbase-token', function ($app) {
            return new PocketBaseGuard(
                $app->make(PocketBaseClient::class),
                $app->make('request'),
                null,
                'token',
            );
        });

        if (class_exists(\Native\Mobile\Events\PushNotification\TokenGenerated::class)) {
            Event::listen(
                \Native\Mobile\Events\PushNotification\TokenGenerated::class,
                StoreDeviceToken::class,
            );
        }

        if (class_exists(\App\Events\PushTokenGenerated::class)) {
            Event::listen(
                \App\Events\PushTokenGenerated::class,
                StoreDeviceToken::class,
            );
        }
    }
}
