<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use App\Exceptions\DomainException;

class WebhookController extends Controller
{
  public function payment(Request $request, OrderService $orderService)
  {
    $signature = hash_hmac(
      'sha256',
      $request->external_id,
      config('services.fake.secret')
    );

    if ($signature !== $request->signature) {
      throw new DomainException('Invalid webhook signature', 403);
    }

    $payment = Payment::where('external_id', $request->external_id)->firstOrFail();

    // 🔐 Idempotency
    if ($payment->status === 'success') {
      return response()->json(['status' => 'already_processed']);
    }

    $payment->update(['status' => 'success']);

    // trigger business
    $orderService->pay($payment->order_id);

    return response()->json(['status' => 'ok']);
  }
}
