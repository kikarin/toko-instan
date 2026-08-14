<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Gateways\MidtransGateway;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Withdrawal;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\StockMovementObserver;
use App\Observers\StoreObserver;
use App\Observers\WithdrawalObserver;
use App\Services\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn (): TenantContext => new TenantContext);

        // Config-driven default gateway (online methods still pick Midtrans via PaymentService).
        $this->app->bind(PaymentGateway::class, function ($app) {
            return match (config('services.payment.default', 'midtrans')) {
                default => $app->make(MidtransGateway::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email', '')).'|'.$request->ip(),
            );
        });

        Product::observe(ProductObserver::class);
        StockMovement::observe(StockMovementObserver::class);
        Order::observe(OrderObserver::class);
        Withdrawal::observe(WithdrawalObserver::class);
        Store::observe(StoreObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
