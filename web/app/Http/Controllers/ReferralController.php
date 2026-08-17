<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralService $referrals,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        $tenant = $store?->tenant;

        return Inertia::render('Referral/Index', [
            'summary' => $tenant ? $this->referrals->summary($tenant) : null,
        ]);
    }
}
