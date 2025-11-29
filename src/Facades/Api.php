<?php

namespace RMS\Api\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void module(string $name, \Closure $routes, array $options = [])
 */
class Api extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RMS\Api\Support\Routing\ModuleRegistrar::class;
    }
}

