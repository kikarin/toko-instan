<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SellerNotified implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array{title: string, body: string, url?: string|null}  $payload
     */
    public function __construct(
        public int $userId,
        public array $payload,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('seller.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'seller.notified';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
