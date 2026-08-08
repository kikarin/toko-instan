<?php

namespace App\Http\Controllers;

use App\DTO\CreateOrderDTO;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function show(Request $request): Response
    {
        return Inertia::render('Checkout');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string'],
            'shipping_courier' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $dto = CreateOrderDTO::fromRequest($validated);
        $order = $this->orderService->processCheckout($dto);

        return redirect()->route('orders.success', $order->order_number);
    }

    public function success(string $orderNumber): Response
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
