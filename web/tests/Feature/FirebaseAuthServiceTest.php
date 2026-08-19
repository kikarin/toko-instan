<?php

use App\Services\FirebaseAuthService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

function firebaseTestKeyPair(): array
{
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    expect($privateKey)->not->toBeFalse();

    openssl_pkey_export($privateKey, $privatePem);
    $details = openssl_pkey_get_details($privateKey);

    $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
    $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

    return [
        'private' => $privatePem,
        'jwks' => [
            'keys' => [[
                'kty' => 'RSA',
                'kid' => 'test-kid',
                'use' => 'sig',
                'alg' => 'RS256',
                'n' => $n,
                'e' => $e,
            ]],
        ],
    ];
}

function firebaseIdToken(string $privatePem, string $projectId, array $claims = []): string
{
    $now = time();

    return JWT::encode(array_merge([
        'iss' => 'https://securetoken.google.com/'.$projectId,
        'aud' => $projectId,
        'sub' => 'firebase-uid-abc',
        'email' => 'user@example.com',
        'name' => 'Test User',
        'iat' => $now,
        'exp' => $now + 3600,
    ], $claims), $privatePem, 'RS256', 'test-kid');
}

test('project id strips firebase auth domain suffix', function () {
    config(['services.firebase.project_id' => 'toko-instan-2d2b1.firebaseapp.com']);

    expect(app(FirebaseAuthService::class)->projectId())->toBe('toko-instan-2d2b1');
});

test('verifyIdToken accepts issuer when env mistakenly used auth domain as project id', function () {
    $keys = firebaseTestKeyPair();
    $projectId = 'toko-instan-2d2b1';

    config(['services.firebase.project_id' => $projectId.'.firebaseapp.com']);

    Http::fake([
        'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com' => Http::response($keys['jwks']),
    ]);

    $token = firebaseIdToken($keys['private'], $projectId);
    $identity = app(FirebaseAuthService::class)->verifyIdToken($token);

    expect($identity)->toMatchArray([
        'uid' => 'firebase-uid-abc',
        'email' => 'user@example.com',
        'name' => 'Test User',
    ]);
});

test('verifyIdToken rejects wrong issuer', function () {
    $keys = firebaseTestKeyPair();

    config(['services.firebase.project_id' => 'toko-instan-2d2b1']);

    Http::fake([
        'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com' => Http::response($keys['jwks']),
    ]);

    $token = firebaseIdToken($keys['private'], 'other-project');

    expect(fn () => app(FirebaseAuthService::class)->verifyIdToken($token))
        ->toThrow(RuntimeException::class, 'Issuer token tidak valid.');
});

test('auth pages send coop header for google popup', function () {
    $this->get('/register')
        ->assertOk()
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
});
