<?php

namespace App\Http\Controllers;

use App\DTO\CreateOrderDTO;
use App\Enums\PaymentProvider;
use App\Services\AddressService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected AddressService $addressService,
        protected PaymentService $paymentService,
    ) {}

    public function show(string $storeSlug, Request $request): Response
    {
        $addresses = [];
        if ($user = $request->user()) {
            $addresses = $this->addressService->getAddressesForUser($user->id);
        }

        return Inertia::render('Checkout', [
            'addresses' => $addresses,
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransIsProduction' => (bool) config('services.midtrans.is_production'),
        ]);
    }

    public function store(string $storeSlug, Request $request): RedirectResponse
    {
        $dto = CreateOrderDTO::fromRequest($request);

        try {
            [$order, $payment] = DB::transaction(function () use ($dto) {
                $order = $this->orderService->processCheckout($dto);
                $payment = $this->paymentService->initiate($order, $dto->paymentMethod);

                return [$order, $payment];
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'checkout' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('orders.success', [
                'store_slug' => $storeSlug,
                'orderNumber' => $order->order_number,
            ])
            ->with('payment', [
                'id' => $payment->id,
                'provider' => $payment->provider->value,
                'method' => $payment->method->value,
                'status' => $payment->status->value,
                'snap_token' => $payment->snap_token,
                'redirect_url' => $payment->redirect_url,
            ]);
    }

    public function success(string $storeSlug, string $orderNumber, Request $request): Response
    {
        $invoice = $this->orderService->getInvoiceData($orderNumber);

        if (! $invoice) {
            abort(404, 'Pesanan tidak ditemukan');
        }

        $order = $this->orderService->getOrderWithTenant($orderNumber);
        $payment = $order ? $this->paymentService->latestForOrder($order) : null;

        if ($order && $payment?->provider === PaymentProvider::Midtrans && ! $payment->isPaid()) {
            try {
                $payment = $this->paymentService->syncMidtransStatus($order);
                $invoice = $this->orderService->getInvoiceData($orderNumber) ?? $invoice;
            } catch (\Throwable) {
                // Webhook/API may lag; page still renders pending with a pay button.
            }
        }

        $paymentPayload = $payment ? [
            'id' => $payment->id,
            'provider' => $payment->provider->value,
            'method' => $payment->method->value,
            'status' => $payment->status->value,
            'snap_token' => $payment->snap_token,
            'redirect_url' => $payment->redirect_url,
            'proof_path' => $payment->proof_path,
            'proof_name' => $payment->proof_name,
        ] : session('payment');

        return Inertia::render('OrderSuccess', [
            'invoice' => $invoice,
            'payment' => $paymentPayload,
            'storeSlug' => $storeSlug,
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransSnapUrl' => config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }
}
