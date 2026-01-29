<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\VerifyEmailController;
use App\Http\Controllers\Api\WebhookController;
use App\Services\Payment\FakePaymentService;
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
  Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
  Route::post('/subscription/resume', [SubscriptionController::class, 'resume']);

  Route::post('/payments', [PaymentController::class, 'create']);
  // 🔹 Wallet topup
  Route::post('/payments/topup', [PaymentController::class, 'topup']);
  // 🔹 Subscription first charge
  Route::post('/payments/subscription', [PaymentController::class, 'subscribe']);
});

// 3️⃣ WEBHOOK = NGUỒN SỰ THẬT
Route::post('/webhooks/{gateway}', [PaymentWebhookController::class, 'handle']);
Route::post('/webhooks/payment', [WebhookController::class, 'payment']);
