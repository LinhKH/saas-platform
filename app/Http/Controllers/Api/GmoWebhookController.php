<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Payment\GmoPaymentService;

class GmoWebhookController
{
  public function handle(Request $request, GmoPaymentService $service)
  {
    $service->handleResult($request->all());

    return response()->json(['status' => 'ok']);
  }
}
