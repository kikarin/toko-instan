<?php

namespace App\Mail;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Withdrawal $withdrawal) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Penarikan saldo disetujui',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.withdrawal-approved',
            with: [
                'amount' => 'Rp '.number_format((float) $this->withdrawal->net_amount, 0, ',', '.'),
                'url' => url('/wallet'),
            ],
        );
    }
}
