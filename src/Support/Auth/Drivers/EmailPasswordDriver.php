<?php

namespace RMS\Api\Support\Auth\Drivers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RMS\Api\Contracts\AuthDriver;
use RMS\Api\Support\Auth\AuthManager;

class EmailPasswordDriver implements AuthDriver
{
    public function __construct(protected AuthManager $manager)
    {
    }

    public function login(array $credentials, Request $request): ?Authenticatable
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return null;
        }

        $modelClass = $this->manager->userModel();
        /** @var Model|Authenticatable|null $user */
        $user = (new $modelClass())->newQuery()->where('email', $email)->first();

        if (!$user || !Hash::check($password, (string) $user->password)) {
            return null;
        }

        return $user;
    }

    public function register(array $data, Request $request): Authenticatable
    {
        $modelClass = $this->manager->userModel();
        /** @var Model|Authenticatable $user */
        $user = new $modelClass();

        $user->fill([
            'name' => $data['name'] ?? Str::before($data['email'] ?? '', '@'),
            'email' => $data['email'] ?? '',
        ]);

        $user->password = $this->manager->hashPassword((string) ($data['password'] ?? Str::random(12)));
        $user->save();

        return $user;
    }

    public function logout(Request $request, Authenticatable $user): void
    {
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }
}

