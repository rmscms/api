<?php

namespace RMS\Api\Events;

use Illuminate\Http\Request;
use RMS\Api\Support\Response\ApiResponsePayload;

class ApiResponseReady
{
    public function __construct(public ApiResponsePayload $payload, public Request $request)
    {
    }
}

