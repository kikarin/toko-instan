<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->handleMidtransWebhook($request);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Webhook gagal diproses.'], 500);
        }

        $status = $payment->status;

        return response()->json([
            'ok' => true,
            'payment_id' => $payment->id,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
        ]);
    }
}
