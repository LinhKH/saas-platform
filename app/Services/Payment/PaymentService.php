<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Payments\PaymentGatewayFactory; // tại sao chỗ này không inject vào constructor? 
// vì factory là để tạo instance, không phải instance cụ thể nên không inject được
// nên gọi tĩnh để lấy instance cụ thể theo gateway
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
🧠 TƯ DUY CHUẨN (STRIPE-LIKE)

❗ Payment luôn gắn với CONTEXT

Ví dụ:

Topup wallet

Subscribe plan

Renew subscription

One-time purchase

👉 Ta lưu context vào Payment, không đoán.
 */
class PaymentService
{
  public function __construct(
    private PaymentRepositoryInterface $paymentRepo,
    private WalletService $walletService,
    private \App\Payments\Contracts\GmoGatewayInterface $gmoGateway,
  ) {}

  /**
   * Create payment intent (pending)
   */
  public function create(
    float $amount,
    string $reference,
    string $gateway = 'mock'
  ): array {
    // idempotency by reference
    $existing = $this->paymentRepo->findByReference($reference);
    if ($existing) {
      return [
        'payment_id' => $existing->id,
        'status' => $existing->status,
      ];
    }

    return DB::transaction(function () use ($amount, $reference, $gateway) {

      $payment = $this->paymentRepo->create([
        'gateway' => $gateway,
        'reference' => $reference,
        'amount' => $amount,
        'status' => 'pending',
      ]);

      $gatewayInstance = PaymentGatewayFactory::make($gateway);
      $intent = $gatewayInstance->createPayment($payment);

      // lưu gateway_payment_id
      $payment->update([
        'gateway_payment_id' => $intent['gateway_payment_id'],
      ]);

      return [
        'payment_id' => $payment->id,
        'pay_url' => $intent['pay_url'],
      ];
    });
  }

  /**
   * Handle webhook (idempotent)
   */
  public function handleWebhook(string $gateway, Request $request): void
  {
    $gatewayInstance = PaymentGatewayFactory::make($gateway);
    $parsed = $gatewayInstance->parseWebhook($request);

    $payment = $this->paymentRepo->findByGatewayPaymentId($gateway, $parsed->gatewayPaymentId);

    if (!$payment) {
      return; // unknown payment → ignore
    }

    DB::transaction(function () use ($payment, $parsed) {

      if ($parsed->status === 'succeeded') {

        $this->paymentRepo->markSucceeded($payment, $parsed->rawPayload);

        match ($payment->purpose) {

          'topup' => $this->walletService->credit(
            $payment->user_id,
            $payment->amount,
            'payment_' . $payment->reference,
            'Wallet topup'
          ),

          'subscription' => $this->handleSubscriptionPayment($payment),

          default => null,
        };
      }

      if ($parsed->status === 'failed') {
        $this->paymentRepo->markFailed($payment, $parsed->rawPayload);
      }
    });
  }

  protected function handleSubscriptionPayment(Payment $payment): void
  {
    // payment thành công → kích hoạt subscription
    // reference idempotent nên an toàn retry

    $subscription = \App\Models\Subscription::find($payment->subscription_id);
    if (!$subscription) {
      return;
    }

    $subscription->update([
      'status' => 'active',
      'current_period_start' => now(),
      'current_period_end' => now()->addMonth(),
    ]);
  }

  // PAYMENT INTENT
  public function createTopup(
    int $userId,
    float $amount,
    string $reference,
    string $gateway = 'mock'
  ): array {
    return $this->createWithContext(
      $amount,
      $reference,
      $gateway,
      [
        'user_id' => $userId,
        'purpose' => 'topup',
      ]
    );
  }

  public function createSubscriptionPayment(
    int $userId,
    int $subscriptionId,
    float $amount,
    string $reference,
    string $gateway = 'mock'
  ): array {
    return $this->createWithContext(
      $amount,
      $reference,
      $gateway,
      [
        'user_id' => $userId,
        'purpose' => 'subscription',
        'subscription_id' => $subscriptionId,
      ]
    );
  }

  protected function createWithContext(
    float $amount,
    string $reference,
    string $gateway,
    array $context
  ): array {
    $existing = $this->paymentRepo->findByReference($reference);
    if ($existing) {
      return [
        'payment_id' => $existing->id,
        'status' => $existing->status,
      ];
    }

    return DB::transaction(function () use ($amount, $reference, $gateway, $context) {

      $payment = $this->paymentRepo->create(array_merge([
        'gateway' => $gateway,
        'reference' => $reference,
        'amount' => $amount,
        'status' => 'pending',
      ], $context));

      $gatewayInstance = PaymentGatewayFactory::make($gateway);
      $intent = $gatewayInstance->createPayment($payment);

      $payment->update([
        'gateway_payment_id' => $intent['gateway_payment_id'],
      ]);

      return [
        'payment_id' => $payment->id,
        'pay_url' => $intent['pay_url'],
      ];
    });
  }


  /**
🧠 RẤT QUAN TRỌNG

EntryTran có thể retry

Phải idempotent

Không gọi lại nếu đã có AccessID
   */
  public function entryTransaction(string $orderId): void
  {
    $payment = $this->paymentRepo->findByOrderId($orderId);

    if (!$payment) {
      throw new \DomainException('Payment not found');
    }

    // idempotent: đã có access thì không entry lại
    if ($payment->access_id) {
      return;
    }
    /**
🧠 RẤT QUAN TRỌNG

EntryTran có thể retry

Phải idempotent

Không gọi lại nếu đã có AccessID
     */
    $result = $this->gmoGateway->entryTran(
      $payment->order_id,
      (int) $payment->amount
    );

    $this->paymentRepo->saveAccess(
      $payment->order_id,
      $result['AccessID'],
      $result['AccessPass']
    );
  }

  public function execTransaction(string $orderId): string
  {
    $payment = $this->paymentRepo->findByOrderId($orderId);

    if (!$payment || !$payment->access_id) {
      throw new \DomainException('Payment not ready');
    }

    $result = $this->gmoGateway->execTran(
      $payment->access_id,
      $payment->access_pass
    );

    return $result['payment_url'];
  }
}
