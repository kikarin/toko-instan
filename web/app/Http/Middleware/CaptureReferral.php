<?php

namespace App\Http\Middleware;

use App\Services\ReferralService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferral
{
    public function __construct(protected ReferralService $referrals) {}

    public function handle(Request $request, Closure $next): Response
    {
        $code = strtoupper(trim((string) $request->query('ref', '')));
        if ($code !== '') {
            $this->referrals->trackClick($code, $request->ip(), (string) $request->userAgent());
            $response = $next($request);

            return $response->cookie(
                config('referral.cookie', 'ref_code'),
                $code,
                (int) config('referral.cookie_days', 30) * 1440
            );
        }

        return $next($request);
    }
}
