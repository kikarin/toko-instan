<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\StoreService;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $tickets,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        return Inertia::render('Support/Index', [
            'tickets' => $this->tickets->listForUser($request->user(), $store),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:4000'],
            'priority' => ['nullable', 'in:low,normal,high'],
        ]);
        $store = $this->storeService->getActiveStore($request->user()->id);
        $this->tickets->create($request->user(), $validated['subject'], $validated['body'], $store, $validated['priority'] ?? 'normal');

        return back()->with('success', 'Tiket dibuat.');
    }

    public function show(Request $request, int $id): Response
    {
        $ticket = $this->owned($request, $id);

        return Inertia::render('Support/Show', [
            'ticket' => $this->tickets->detail($ticket),
        ]);
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:4000']]);
        $this->tickets->reply($this->owned($request, $id), $request->user(), $validated['body']);

        return back()->with('success', 'Balasan terkirim.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:open,answered,closed']]);
        $this->tickets->setStatus($this->owned($request, $id), $validated['status'], $request->user());

        return back()->with('success', 'Status tiket diubah.');
    }

    protected function owned(Request $request, int $id): SupportTicket
    {
        $user = $request->user();
        $ticket = SupportTicket::query()->findOrFail($id);
        $store = $this->storeService->getActiveStore($user->id);

        if ($user->isAdmin()) {
            return $ticket;
        }
        if ($ticket->user_id === $user->id) {
            return $ticket;
        }
        if ($user->isSeller() && $store && (int) $ticket->tenant_id === (int) $store->tenant_id) {
            return $ticket;
        }

        abort(403);
    }
}
