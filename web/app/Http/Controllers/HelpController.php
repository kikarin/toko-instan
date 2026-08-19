<?php

namespace App\Http\Controllers;

use App\Services\StoreFaqService;
use App\Services\StoreService;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HelpController extends Controller
{
    public function __construct(
        protected SupportTicketService $support,
        protected StoreFaqService $faqs,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        return Inertia::render('Help/Index', [
            'articles' => $this->support->knowledge(),
            'store_faqs' => $store ? $this->faqs->listForStore($store) : [],
        ]);
    }
}
