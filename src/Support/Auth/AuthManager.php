<?php

namespace RMS\Api\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RMS\Api\Contracts\AuthDriver;

class AuthManager
{
    public function __construct(protected Container $container)
    {
    }

    public function guard(): ?string
    {
        return config('rms-api.auth.guard');
    }

    public function deviceName(): string
    {
        return config('rms-api.auth.device_name', 'rms-api');
    }

    public function userModel(): string
    {
        return config('rms-api.auth.user_model', config('auth.providers.users.model'));
    }

    public function driver(?string $name = null): AuthDriver
    {
        $config = config('rms-api.auth');
        $name ??= $config['default_driver'] ?? 'email';

        $drivers = $config['drivers'] ?? [];
        $driverClass = Arr::get($drivers, $name);

        if (!$driverClass || !class_exists($driverClass)) {
            throw new InvalidArgumentException("Auth driver [{$name}] is not defined.");
        }

        return $this->container->make($driverClass);
    }

    public function createToken(Authenticatable $user): string
    {
        if (!method_exists($user, 'createToken')) {
            throw new InvalidArgumentException('Selected user model must use Laravel Sanctum HasApiTokens trait.');
        }

        return $user->createToken($this->deviceName())->plainTextToken;
    }

    public function hashPassword(string $password): string
    {
        return Hash::make($password);
    }
}

