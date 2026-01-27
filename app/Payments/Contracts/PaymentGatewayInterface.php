<?php

namespace App\Payments\Contracts;

use App\Models\Payment;

/**
🧠 Senior note

createPayment() trả data để client dùng (client_secret, redirect_url…)

parseWebhook() không update DB, chỉ parse & verify

Business update nằm ở Service
 */
interface PaymentGatewayInterface
{
  /**
   * Create payment intent on gateway
   */
  public function createPayment(Payment $payment): array;

  /**
   * Verify & parse webhook payload
   */
  public function parseWebhook(array $payload): array;

  /**
   * Gateway name (stripe, mock)
   */
  public function name(): string;
}
