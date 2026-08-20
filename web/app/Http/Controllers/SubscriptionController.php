<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Plan;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $request->user()->primaryTenant;
        abort_unless($tenant, 404, 'Tenant tidak ditemukan.');

        $payment = SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', PaymentStatus::Pending)
            ->latest('id')
            ->first();

        if ($payment && ! $this->subscriptionService->isPremium($tenant)) {
            try {
                $payment = $this->subscriptionService->syncPayment($payment);
            } catch (\Throwable) {
                // Midtrans may lag; page still shows pay button.
            }
        }

        $plans = Plan::query()->where('is_active', true)->orderBy('price')->get()->map(fn (Plan $plan) => [
            'code' => $plan->code,
            'name' => $plan->name,
            'price' => (int) $plan->price,
            'price_label' => $plan->price > 0
                ? 'Rp '.number_format((int) $plan->price, 0, ',', '.').'/bulan'
                : 'Gratis',
            'withdraw_fee' => (int) $plan->withdraw_fee,
            'settlement_mode' => $plan->settlement_mode,
            'features' => $plan->code === 'premium'
                ? [
                    'Dana order langsung ke saldo tersedia (skip escrow)',
                    'Withdraw tanpa biaya Rp5.000',
                    'Berlaku 30 hari per pembayaran',
                ]
                : [
                    'Escrow sampai order selesai',
                    'Biaya withdraw Rp5.000 / request',
                ],
        ]);

        return Inertia::render('Subscription/Index', [
            'subscription' => $this->subscriptionService->summaryFor($tenant->fresh()),
            'plans' => $plans,
            'payment' => $payment && $payment->status === PaymentStatus::Pending ? [
                'id' => $payment->id,
                'status' => $payment->status->value,
                'snap_token' => $payment->snap_token,
                'redirect_url' => $payment->redirect_url,
                'amount' => (int) $payment->amount,
            ] : null,
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransSnapUrl' => config('services.midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }

    public function upgrade(Request $request): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant;
        abort_unless($tenant, 404, 'Tenant tidak ditemukan.');

        try {
            $this->subscriptionService->initiateUpgrade($tenant);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Lanjutkan pembayaran Premium di Midtrans.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant;
        abort_unless($tenant, 404, 'Tenant tidak ditemukan.');

        $payment = SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', PaymentStatus::Pending)
            ->latest('id')
            ->first();

        if (! $payment) {
            return back()->with('success', 'Tidak ada pembayaran langganan yang menunggu.');
        }

        try {
            $this->subscriptionService->syncPayment($payment);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status langganan diperbarui.');
    }
}
