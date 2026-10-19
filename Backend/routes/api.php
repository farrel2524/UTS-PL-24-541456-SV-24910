<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\spddataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // ini buat ngelimit jadi 5 request per 1 menit (kyknya)

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn (Request $request) => $request-> user());


    Route::apiResource('spddatas', spddataController::class);




});





// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
