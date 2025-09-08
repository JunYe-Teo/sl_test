<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;

Route::apiResource('logs', LogController::class)->only(['index', 'store', 'show']);
