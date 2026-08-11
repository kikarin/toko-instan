<?php

namespace App\Http\Controllers;

use App\DTO\CreateOrderDTO;
use App\Services\AddressService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected AddressService $addressService
    ) {}

    public function show(string $storeSlug, Request $request): Response
    {
        $addresses = [];
        if ($user = $request->user()) {
            $addresses = $this->addressService->getAddressesForUser($user->id);
        }

        return Inertia::render('Checkout', [
            'addresses' => $addresses,
        ]);
    }

    public function store(string $storeSlug, Request $request): RedirectResponse
    {
        $dto = CreateOrderDTO::fromRequest($request);

        try {
            $order = $this->orderService->processCheckout($dto);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()->route('orders.success', ['store_slug' => $storeSlug, 'orderNumber' => $order->order_number]);
    }

    public function success(string $storeSlug, string $orderNumber): Response
    {
        $invoice = $this->orderService->getInvoiceData($orderNumber);

        if (! $invoice) {
            abort(404, 'Pesanan tidak ditemukan');
        }

        return Inertia::render('OrderSuccess', [
            'invoice' => $invoice,
        ]);
    }
}
