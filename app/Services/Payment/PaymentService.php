<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Payments\PaymentGatewayFactory;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
  public function __construct(
    private PaymentRepositoryInterface $paymentRepo,
    private WalletService $walletService
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
  public function handleWebhook(string $gateway, array $payload): void
  {
    $gatewayInstance = PaymentGatewayFactory::make($gateway);
    $parsed = $gatewayInstance->parseWebhook($payload);

    $payment = $this->paymentRepo
      ->findByGatewayPaymentId($gateway, $parsed->gatewayPaymentId);

    if (!$payment) {
      return; // unknown payment → ignore
    }

    DB::transaction(function () use ($payment, $parsed) {

      if ($parsed->status === 'succeeded') {
        $this->paymentRepo->markSucceeded($payment, $parsed->rawPayload);

        // 👉 side-effect: topup wallet (ví dụ)
        $this->walletService->credit(
          $payment->reference_user_id ?? 1, // demo: map user sau
          $payment->amount,
          'payment_' . $payment->reference,
          'Payment topup'
        );
      }

      if ($parsed->status === 'failed') {
        $this->paymentRepo->markFailed($payment, $parsed->rawPayload);
      }
    });
  }
}
