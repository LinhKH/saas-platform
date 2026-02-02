<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
  public function create(array $data): Payment;

  public function findByReference(string $reference): ?Payment;

  public function findByGatewayPaymentId(string $gateway, string $gatewayPaymentId): ?Payment;

  public function saveAccess(string $orderId, string $accessId, string $accessPass): void;

  public function findByOrderId(string $orderId);
  public function markSucceeded(string $orderId, array $raw): void;
  public function markFailed(string $orderId, array $raw): void;
  public function getPendingGmoPayments(int $limit = 50);
}
