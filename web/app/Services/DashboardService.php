<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\StoreRepository;
use App\Repositories\WithdrawalRepository;

class DashboardService
{
    public function __construct(
        protected StoreRepository $storeRepository,
        protected OrderRepository $orderRepository,
        protected WithdrawalRepository $withdrawalRepository
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(int $userId): array
    {
        $primaryStore = $this->storeRepository->getStoreForUser($userId);
        $storeId = $primaryStore?->id;

        $completedSum = $this->orderRepository->getCompletedOrdersSum($storeId);
        $totalOrders = $this->orderRepository->countTotalOrders($storeId);
        $avgOrder = $this->orderRepository->getAverageOrderValue($storeId);
        $productCount = $storeId
            ? $primaryStore->products()->count()
            : 0;

        $walletModel = $primaryStore?->wallet;
        $storeBalance = (float) ($walletModel?->balance ?? 0);
        $pendingEscrow = (float) ($walletModel?->pending_balance ?? 0);

        $kpis = [
            [
                'label' => 'Gross Revenue',
                'value' => 'Rp '.number_format($completedSum, 0, ',', '.'),
                'delta' => 'live',
                'up' => null,
                'sub' => 'order selesai',
                'c' => '#e07c28',
                'cs' => 'rgba(224,124,40,0.12)',
            ],
            [
                'label' => 'Total Pesanan',
                'value' => number_format($totalOrders, 0, ',', '.'),
                'delta' => 'live',
                'up' => null,
                'sub' => 'semua status',
                'c' => '#0e9f8a',
                'cs' => 'rgba(14,159,138,0.12)',
            ],
            [
                'label' => 'Total Produk',
                'value' => number_format($productCount, 0, ',', '.'),
                'delta' => 'live',
                'up' => null,
                'sub' => 'katalog toko',
                'c' => '#3b82f6',
                'cs' => 'rgba(59,130,246,0.12)',
            ],
            [
                'label' => 'Avg. Order',
                'value' => 'Rp '.number_format($avgOrder, 0, ',', '.'),
                'delta' => 'live',
                'up' => null,
                'sub' => 'per transaksi',
                'c' => '#6d4fc2',
                'cs' => 'rgba(109,79,194,0.12)',
            ],
            [
                'label' => 'Saldo Dompet',
                'value' => 'Rp '.number_format($storeBalance, 0, ',', '.'),
                'delta' => 'live',
                'up' => null,
                'sub' => 'siap tarik',
                'c' => '#22a15a',
                'cs' => 'rgba(34,161,90,0.12)',
            ],
            [
                'label' => 'Escrow Pending',
                'value' => 'Rp '.number_format($pendingEscrow, 0, ',', '.'),
                'delta' => 'live',
                'up' => null,
                'sub' => 'menunggu settle',
                'c' => '#d97706',
                'cs' => 'rgba(217,119,6,0.12)',
            ],
        ];

        $orderFlow = [
            ['label' => 'Pending', 'n' => $this->orderRepository->countByStatus('pending', $storeId), 'c' => '#d97706'],
            ['label' => 'Diproses', 'n' => $this->orderRepository->countByStatus('processing', $storeId), 'c' => '#3b82f6'],
            ['label' => 'Dikemas', 'n' => $this->orderRepository->countByStatus('packed', $storeId), 'c' => '#6d4fc2'],
            ['label' => 'Dikirim', 'n' => $this->orderRepository->countByStatus('shipped', $storeId), 'c' => '#0e9f8a'],
            ['label' => 'Selesai', 'n' => $this->orderRepository->countByStatus('completed', $storeId), 'c' => '#22a15a'],
            ['label' => 'Dibatalkan', 'n' => $this->orderRepository->countByStatus('cancelled', $storeId), 'c' => '#e0405a'],
        ];

        $topSellers = [];

        $totalWithdrawals = $this->withdrawalRepository->getTransferredSumForStore($storeId);
        $lastWithdrawal = $this->withdrawalRepository->getLatestTransferredForStore($storeId);

        $wallet = [
            'balance' => 'Rp '.number_format($storeBalance, 0, ',', '.'),
            'pending_escrow' => 'Rp '.number_format($pendingEscrow, 0, ',', '.'),
            'total_withdraw' => 'Rp '.number_format($totalWithdrawals, 0, ',', '.'),
            'last_withdraw' => $lastWithdrawal?->created_at?->format('j M Y') ?? '—',
        ];

        return [
            'kpis' => $kpis,
            'orderFlow' => $orderFlow,
            'topSellers' => $topSellers,
            'wallet' => $wallet,
            'store' => $primaryStore ? [
                'id' => $primaryStore->id,
                'name' => $primaryStore->name,
                'slug' => $primaryStore->slug,
            ] : null,
        ];
    }
}
