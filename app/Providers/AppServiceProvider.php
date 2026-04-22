<?php

namespace App\Providers;

use App\Auth\PocketBaseGuard;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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
    }
}
