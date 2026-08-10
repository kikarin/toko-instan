<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected StoreService $storeService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $store = $this->storeService->getStoreForUser($user->id);
        $storeId = $store?->id;

        $customers = $storeId ? $this->customerService->getCustomersByStore($storeId) : [];

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'storeName' => $store?->name ?? 'Toko Anda',
        ]);
    }
}
