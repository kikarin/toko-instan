<?php

use App\Services\Payments\SimulatedPaymentGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | The active gateway used to create payments. Swap to "midtrans" or
    | "xendit" once those providers are implemented.
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'simulated'),

    'gateways' => [
        'simulated' => [
            'driver' => SimulatedPaymentGateway::class,
        ],
    ],

    'simulated' => [
        'auto_confirm' => true,
    ],

];
