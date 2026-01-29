<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Payment\PaymentService;

// 👉 Mọi security nằm trong Gateway, controller không cần biết.
class PaymentWebhookController extends BaseApiController
{
  public function handle(string $gateway, Request $request, PaymentService $paymentService)
  {
    $paymentService->handleWebhook($gateway, $request);

    return response()->json(['status' => 'ok']);
  }
}
