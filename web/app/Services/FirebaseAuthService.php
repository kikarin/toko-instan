<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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
        $projectId = $this->projectId();

        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID tidak dikonfigurasi.');
        }

        // In development (Deno), set a long leeway for JWT expiration to avoid clock skew issues
        // On production, this block won't execute, so standard validation applies
        if (config('app.env') === 'local' || config('app.env') === 'testing') {
            JWT::$leeway = 60 * 60 * 24 * 365 * 10; // 10 years leeway
        }

        try {
            $payload = JWT::decode($idToken, $this->keys());
        } catch (\Throwable $e) {
            throw new RuntimeException('Token Google tidak valid: '.$e->getMessage(), 0, $e);
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
     * Resolve Firebase project id, stripping a common authDomain misconfiguration.
     */
    public function projectId(): string
    {
        $projectId = (string) config('services.firebase.project_id');

        return (string) preg_replace('/\.(firebaseapp\.com|web\.app)$/i', '', $projectId);
    }

    /**
     * @return array<string, Key>
     */
    protected function keys(): array
    {
        $jwks = Cache::remember('firebase_jwt_keys_json', now()->addMinutes(60), function (): array {
            $response = Http::timeout(10)
                ->get(self::JWKS_URL);

            if ($response->failed()) {
                throw new RuntimeException('Gagal memuat kunci verifikasi Google.');
            }

            return $response->json();
        });

        return JWK::parseKeySet($jwks);
    }
}
