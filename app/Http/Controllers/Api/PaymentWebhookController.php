<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Payment\PaymentService;

class PaymentWebhookController extends BaseApiController
{
  public function handle(string $gateway, Request $request, PaymentService $paymentService)
  {
    $paymentService->handleWebhook($gateway, $request->all());

    return response()->json(['status' => 'ok']);
  }
}
