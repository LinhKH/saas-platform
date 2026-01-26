<?php

use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
  return response()->json(['status' => 'ok']);
});

Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
  ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
  Route::get('/auth/devices', [\App\Http\Controllers\Api\AuthController::class, 'devices']);
  Route::post('/auth/logout/current', [\App\Http\Controllers\Api\AuthController::class, 'logoutCurrent']);
  Route::post('/auth/logout/all', [\App\Http\Controllers\Api\AuthController::class, 'logoutAll']);
  Route::post('/auth/logout/device/{tokenId}', [\App\Http\Controllers\Api\AuthController::class, 'logoutDevice']);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
});
