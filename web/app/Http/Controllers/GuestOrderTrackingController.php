<?php

namespace App\Http\Controllers;

use App\Services\OrderTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GuestOrderTrackingController extends Controller
{
    public function __construct(
        protected OrderTrackingService $orderTrackingService,
    ) {}

    public function show(string $storeSlug): Response
    {
        return Inertia::render('Orders/TrackLookup');
    }

    public function lookup(string $storeSlug, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $order = $this->orderTrackingService->findForGuestLookup(
            $storeSlug,
            $validated['order_number'],
            $validated['email'],
        );

        if ($order === null) {
            throw ValidationException::withMessages([
                'order_number' => 'Pesanan tidak ditemukan. Periksa nomor pesanan dan email.',
            ]);
        }

        return redirect()->to($this->orderTrackingService->signedUrl($order));
    }
}
