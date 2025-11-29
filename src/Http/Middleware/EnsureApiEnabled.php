<?php

namespace RMS\Api\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnsureApiEnabled
{
    public function handle($request, Closure $next)
    {
        if (!config('rms-api.enabled', true)) {
            throw new NotFoundHttpException();
        }

        return $next($request);
    }
}

