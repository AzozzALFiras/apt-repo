<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tweak;
use App\Http\Controllers\Controller;
use App\Http\Resources\SileoResource;

class DepictionController extends Controller
{
    public function show($tweak)
    {
        $tweak = Tweak::where('package', $tweak)->with('changeLogs')->firstOrFail();
        return new SileoResource($tweak);
    }
}
