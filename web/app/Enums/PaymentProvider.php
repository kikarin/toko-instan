<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Midtrans = 'midtrans';
    case Manual = 'manual';
    case Cod = 'cod';
}
