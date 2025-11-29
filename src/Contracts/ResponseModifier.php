<?php

namespace RMS\Api\Contracts;

use Illuminate\Http\Request;
use RMS\Api\Support\Response\ApiResponsePayload;

interface ResponseModifier
{
    public function modify(ApiResponsePayload $payload, Request $request): void;
}

