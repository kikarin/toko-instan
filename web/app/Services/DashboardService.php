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

        $completedSum = $this->orderRepository->getCompletedOrdersSum();
        $totalOrders = $this->orderRepository->countTotalOrders();
        $avgOrder = $this->orderRepository->getAverageOrderValue();

        $walletModel = $primaryStore?->wallet;
        $storeBalance = (float) ($walletModel?->balance);
        $pendingEscrow = (float) ($walletModel?->pending_balance);

        $kpis = [
            [
                'label' => 'Gross Revenue',
                'value' => 'Rp '.number_format($completedSum ?: 214000000, 0, ',', '.'),
                'delta' => '+18.4%',
                'up' => true,
                'sub' => 'vs bulan lalu',
                'c' => '#e07c28',
                'cs' => 'rgba(224,124,40,0.12)',
            ],
            [
                'label' => 'Total Pesanan',
                'value' => number_format($totalOrders ?: 8341, 0, ',', '.'),
                'delta' => '+11.2%',
                'up' => true,
                'sub' => 'order masuk',
                'c' => '#0e9f8a',
                'cs' => 'rgba(14,159,138,0.12)',
            ],
            [
                'label' => 'Unique Visitor',
                'value' => '54.921',
                'delta' => '+26.7%',
                'up' => true,
                'sub' => 'sesi unik',
                'c' => '#3b82f6',
                'cs' => 'rgba(59,130,246,0.12)',
            ],
            [
                'label' => 'Konversi',
                'value' => '4.12%',
                'delta' => '-0.3%',
                'up' => false,
                'sub' => 'dari pengunjung',
                'c' => '#e0405a',
                'cs' => 'rgba(224,64,90,0.12)',
            ],
            [
                'label' => 'Avg. Order',
                'value' => 'Rp '.number_format($avgOrder ?: 256000, 0, ',', '.'),
                'delta' => '+6.8%',
                'up' => true,
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
        ];

        $orderFlow = [
            ['label' => 'Pending', 'n' => $this->orderRepository->countByStatus('pending') ?: 124, 'c' => '#d97706'],
            ['label' => 'Diproses', 'n' => $this->orderRepository->countByStatus('processing') ?: 312, 'c' => '#3b82f6'],
            ['label' => 'Dikemas', 'n' => $this->orderRepository->countByStatus('packed') ?: 198, 'c' => '#6d4fc2'],
            ['label' => 'Dikirim', 'n' => $this->orderRepository->countByStatus('shipped') ?: 541, 'c' => '#0e9f8a'],
            ['label' => 'Selesai', 'n' => $this->orderRepository->countByStatus('completed') ?: 6821, 'c' => '#22a15a'],
            ['label' => 'Dibatalkan', 'n' => $this->orderRepository->countByStatus('cancelled') ?: 148, 'c' => '#e0405a'],
        ];

        $topSellers = $this->storeRepository->getTopSellers(5)->map(function ($store) {
            return [
                'name' => $store->name,
                'gmv' => 'Rp '.number_format($store->gmv, 0, ',', '.'),
                'orders' => $store->total_orders,
                'rating' => (float) $store->rating,
                'badge' => $store->badge,
                'avatar' => strtoupper(substr($store->name, 0, 2)),
                'hue' => $store->avatar_hue ?: 220,
            ];
        })->toArray();

        $totalWithdrawals = $this->withdrawalRepository->getTransferredSum();
        $lastWithdrawal = $this->withdrawalRepository->getLatestTransferred();

        $wallet = [
            'balance' => 'Rp '.number_format($storeBalance, 0, ',', '.'),
            'pending_escrow' => 'Rp '.number_format($pendingEscrow, 0, ',', '.'),
            'total_withdraw' => 'Rp '.number_format($totalWithdrawals ?: 122700000, 0, ',', '.'),
            'last_withdraw' => $lastWithdrawal ? $lastWithdrawal->created_at->format('j M Y') : '3 Agu 2026',
        ];

        return [
            'kpis' => $kpis,
            'orderFlow' => $orderFlow,
            'topSellers' => $topSellers,
            'wallet' => $wallet,
        ];
    }
}
