<?php

namespace App\Observers;

use App\Models\Order;
use App\Observers\Concerns\RecordsActivity;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    use RecordsActivity;

    public function updated(Order $order): void
    {
        // Only record when an authenticated user (e.g. seller/admin) drives the
        // change. Automated flows (payment webhooks, checkout persistence)
        // update orders without a user and would flood the log otherwise.
        if (! Auth::check()) {
            return;
        }

        $changes = $order->getChanges();
        unset($changes['updated_at']);

        if ($changes === [] || ! isset($changes['status'])) {
            return;
        }

        $this->logActivity('order_status', $order, [
            'order_number' => $order->order_number,
            'from' => $order->getOriginal('status'),
            'to' => $changes['status'],
        ]);
    }
}
