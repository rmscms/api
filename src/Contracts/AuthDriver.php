<?php

namespace RMS\Api\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface AuthDriver
{
    public function login(array $credentials, Request $request): ?Authenticatable;

    public function register(array $data, Request $request): Authenticatable;

    public function logout(Request $request, Authenticatable $user): void;
}

