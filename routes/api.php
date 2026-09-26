<?php

use App\Http\Controllers\Api\MonitoringApiController as Api;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/hosts', [Api::class, 'hosts']);
    Route::post('/hosts', [Api::class, 'store']);
    Route::get('/hosts/{host}', [Api::class, 'show']);
    Route::put('/hosts/{host}', [Api::class, 'update']);
    Route::delete('/hosts/{host}', [Api::class, 'destroy']);
    Route::get('/hosts/{host}/history', [Api::class, 'history']);
    Route::get('/hosts/{host}/incidents', [Api::class, 'incidents']);
    Route::get('/dashboard/summary', [Api::class, 'summary']);
});
