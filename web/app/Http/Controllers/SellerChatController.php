<?php

namespace App\Http\Controllers;

use App\Services\StoreChatService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerChatController extends Controller
{
    public function __construct(
        protected StoreChatService $chatService,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        return Inertia::render('Chats/Index', [
            'chats' => $store ? $this->chatService->listForStore($store) : [],
        ]);
    }
}
