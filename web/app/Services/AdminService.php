<?php

namespace App\Services;

use App\DTO\Admin\UpdateRoleDTO;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use App\Repositories\WithdrawalRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class AdminService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected StoreRepository $storeRepository,
        protected ProductRepository $productRepository,
        protected OrderRepository $orderRepository,
        protected WithdrawalRepository $withdrawalRepository
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $recentOrders = $this->orderRepository->getRecent(5)
            ->map(fn ($order) => [
                'order_number' => $order->order_number,
                'store_name' => $order->store->name ?? 'Official Store',
                'total_amount' => 'Rp '.number_format($order->total_amount, 0, ',', '.'),
                'status' => ucfirst($order->status),
                'created_at' => $order->created_at?->format('d M Y, H:i'),
            ])
            ->values()
            ->toArray();

        $recentUsers = $this->userRepository->getLatest(5)
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ])
            ->values()
            ->toArray();

        $recentStores = $this->storeRepository->getLatest(5)
            ->map(fn ($store) => [
                'id' => $store->id,
                'name' => $store->name,
                'category' => $store->category,
            ])
            ->values()
            ->toArray();

        return [
            'stats' => [
                'users' => $this->userRepository->countAll(),
                'stores' => $this->storeRepository->countActiveStores(),
                'tenants' => Tenant::count(),
                'products' => $this->productRepository->countTotalProducts(),
                'orders' => $this->orderRepository->countTotalOrders(),
                'revenue' => 'Rp '.number_format($this->orderRepository->getCompletedOrdersSum(), 0, ',', '.'),
                'pending_withdrawals' => $this->withdrawalRepository->countPending(),
                'pending_withdrawals_sum' => 'Rp '.number_format($this->withdrawalRepository->getPendingSum(), 0, ',', '.'),
            ],
            'recent_orders' => $recentOrders,
            'recent_users' => $recentUsers,
            'recent_stores' => $recentStores,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function listUsers(): Collection
    {
        return $this->userRepository->getLatest(100);
    }

    /**
     * @return SupportCollection<int, array<string, mixed>>
     */
    public function listTenants(): SupportCollection
    {
        return Tenant::query()
            ->with(['user:id,name,email', 'stores:id,tenant_id,name,slug,is_active'])
            ->orderByDesc('created_at')
            ->take(100)
            ->get()
            ->map(function (Tenant $tenant) {
                $store = $tenant->stores->first();

                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'plan' => $tenant->plan,
                    'status' => $tenant->status,
                    'owner' => $tenant->user?->name,
                    'owner_email' => $tenant->user?->email,
                    'store_name' => $store?->name,
                    'store_slug' => $store?->slug,
                    'store_active' => (bool) ($store?->is_active ?? false),
                    'created_at' => $tenant->created_at?->format('d M Y'),
                ];
            });
    }

    /**
     * @return SupportCollection<int, array<string, mixed>>
     */
    public function listOrders(): SupportCollection
    {
        return Order::query()
            ->with('store:id,name,slug')
            ->orderByDesc('created_at')
            ->take(100)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'store_name' => $order->store?->name ?? '—',
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'total_amount' => 'Rp '.number_format((float) $order->total_amount, 0, ',', '.'),
                'status' => $order->status,
                'created_at' => $order->created_at?->format('d M Y H:i'),
            ]);
    }

    public function getUser(int $id): User
    {
        return $this->userRepository->findOrFail($id);
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function setRole(User $user, UpdateRoleDTO $dto): void
    {
        $user->update(['role' => $dto->role]);
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
