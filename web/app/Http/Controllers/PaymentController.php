<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected OrderService $orderService
    ) {}

    public function pay(string $orderNumber, Request $request): Response
    {
        $order = $this->orderService->getOrderWithTenant($orderNumber);

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan');
        }

        $this->authorizeOrderAccess($order, $request);

        return Inertia::render('Payment/Pay', [
            'invoice' => $this->orderService->getInvoiceData($orderNumber),
        ]);
    }

    public function confirm(string $orderNumber, Request $request): RedirectResponse
    {
        $order = $this->orderService->getOrderWithTenant($orderNumber);

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan');
        }

        $this->authorizeOrderAccess($order, $request);

        $this->paymentService->markPaid($order);

        return redirect()->route('orders.success', [
            'store_slug' => $order->store?->slug ?? '',
            'orderNumber' => $order->order_number,
        ]);
    }

    /**
     * Guests reach the payment page through the (unguessable) order number;
     * signed-in users must own the order or sell from its store.
     */
    protected function authorizeOrderAccess(mixed $order, Request $request): void
    {
        $user = $request->user();

        if (! $user || $user->role === 'admin') {
            return;
        }

        $isBuyer = $order->customer_email === $user->email;
        $isSeller = $order->store?->tenant?->user_id === $user->id;

        if (! $isBuyer && ! $isSeller) {
            abort(403, 'Akses ditolak');
        }
    }
}
