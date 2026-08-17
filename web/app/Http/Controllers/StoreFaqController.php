<?php

namespace App\Http\Controllers;

use App\Services\StoreFaqService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreFaqController extends Controller
{
    public function __construct(
        protected StoreFaqService $faqs,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        return Inertia::render('Faqs/Index', [
            'faqs' => $store ? $this->faqs->listForStore($store) : [],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        abort_unless($store, 404);
        $this->faqs->generateFromProducts($store);

        return back()->with('success', 'FAQ digenerate dari produk.');
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        abort_unless($store, 404);
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:200'],
            'answer' => ['required', 'string', 'max:2000'],
        ]);
        $this->faqs->storeManual($store, $validated['question'], $validated['answer']);

        return back()->with('success', 'FAQ ditambah.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id);
        abort_unless($store, 404);
        $this->faqs->delete($store, $id);

        return back()->with('success', 'FAQ dihapus.');
    }
}
