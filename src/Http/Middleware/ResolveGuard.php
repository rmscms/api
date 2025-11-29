<?php

namespace RMS\Api\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ResolveGuard
{
    public function handle($request, Closure $next, ?string $guard = null)
    {
        $guard ??= config('rms-api.auth.guard');

        if ($guard) {
            Auth::shouldUse($guard);
            $request->attributes->set('rms.api.guard', $guard);
        }

        return $next($request);
    }
}

