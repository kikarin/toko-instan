<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\OrderTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestOrderReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan '.$this->order->order_number.' Diterima - '.($this->order->store?->name ?? 'Toko Instan'),
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing(['items', 'store']);

        return new Content(
            markdown: 'mail.guest-order-receipt',
            with: [
                'order' => $this->order,
                'trackingUrl' => app(OrderTrackingService::class)->signedUrl($this->order),
            ],
        );
    }
}
