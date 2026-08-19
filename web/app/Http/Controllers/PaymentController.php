<?php

namespace App\Http\Controllers;

use App\Actions\UploadDigitalProductFile;
use App\Enums\PaymentProvider;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected StoreService $storeService,
        protected UploadDigitalProductFile $uploadDigitalProductFile,
    ) {}

    public function uploadProof(Request $request, string $storeSlug, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $payment = $this->findBuyerPayment($request, $storeSlug, $orderNumber);

        try {
            $uploaded = ($this->uploadDigitalProductFile)($validated['proof'], 'payment-proofs');
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['proof' => $e->getMessage()]);
        }

        $this->paymentService->attachTransferProof($payment, $uploaded['path'], $uploaded['name']);

        return back()->with('success', 'Bukti transfer berhasil diunggah. Menunggu konfirmasi seller.');
    }

    public function confirm(Request $request, int $paymentId): RedirectResponse
    {
        $payment = Payment::query()->with('order.store.tenant')->findOrFail($paymentId);
        $user = $request->user();
        $store = $this->storeService->getStoreForUser($user->id);

        if (! $store || $payment->order?->store_id !== $store->id) {
            abort(403);
        }

        if (! in_array($payment->provider, [PaymentProvider::Manual, PaymentProvider::Cod], true)) {
            abort(403);
        }

        $this->paymentService->confirmManualOrCod($payment);

        return back()->with('success', 'Pembayaran dikonfirmasi. Order ditandai Paid.');
    }

    public function sync(Request $request, string $storeSlug, string $orderNumber): RedirectResponse
    {
        $payment = $this->findBuyerPayment($request, $storeSlug, $orderNumber);

        if ($payment->provider !== PaymentProvider::Midtrans) {
            return back();
        }

        $order = $payment->order()->with('store')->firstOrFail();

        try {
            $this->paymentService->syncMidtransStatus($order);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    public function clientConfig(): JsonResponse
    {
        return response()->json([
            'client_key' => config('services.midtrans.client_key'),
            'is_production' => (bool) config('services.midtrans.is_production'),
            'snap_url' => config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }

    protected function findBuyerPayment(Request $request, string $storeSlug, string $orderNumber): Payment
    {
        $payment = Payment::query()
            ->whereHas('order', function ($q) use ($orderNumber, $storeSlug, $request) {
                $q->where('order_number', $orderNumber)
                    ->where('customer_email', $request->user()->email)
                    ->whereHas('store', fn ($sq) => $sq->where('slug', $storeSlug));
            })
            ->latest('id')
            ->firstOrFail();

        return $payment;
    }
}
