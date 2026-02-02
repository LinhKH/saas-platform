<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class SimulatePaymentCallback implements ShouldQueue
{
  public function __construct(
    public Payment $payment
  ) {}

  // 👉 Đây là giả lập cổng thanh toán gọi webhook về server bạn
  public function handle(): void
  {
    Http::post(url('/api/webhooks/payment'), [
      'external_id' => $this->payment->external_id,
      'status' => 'success',
      'signature' => hash_hmac(
        'sha256',
        $this->payment->external_id,
        config('services.fake.secret')
      ),
    ]);
  }
}
