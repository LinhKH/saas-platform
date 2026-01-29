<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\DTOs\ParsedWebhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
  public function parseWebhook(Request $request): ParsedWebhook
  {
    $payload = $request->getContent();
    // 4️⃣ SIGNATURE — STRIPE vs BẠN
    $timestamp = $request->header('X-Timestamp');
    $signature = $request->header('X-Signature');

    if (!$timestamp || !$signature) {
      throw new AccessDeniedHttpException('Missing signature headers');
    }

    // ⏱ Replay attack protection
    $tolerance = config('services.mock.tolerance');
    if (abs(time() - (int)$timestamp) > $tolerance) {
      throw new AccessDeniedHttpException('Webhook timestamp expired');
    }

    // 4️⃣ SIGNATURE — STRIPE vs BẠN
    // 🔐 Verify HMAC
    $secret = config('services.mock.webhook_secret');
    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    /**
    🧠 Senior notes

      hash_equals() chống timing attack

      Tolerance chống replay

      Gateway chỉ verify + parse
     */
    if (!hash_equals($expected, $signature)) {
      throw new AccessDeniedHttpException('Invalid webhook signature');
    }

    $data = json_decode($payload, true);


    // payload mock ví dụ:
    // { gateway_payment_id, status }
    if (!isset($data['gateway_payment_id'], $data['status'])) {
      throw new \InvalidArgumentException('Invalid webhook payload');
    }

    return new ParsedWebhook(
      $data['gateway_payment_id'],
      $data['status'],
      $data
    );
  }
}
/**
🧠 Senior notes

Gateway không update DB

Không business logic

Chỉ tạo intent + parse webhook
 */
