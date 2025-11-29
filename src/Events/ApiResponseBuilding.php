<?php

namespace RMS\Api\Events;

use Illuminate\Http\Request;
use RMS\Api\Support\Response\ApiResponsePayload;

class ApiResponseBuilding
{
    public function __construct(public ApiResponsePayload $payload, public Request $request)
    {
    }
}

