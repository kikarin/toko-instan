<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            IdentifyTenant::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'tenant' => IdentifyTenant::class,
        ]);

        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->homePath() ?? '/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->renderable(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*') || ! in_array($e->getStatusCode(), [403, 404], true)) {
                return null;
            }

            return Inertia::render('Error', ['status' => $e->getStatusCode()])
                ->toResponse($request)
                ->setStatusCode($e->getStatusCode());
        });
    })->create();
