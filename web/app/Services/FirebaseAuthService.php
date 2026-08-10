<?php

namespace App\Services;

use App\Exceptions\InvalidGoogleTokenException;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;
use RuntimeException;

class FirebaseAuthService
{
    protected ?FirebaseAuth $auth = null;

    public function auth(): FirebaseAuth
    {
        if ($this->auth !== null) {
            return $this->auth;
        }

        $credentials = config('services.firebase.credentials');
        $projectId = config('services.firebase.project_id');

        if (! is_string($credentials) || $credentials === '') {
            throw new RuntimeException('FIREBASE_CREDENTIALS belum dikonfigurasi.');
        }

        $path = str_starts_with($credentials, '/')
            ? $credentials
            : base_path($credentials);

        if (! is_file($path)) {
            throw new RuntimeException('File Firebase credentials tidak ditemukan.');
        }

        $factory = (new Factory)->withServiceAccount($path);

        if (is_string($projectId) && $projectId !== '') {
            $factory = $factory->withProjectId($projectId);
        }

        return $this->auth = $factory->createAuth();
    }

    /**
     * @return array{uid: string, email: string|null, name: string|null, picture: string|null}
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verified = $this->auth()->verifyIdToken($idToken);
        } catch (FailedToVerifyToken $e) {
            throw InvalidGoogleTokenException::invalid($e->getMessage());
        } catch (\Throwable $e) {
            throw InvalidGoogleTokenException::invalid($e->getMessage());
        }

        $claims = $verified->claims();

        $email = $claims->get('email');
        $name = $claims->get('name');
        $picture = $claims->get('picture');

        return [
            'uid' => (string) $claims->get('sub'),
            'email' => is_string($email) ? $email : null,
            'name' => is_string($name) ? $name : null,
            'picture' => is_string($picture) ? $picture : null,
        ];
    }
}
