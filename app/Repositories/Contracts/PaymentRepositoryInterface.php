<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
  public function create(array $data): Payment;

  public function findByReference(string $reference): ?Payment;

  public function findByGatewayPaymentId(string $gateway, string $gatewayPaymentId): ?Payment;

  public function markSucceeded(Payment $payment, array $payload = []): void;

  public function markFailed(Payment $payment, array $payload = []): void;
}
