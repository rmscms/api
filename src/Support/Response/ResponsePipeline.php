<?php

namespace RMS\Api\Support\Response;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use RMS\Api\Contracts\ResponseModifier;

class ResponsePipeline
{
    public function __construct(protected Container $container)
    {
    }

    public function apply(ApiResponsePayload $payload, Request $request): ApiResponsePayload
    {
        $modifiers = config('rms-api.response.modifiers', []);

        foreach ($modifiers as $modifierClass) {
            if (!class_exists($modifierClass)) {
                continue;
            }

            $modifier = $this->container->make($modifierClass);

            if ($modifier instanceof ResponseModifier) {
                $modifier->modify($payload, $request);
            }
        }

        return $payload;
    }
}

