<?php

namespace App\Services\Payment;

use App\Events\PaymentSucceeded;
use App\Payments\Contracts\GmoGatewayInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Support\Facades\DB;

// 🔥 ĐÂY LÀ TRÁI TIM CỦA GMO INTEGRATION
class GmoPaymentService
{
  public function __construct(
    private GmoGatewayInterface $gateway,
    private PaymentRepositoryInterface $payments
  ) {}

  // Phase 3
  public function entryAndExec(string $orderId): string
  {
    $payment = $this->payments->findByOrderId($orderId);

    if (!$payment) {
      throw new \DomainException('Payment not found');
    }

    if (!$payment->access_id) {
      $entry = $this->gateway->entryTran(
        $payment->order_id,
        (int) $payment->amount
      );

      $payment->update([
        'access_id' => $entry['AccessID'],
        'access_pass' => $entry['AccessPass'],
      ]);
    }

    $exec = $this->gateway->execTran(
      $payment->access_id,
      $payment->access_pass
    );

    return $exec['payment_url'];
  }

  // Phase 4 core handler
  public function handleResult(array $payload): void
  {
    $result = $this->gateway->verifyAndParseResult($payload);
    $payment = null;
    $shouldFireEvent = false;

    DB::transaction(function () use ($result, &$payment, &$shouldFireEvent) {
      $payment = $this->payments->findByOrderId($result->orderId);

      if (!$payment || $payment->status === 'succeeded') {
        return;
      }

      if ($result->success) {
        $this->payments->markSucceeded(
          $result->orderId,
          $result->raw
        );

        $shouldFireEvent = true;
        // 👉 trigger wallet / subscription sau
      } else {
        $this->payments->markFailed(
          $result->orderId,
          $result->raw
        );
      }
    });
    // 🔥 PHÁT EVENT SAU COMMIT
    if ($shouldFireEvent && $payment) {
      event(new PaymentSucceeded($payment));
    }
    /**
    👉 Đúng chuẩn:

    Transaction xong mới bắn event

    Idempotent
     */
  }

  public function reconcileOne(string $orderId): void
  {
    $result = $this->gateway->searchTrade($orderId);

    $this->handleResult($result);
  }
}
