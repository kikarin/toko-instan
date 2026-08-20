<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pesanan '.$this->order->order_number.' Telah Dibayar - '.($this->order->store?->name ?? 'Toko Instan'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-confirmation',
            with: [
                'order' => $this->order,
            ],
        );
    }
}
