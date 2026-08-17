<?php

namespace App\Services;

use App\Contracts\DomainGateway;
use App\Models\Store;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CustomDomainService
{
    public function __construct(
        protected DomainGateway $gateway,
        protected SubscriptionService $subscriptions,
        protected ActivityLogService $activityLog,
    ) {}

    /**
     * @return array{custom_domain: ?string, custom_domain_status: ?string, is_premium: bool}
     */
    public function attach(Store $store, string $domain): array
    {
        $tenant = $store->tenant;
        if (! $tenant || ! $this->subscriptions->isPremium($tenant)) {
            throw ValidationException::withMessages([
                'custom_domain' => 'Custom domain hanya untuk paket Premium.',
            ]);
        }

        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        if (! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/', $domain)) {
            throw ValidationException::withMessages(['custom_domain' => 'Format domain tidak valid.']);
        }

        $taken = Store::query()
            ->where('custom_domain', $domain)
            ->whereKeyNot($store->id)
            ->exists();
        if ($taken) {
            throw ValidationException::withMessages(['custom_domain' => 'Domain sudah dipakai toko lain.']);
        }

        try {
            $result = $this->gateway->provisionHostname($domain);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['custom_domain' => $e->getMessage()]);
        }

        $store->update([
            'custom_domain' => $domain,
            'custom_domain_status' => $result['status'],
        ]);

        $this->activityLog->record('custom_domain', Store::class, $store->id, [
            'domain' => $domain,
            'status' => $result['status'],
        ]);

        return [
            'custom_domain' => $store->custom_domain,
            'custom_domain_status' => $store->custom_domain_status,
            'is_premium' => true,
            'message' => $result['message'],
        ];
    }

    public function detach(Store $store): void
    {
        $store->update([
            'custom_domain' => null,
            'custom_domain_status' => null,
        ]);
        $this->activityLog->record('custom_domain_removed', Store::class, $store->id);
    }
}
