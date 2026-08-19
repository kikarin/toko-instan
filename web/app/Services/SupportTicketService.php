<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use App\Models\Store;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketService
{
    public function __construct(protected ActivityLogService $activityLog) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(User $user, ?Store $store = null): array
    {
        $query = SupportTicket::query()->with('user:id,name')->latest('id');

        if ($user->isAdmin()) {
            // all
        } elseif ($user->isSeller() && $store) {
            $query->where(function ($q) use ($user, $store) {
                $q->where('user_id', $user->id)
                    ->orWhere('tenant_id', $store->tenant_id);
            });
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->limit(50)->get()->map(fn (SupportTicket $t) => $this->format($t))->all();
    }

    public function create(User $user, string $subject, string $body, ?Store $store = null, string $priority = 'normal'): SupportTicket
    {
        $ticket = SupportTicket::query()->create([
            'tenant_id' => $store?->tenant_id,
            'store_id' => $store?->id,
            'user_id' => $user->id,
            'subject' => $subject,
            'body' => $body,
            'status' => 'open',
            'priority' => $priority,
        ]);

        $this->activityLog->record('ticket_created', SupportTicket::class, $ticket->id, [
            'subject' => $subject,
        ], $user);

        return $ticket;
    }

    public function reply(SupportTicket $ticket, User $user, string $body): void
    {
        $ticket->replies()->create([
            'user_id' => $user->id,
            'body' => $body,
            'is_staff' => $user->isAdmin() || $user->isSeller(),
        ]);

        $ticket->update([
            'status' => $user->isAdmin() ? 'answered' : 'open',
        ]);

        $this->activityLog->record('ticket_replied', SupportTicket::class, $ticket->id, [], $user);
    }

    public function setStatus(SupportTicket $ticket, string $status, User $user): void
    {
        $ticket->update(['status' => $status]);
        $this->activityLog->record('ticket_status', SupportTicket::class, $ticket->id, ['status' => $status], $user);
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(SupportTicket $ticket): array
    {
        $ticket->load(['user:id,name', 'replies.user:id,name']);

        return [
            ...$this->format($ticket),
            'replies' => $ticket->replies->map(fn ($r) => [
                'id' => $r->id,
                'body' => $r->body,
                'is_staff' => $r->is_staff,
                'author' => $r->user?->name,
                'created_at' => $r->created_at?->format('d M Y H:i'),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function format(SupportTicket $t): array
    {
        return [
            'id' => $t->id,
            'subject' => $t->subject,
            'body' => $t->body,
            'status' => $t->status,
            'priority' => $t->priority,
            'author' => $t->user?->name,
            'created_at' => $t->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * @return list<array{slug: string, title: string, category: string, body: string}>
     */
    public function knowledge(?string $slug = null): array
    {
        $this->seedIfEmpty();

        $query = KnowledgeArticle::query()->where('is_published', true)->orderBy('category')->orderBy('title');
        if ($slug) {
            $query->where('slug', $slug);
        }

        return $query->get(['slug', 'title', 'category', 'body'])->toArray();
    }

    public function seedIfEmpty(): void
    {
        if (KnowledgeArticle::query()->exists()) {
            return;
        }

        $rows = [
            ['slug' => 'mulai-toko', 'title' => 'Mulai jualan di Toko Instan', 'category' => 'Memulai', 'body' => 'Daftar sebagai seller, lengkapi pengaturan toko, unggah produk, lalu bagikan tautan storefront.'],
            ['slug' => 'pembayaran', 'title' => 'Cara pembayaran & Midtrans', 'category' => 'Pembayaran', 'body' => 'Checkout mendukung QRIS, VA, e-wallet via Midtrans, plus transfer manual dan COD.'],
            ['slug' => 'withdraw', 'title' => 'Penarikan saldo', 'category' => 'Dompet', 'body' => 'Saldo bisa ditarik dari menu Dompet. Seller free dikenai biaya Rp5.000. Premium tanpa biaya.'],
            ['slug' => 'seo-blog', 'title' => 'SEO, blog, dan sitemap', 'category' => 'Marketing', 'body' => 'Isi meta produk, tulis blog, lalu cek /{slug}/sitemap.xml agar mesin cari menemukannya.'],
            ['slug' => 'api-key', 'title' => 'API publik', 'category' => 'Developer', 'body' => 'Buat bearer token di menu API. Dokumentasi Swagger ada di /api/docs.'],
        ];

        foreach ($rows as $row) {
            KnowledgeArticle::query()->create($row);
        }
    }
}
