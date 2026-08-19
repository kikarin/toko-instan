<?php

namespace App\Http\Controllers;

use App\DTO\Wallet\WithdrawalRequestDTO;
use App\Repositories\WithdrawalRepository;
use App\Services\SubscriptionService;
use App\Services\WalletService;
use App\Services\WithdrawService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WithdrawalController extends Controller
{
    public function __construct(
        protected WithdrawService $withdrawService,
        protected WithdrawalRepository $withdrawalRepository,
        protected WalletService $walletService,
        protected SubscriptionService $subscriptionService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $tenant = $user->primaryTenant;
        $wallet = $tenant?->wallet;

        $withdrawals = $tenant
            ? $this->withdrawalRepository->getWithdrawalsByTenantPaginated($tenant->id, 5, $request->integer('wpage', 1))
            : null;

        $transactions = $wallet
            ? $this->walletService->getTransactionsPaginated($wallet->id, 10, $request->integer('page', 1))
            : null;

        $plan = $tenant ? $this->subscriptionService->summaryFor($tenant) : null;

        return Inertia::render('Wallet/Index', [
            'wallet' => $wallet ? [
                'balance' => 'Rp '.number_format((float) $wallet->balance, 0, ',', '.'),
                'pending_balance' => 'Rp '.number_format((float) $wallet->pending_balance, 0, ',', '.'),
            ] : null,
            'plan' => $plan,
            'withdrawals' => $withdrawals ? [
                'data' => $withdrawals->map(fn ($w) => [
                    'id' => $w->id,
                    'amount' => 'Rp '.number_format((float) $w->amount, 0, ',', '.'),
                    'net_amount' => 'Rp '.number_format((float) $w->net_amount, 0, ',', '.'),
                    'fee' => 'Rp '.number_format((float) $w->fee, 0, ',', '.'),
                    'status' => $w->status,
                    'bank_name' => $w->bank_name,
                    'account_number' => $w->account_number,
                    'account_name' => $w->account_name,
                    'created_at' => $w->created_at?->format('d M Y, H:i'),
                ])->values(),
                'pagination' => $this->paginationMeta($withdrawals),
            ] : null,
            'transactions' => $transactions ? [
                'data' => $transactions->map(fn ($t) => [
                    'id' => $t->id,
                    'type' => $t->type,
                    'direction' => $t->direction,
                    'amount' => 'Rp '.number_format((float) $t->amount, 0, ',', '.'),
                    'balance_after' => 'Rp '.number_format((float) $t->balance_after, 0, ',', '.'),
                    'pending_after' => 'Rp '.number_format((float) $t->pending_after, 0, ',', '.'),
                    'description' => $t->description,
                    'created_at' => $t->created_at->format('d M Y, H:i'),
                ])->values(),
                'pagination' => $this->paginationMeta($transactions),
            ] : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dto = WithdrawalRequestDTO::fromRequest($request);

        $user = $request->user();
        $wallet = $user->primaryTenant?->wallet;

        if (! $wallet) {
            return back()->withErrors(['amount' => 'Wallet tidak ditemukan.']);
        }

        $storeId = $user->store?->id;

        try {
            $this->withdrawService->request(
                $wallet,
                $dto->amount,
                $dto->toArray(),
                $storeId
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Permintaan penarikan diajukan.');
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return array{current_page: int, last_page: int, total: int, per_page: int}
     */
    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
        ];
    }
}
