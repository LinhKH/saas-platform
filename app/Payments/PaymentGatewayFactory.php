<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\Gateways\MockGateway;

/**
🧠 Senior note

Factory tách logic chọn gateway

Sau này thêm Stripe chỉ cần add case
 */
class PaymentGatewayFactory
{
  public static function make(string $gateway): PaymentGatewayInterface
  {
    return match ($gateway) {
      'mock' => app(MockGateway::class),
      default => throw new \InvalidArgumentException('Unsupported gateway'),
    };
  }
}
