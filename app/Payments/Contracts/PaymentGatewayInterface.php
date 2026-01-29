<?php

namespace App\Payments\Contracts;

use App\Models\Payment;
use App\Payments\DTOs\ParsedWebhook;
use Symfony\Component\HttpFoundation\Request;

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
  /**
🔁 Lưu ý: chuyển từ array $payload → Request $request để gateway đọc header + body.
   */
  public function parseWebhook(Request $request): ParsedWebhook;

  /**
   * Gateway name (stripe, mock)
   */
  public function name(): string;
}
