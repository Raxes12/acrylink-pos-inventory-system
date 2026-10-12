<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectRequestRateAnomaly;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust only explicitly configured reverse proxies. Trusting every
        // sender lets clients spoof X-Forwarded-For and bypass IP controls.
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        )));
        if ($trustedProxies !== []) {
            $middleware->trustProxies(at: $trustedProxies);
        }

        // Append custom middleware to the web group
        $middleware->web(append: [
            AddSecurityHeaders::class,
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectRequestRateAnomaly::class,
            TrackUserSession::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'device' => ValidateAuthorizedDevice::class,
            'session.anomaly' => DetectSessionAnomaly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
