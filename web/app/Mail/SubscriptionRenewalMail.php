<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Langganan Premium hampir berakhir',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.subscription-renewal',
            with: [
                'endsAt' => $this->subscription->ends_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
                'renewUrl' => url('/subscription'),
            ],
        );
    }
}
