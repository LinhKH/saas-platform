<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\ParsedWebhook;

class MockGateway implements PaymentGatewayInterface
{
  public function name(): string
  {
    return 'mock';
  }

  /**
   * Giả lập create payment intent (ý đinh thanh toán)
   */
  public function createPayment(Payment $payment): array
  {
    // giả lập gateway_payment_id
    $gatewayPaymentId = 'mock_' . uniqid();

    return [
      'gateway' => $this->name(),
      'gateway_payment_id' => $gatewayPaymentId,
      'pay_url' => url('/mock-pay/' . $gatewayPaymentId),
    ];
  }

  /**
   * Parse & verify webhook payload (mock)
   */
  public function parseWebhook(array $payload): ParsedWebhook
  {
    // payload mock ví dụ:
    // { gateway_payment_id, status }
    if (!isset($payload['gateway_payment_id'], $payload['status'])) {
      throw new \InvalidArgumentException('Invalid webhook payload');
    }

    return new ParsedWebhook(
      $payload['gateway_payment_id'],
      $payload['status'], // succeeded | failed
      $payload
    );
  }
}
/**
🧠 Senior notes

Gateway không update DB

Không business logic

Chỉ tạo intent + parse webhook
 */