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

  // 👉 Đây là idempotency point
  public function markSucceeded(string $orderId, array $raw): void
  {
    $payment = $this->findByOrderId($orderId);

    if (!$payment || $payment->status === 'succeeded') {
      return;
    }

    $payment->update([
      'status' => 'succeeded',
      'raw_result' => $raw,
    ]);
  }

  public function markFailed(string $orderId, array $payload = []): void
  {
    $payment = $this->findByOrderId($orderId);

    if (!$payment || $payment->status === 'failed') {
      return; // idempotent
    }

    $payment->update([
      'status' => 'failed',
      'payload' => $payload,
    ]);
  }

  public function findByOrderId(string $orderId): ?Payment
  {
    return Payment::where('order_id', $orderId)->first();
  }

  public function saveAccess(string $orderId, string $accessId, string $accessPass): void
  {
    Payment::where('order_id', $orderId)->update([
      'access_id' => $accessId,
      'access_pass' => $accessPass,
    ]);
  }

  public function getPendingGmoPayments(int $limit = 50)
  {
    return Payment::where('gateway', 'gmo')
      ->where('status', 'pending')
      ->limit($limit)
      ->get();
  }
}
