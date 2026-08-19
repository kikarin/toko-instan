<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process';

    protected $description = 'Expire overdue Premium subscriptions and send renewal reminders';

    public function handle(SubscriptionService $subscriptions): int
    {
        $reminded = $subscriptions->notifyUpcomingRenewals();
        $expired = $subscriptions->expireOverdue();

        $this->info("Renewal reminders: {$reminded}. Expired: {$expired}.");

        return self::SUCCESS;
    }
}
