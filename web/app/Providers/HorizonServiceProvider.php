<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     * Local APP_ENV remains open (Horizon default).
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user = null) {
            if ($user === null) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }

            $allowed = collect(explode(',', (string) env('HORIZON_AUTH_EMAIL', '')))
                ->map(fn (string $email) => strtolower(trim($email)))
                ->filter()
                ->all();

            return in_array(strtolower((string) $user->email), $allowed, true);
        });
    }
}
