<?php

use App\Console\Commands\DispatchOutboxEvents;
use App\Http\Middleware\EnsurePromoterIsVerified;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\PermissionMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        DispatchOutboxEvents::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'verified_promotor' => EnsurePromoterIsVerified::class,
            'permission' => PermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('outbox:dispatch --limit=50')
            ->everyMinute()
            ->name('stiflow-outbox-dispatch')
            ->withoutOverlapping(5);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
