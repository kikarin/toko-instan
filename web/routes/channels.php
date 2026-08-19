<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('seller.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId && in_array($user->role, ['seller', 'admin'], true);
});
