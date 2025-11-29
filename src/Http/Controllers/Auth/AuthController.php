<?php

namespace RMS\Api\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use RMS\Api\Http\Controllers\BaseApiController;
use RMS\Api\Support\Auth\AuthManager;

class AuthController extends BaseApiController
{
    public function __construct(protected AuthManager $authManager)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'driver' => ['nullable', 'string'],
        ]);

        $driver = $this->authManager->driver($data['driver'] ?? null);

        $user = $driver->login($data, $request);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $token = $this->authManager->createToken($user);

        return $this->apiSuccess([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'email' => ['required', 'email', 'unique:' . (new ($this->authManager->userModel()))->getTable() . ',email'],
            'password' => ['required', 'string', 'min:8'],
            'driver' => ['nullable', 'string'],
        ]);

        $driver = $this->authManager->driver($data['driver'] ?? null);

        $user = $driver->register($data, $request);
        $token = $this->authManager->createToken($user);

        return $this->apiSuccess([
            'token' => $token,
            'user' => $this->userPayload($user),
        ], status: 201);
    }

    public function me(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return $this->apiError(['auth' => ['Unauthenticated']], 401);
        }

        return $this->apiSuccess([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $driver = $this->authManager->driver();
            $driver->logout($request, $request->user());
        }

        return $this->apiSuccess(['message' => 'logged_out']);
    }

    protected function userPayload($user): array
    {
        return Arr::only($user->toArray(), ['id', 'name', 'email']);
    }
}

