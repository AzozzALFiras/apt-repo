<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DepictionController;

Route::get('description-sileo/{tweak}', [DepictionController::class, 'show']);
