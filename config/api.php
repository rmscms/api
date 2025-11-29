<?php

return [
    'enabled' => env('RMSCMS_API_ENABLED', true),

    'routing' => [
        'prefix' => 'api/v1',
        'middleware' => ['api'],
        'name' => 'rms.api.',
    ],

    'auth' => [
        'guard' => env('RMSCMS_API_GUARD', 'sanctum'),
        'device_name' => env('RMSCMS_API_DEVICE', 'rms-api'),
        'user_model' => env('RMSCMS_API_USER_MODEL', config('auth.providers.users.model')),
        'default_driver' => env('RMSCMS_API_AUTH_DRIVER', 'email'),
        'drivers' => [
            'email' => RMS\Api\Support\Auth\Drivers\EmailPasswordDriver::class,
        ],
    ],

    'response' => [
        'status_key' => 'status',
        'data_key' => 'data',
        'errors_key' => 'errors',
        'meta_key' => 'meta',
        'default_status' => 'ok',
        'modifiers' => [
            // \App\Api\Modifiers\ExampleModifier::class,
        ],
    ],

    'rate_limit' => [
        'enabled' => false,
        'max_attempts' => 60,
        'decay_seconds' => 60,
        'key_resolver' => null,
    ],
];

