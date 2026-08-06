<?php

namespace App\Http\Controllers;

use App\Services\MarketplaceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function __construct(
        protected MarketplaceService $marketplaceService
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $category = $request->query('category');

        return Inertia::render('Marketplace', $this->marketplaceService->getMarketplaceData($search, $category));
    }
}
