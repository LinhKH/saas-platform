<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
  public function create(array $data): Payment
  {
    return Payment::create($data);
  }

  public function findByReference(string $reference): ?Payment
  {
    return Payment::where('reference', $reference)->first();
  }

  public function findByGatewayPaymentId(string $gateway, string $gatewayPaymentId): ?Payment
  {
    return Payment::where('gateway', $gateway)
      ->where('gateway_payment_id', $gatewayPaymentId)
      ->first();
  }

  public function markSucceeded(Payment $payment, array $payload = []): void
  {
    if ($payment->status === 'succeeded') {
      return; // idempotent
    }

    $payment->update([
      'status' => 'succeeded',
      'payload' => $payload,
    ]);
  }

  public function markFailed(Payment $payment, array $payload = []): void
  {
    if ($payment->status === 'failed') {
      return; // idempotent
    }

    $payment->update([
      'status' => 'failed',
      'payload' => $payload,
    ]);
  }
}
