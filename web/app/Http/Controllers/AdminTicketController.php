<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminTicketController extends Controller
{
    public function __construct(protected SupportTicketService $tickets) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Tickets', [
            'tickets' => $this->tickets->listForUser($request->user()),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $ticket = SupportTicket::query()->findOrFail($id);

        return Inertia::render('Admin/Tickets', [
            'ticket' => $this->tickets->detail($ticket),
        ]);
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:4000']]);
        $this->tickets->reply(SupportTicket::query()->findOrFail($id), $request->user(), $validated['body']);

        return back()->with('success', 'Balasan terkirim.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:open,answered,closed']]);
        $this->tickets->setStatus(SupportTicket::query()->findOrFail($id), $validated['status'], $request->user());

        return back()->with('success', 'Status diperbarui.');
    }
}
