<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebaseAuthService
{
    private const JWKS_URL = 'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com';

    /**
     * Verify a Firebase ID token and return its trusted claims.
     *
     * @return array{uid: string, email: string|null, name: string|null}
     */
    public function verifyIdToken(string $idToken): array
    {
        $projectId = (string) config('services.firebase.project_id');

        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID tidak dikonfigurasi.');
        }

        try {
            $payload = JWT::decode($idToken, $this->keys());
        } catch (\Throwable $e) {
            throw new RuntimeException('Token Google tidak valid.', 0, $e);
        }

        if (($payload->iss ?? null) !== 'https://securetoken.google.com/'.$projectId) {
            throw new RuntimeException('Issuer token tidak valid.');
        }

        if (($payload->aud ?? null) !== $projectId) {
            throw new RuntimeException('Audience token tidak valid.');
        }

        if (! is_string($payload->sub ?? null) || $payload->sub === '') {
            throw new RuntimeException('UID token tidak valid.');
        }

        return [
            'uid' => $payload->sub,
            'email' => isset($payload->email) ? (string) $payload->email : null,
            'name' => isset($payload->name) ? (string) $payload->name : null,
        ];
    }

    /**
     * @return array<string, Key>
     */
    protected function keys(): array
    {
        return Cache::remember('firebase_jwt_keys', now()->addMinutes(60), function (): array {
            $response = Http::timeout(10)
                ->get(self::JWKS_URL);

            if ($response->failed()) {
                throw new RuntimeException('Gagal memuat kunci verifikasi Google.');
            }

            return JWK::parseKeySet($response->json());
        });
    }
}
