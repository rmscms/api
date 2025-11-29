<?php

namespace RMS\Api\Http\Controllers;

use Illuminate\Routing\Controller;
use RMS\Api\Support\Response\Concerns\HandlesApiResponse;

abstract class BaseApiController extends Controller
{
    use HandlesApiResponse;
}

