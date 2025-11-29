<?php

namespace RMS\Api\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;

class ApplyRateLimit
{
    public function __construct(protected RateLimiter $rateLimiter)
    {
    }

    public function handle($request, Closure $next, ?int $maxAttempts = null, ?int $decaySeconds = null)
    {
        $config = config('rms-api.rate_limit', []);

        if (!($config['enabled'] ?? false)) {
            return $next($request);
        }

        $maxAttempts ??= $config['max_attempts'] ?? 60;
        $decaySeconds ??= $config['decay_seconds'] ?? 60;

        $key = $this->resolveKey($request, $config) ?? $request->ip();

        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            throw new ThrottleRequestsException(
                'Too many attempts.',
                null,
                [
                    'Retry-After' => $this->rateLimiter->availableIn($key),
                ]
            );
        }

        $this->rateLimiter->hit($key, $decaySeconds);

        $response = $next($request);

        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $this->rateLimiter->remaining($key, $maxAttempts)),
        ]);

        return $response;
    }

    protected function resolveKey($request, array $config): ?string
    {
        $resolver = $config['key_resolver'] ?? null;

        if (is_string($resolver) && class_exists($resolver)) {
            $resolver = app($resolver);
        }

        if (is_callable($resolver)) {
            $key = $resolver($request);
            if (is_string($key) && $key !== '') {
                return Str::lower($key);
            }
        }

        if ($request->user()) {
            return 'user:' . $request->user()->getAuthIdentifier();
        }

        return null;
    }
}

