<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;

class AdminService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected StoreRepository $storeRepository,
        protected ProductRepository $productRepository,
        protected OrderRepository $orderRepository
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
                'users' => User::count(),
                'stores' => $this->storeRepository->countActiveStores(),
                'products' => $this->productRepository->countTotalProducts(),
                'orders' => $this->orderRepository->countTotalOrders(),
                'revenue' => 'Rp '.number_format($this->orderRepository->getCompletedOrdersSum() ?: 0, 0, ',', '.'),
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

    public function setRole(User $user, string $role): void
    {
        $user->update(['role' => $role]);
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
