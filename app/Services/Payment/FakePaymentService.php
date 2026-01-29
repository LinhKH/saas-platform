<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SimulatePaymentCallback;

class FakePaymentService
{
  public function createPayment(Order $order): Payment
  {
    return Payment::create([
      'order_id' => $order->id,
      'provider' => 'fake',
      'status' => 'pending',
      'external_id' => Str::uuid(),
      'amount' => $order->total,
    ]);
  }

  public function dispatchCallback(Payment $payment): void
  {
    // giả lập gateway callback sau 3s
    SimulatePaymentCallback::dispatch($payment)
      ->delay(now()->addSeconds(3));
  }
}
