<?php

namespace App\Events;

use App\Models\Payment;

class PaymentSucceeded
{
  public function __construct(
    public Payment $payment
  ) {}
}
