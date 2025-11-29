<?php

namespace RMS\Api\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

class ModuleRegistrar
{
    protected array $modules = [];

    public function module(string $name, Closure $routes, array $options = []): void
    {
        $this->modules[] = compact('name', 'routes', 'options');
    }

    public function boot(): void
    {
        foreach ($this->modules as $module) {
            if (Route::hasMacro('rmsApiModule')) {
                Route::rmsApiModule($module['name'], $module['routes'], $module['options']);
            }
        }
    }
}

