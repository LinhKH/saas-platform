<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Services\Wallet\WalletService;
use App\Services\Subscription\SubscriptionService;

class ApplyPaymentSuccess
{
  public function __construct(
    private WalletService $wallets,
    private SubscriptionService $subscriptions
  ) {}

  public function handle(PaymentSucceeded $event): void
  {
    $payment = $event->payment;

    match ($payment->purpose) {
      'topup' => $this->wallets->credit(
        $payment->user_id,
        $payment->amount,
        'gmo_topup',
        $payment->order_id
      ),

      'subscription' => $this->subscriptions->activateFromPayment(
        $payment
      ),

      default => null
    };
  }
}
