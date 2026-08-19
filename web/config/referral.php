<?php

return [
    'commission_rate' => (float) env('REFERRAL_COMMISSION_RATE', 0.05),
    'commission_max' => (int) env('REFERRAL_COMMISSION_MAX', 50000),
    'cookie' => 'ref_code',
    'cookie_days' => 30,
];
