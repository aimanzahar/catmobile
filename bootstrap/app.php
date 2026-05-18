<?php

use App\Services\PocketBase\Exceptions\PocketBaseException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $backendUnavailableResponse = function (Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to reach the booking service. Please check your connection and try again.',
                ], 503);
            }

            return response()->view('errors.backend-unavailable', [], 503);
        };

        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please try again.',
                ], 403);
            }

            return redirect()
                ->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Session expired. Please try again.');
        });

        $exceptions->render(function (ConnectionException $e, Request $request) use ($backendUnavailableResponse) {
            Log::warning('External service connection failed', [
                'url' => $request->fullUrl(),
                'error' => $e->getMessage(),
            ]);

            return $backendUnavailableResponse($request);
        });

        $exceptions->render(function (PocketBaseException $e, Request $request) use ($backendUnavailableResponse) {
            if ($e->getCode() < 500) {
                return null;
            }

            Log::warning('PocketBase service request failed', [
                'url' => $request->fullUrl(),
                'error' => $e->getMessage(),
            ]);

            return $backendUnavailableResponse($request);
        });
    })->create();
