<?php

namespace RMS\Api\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

class RouteMacroRegistrar
{
    public static function register(): void
    {
        if (!Route::hasMacro('rmsApi')) {
            Route::macro('rmsApi', function (array $options, Closure $callback) {
                $routing = config('rms-api.routing', []);
                $prefix = RouteMacroRegistrar::buildPrefix($routing['prefix'] ?? null, $options['prefix'] ?? null);
                $middleware = $options['middleware'] ?? $routing['middleware'] ?? ['api'];
                $name = RouteMacroRegistrar::buildName($routing['name'] ?? null, $options['name'] ?? null);

                return Route::prefix($prefix)
                    ->middleware($middleware)
                    ->name($name)
                    ->group($callback);
            });
        }

        if (!Route::hasMacro('rmsApiModule')) {
            Route::macro('rmsApiModule', function (string $module, Closure $callback, array $options = []) {
                $routing = config('rms-api.routing', []);
                $modulePrefix = $options['prefix'] ?? $module;
                $moduleName = $options['name'] ?? $module;

                $prefix = RouteMacroRegistrar::buildPrefix($routing['prefix'] ?? null, $modulePrefix);
                $middleware = $options['middleware'] ?? $routing['middleware'] ?? ['api'];
                $name = RouteMacroRegistrar::buildName($routing['name'] ?? null, $moduleName);

                return Route::prefix($prefix)
                    ->middleware($middleware)
                    ->name($name)
                    ->group(function () use ($callback, $module) {
                        $callback($module);
                    });
            });
        }
    }

    public static function buildPrefix(?string ...$parts): string
    {
        $segments = [];
        foreach ($parts as $part) {
            if (!$part) {
                continue;
            }
            $segments[] = trim($part, '/');
        }

        $prefix = trim(implode('/', array_filter($segments)), '/');

        return $prefix === '' ? '/' : $prefix;
    }

    public static function buildName(?string ...$parts): string
    {
        $segments = [];
        foreach ($parts as $part) {
            if (!$part) {
                continue;
            }
            $segments[] = trim($part, '.');
        }

        $name = implode('.', array_filter($segments));

        return $name ? $name . '.' : '';
    }
}

