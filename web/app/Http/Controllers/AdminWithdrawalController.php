<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Services\WithdrawService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminWithdrawalController extends Controller
{
    public function __construct(
        protected WithdrawService $withdrawService
    ) {}

    public function index(Request $request): Response
    {
        $withdrawals = Withdrawal::with(['store', 'wallet.tenant'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'store_name' => $w->store->name ?? '—',
                'amount' => 'Rp '.number_format((float) $w->amount, 0, ',', '.'),
                'net_amount' => 'Rp '.number_format((float) $w->net_amount, 0, ',', '.'),
                'fee' => 'Rp '.number_format((float) $w->fee, 0, ',', '.'),
                'status' => $w->status,
                'bank_name' => $w->bank_name,
                'account_number' => $w->account_number,
                'account_name' => $w->account_name,
                'created_at' => $w->created_at?->format('d M Y, H:i'),
            ])
            ->values();

        return Inertia::render('Admin/Withdrawals', [
            'withdrawals' => $withdrawals,
        ]);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $this->withdrawService->approve(Withdrawal::findOrFail($id));

        return back()->with('success', 'Penarikan disetujui.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->withdrawService->reject(Withdrawal::findOrFail($id), $validated['reason']);

        return back()->with('success', 'Penarikan ditolak, dana dikembalikan.');
    }

    public function markTransferred(Request $request, int $id): RedirectResponse
    {
        $this->withdrawService->markTransferred(Withdrawal::findOrFail($id));

        return back()->with('success', 'Penarikan ditandai sudah ditransfer.');
    }
}
