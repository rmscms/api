<?php

namespace RMS\Api;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use RMS\Api\Support\Response\ApiResponder;
use RMS\Api\Support\Response\ResponsePipeline;
use RMS\Api\Support\Routing\ModuleRegistrar;
use RMS\Api\Support\Routing\RouteMacroRegistrar;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api.php', 'rms-api');

        $this->app->singleton(ResponsePipeline::class, fn (Container $app) => new ResponsePipeline($app));

        $this->app->singleton(ApiResponder::class, fn (Container $app) => new ApiResponder(
            $app->make(ResponsePipeline::class)
        ));

        $this->app->singleton(ModuleRegistrar::class, fn (Container $app) => new ModuleRegistrar());
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/api.php' => config_path('rms-api.php'),
        ], 'rms-api-config');

        RouteMacroRegistrar::register();

        $this->app->booted(function () {
            $this->app->make(ModuleRegistrar::class)->boot();
        });
    }
}

