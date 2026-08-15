<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureSellerApi;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\RecordStoreVisit;
use App\Http\Middleware\SetCrossOriginOpenerPolicy;
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
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['web', 'auth']],
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('subscriptions:process')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            IdentifyTenant::class,
            SetCrossOriginOpenerPolicy::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'tenant' => IdentifyTenant::class,
            'store.visit' => RecordStoreVisit::class,
            'seller.api' => EnsureSellerApi::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/midtrans',
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
