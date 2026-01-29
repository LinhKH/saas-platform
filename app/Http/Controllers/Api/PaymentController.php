<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Payment\PaymentService;

/**
🧠 Senior note

Controller không biết Wallet / Webhook

Không try-catch business exception

Để Handler xử lý
 */
class PaymentController extends BaseApiController
{
  public function __construct(
    private PaymentService $paymentService
  ) {}

  /**
   * Create payment intent
   */
  public function create(Request $request)
  {
    $data = $request->validate([
      'amount' => 'required|numeric|min:1',
      'reference' => 'required|string',
      'gateway' => 'sometimes|string', // mock | stripe
    ]);

    $result = $this->paymentService->create(
      $data['amount'],
      $data['reference'],
      $data['gateway'] ?? 'mock'
    );

    return $this->success($result, 'Payment created');
  }

  /**
   * Create TOPUP payment
   */
  public function topup(Request $request)
  {
    $data = $request->validate([
      'amount' => 'required|numeric|min:1',
      'reference' => 'required|string',
      'gateway' => 'sometimes|string',
    ]);

    $result = $this->paymentService->createTopup(
      $request->user()->id,
      $data['amount'],
      $data['reference'],
      $data['gateway'] ?? 'mock'
    );

    return $this->success($result, 'Topup payment created');
  }

  /**
   * Create SUBSCRIPTION payment
   */
  public function subscribe(Request $request)
  {
    $data = $request->validate([
      'subscription_id' => 'required|integer',
      'amount' => 'required|numeric|min:1',
      'reference' => 'required|string',
      'gateway' => 'sometimes|string',
    ]);

    $result = $this->paymentService->createSubscriptionPayment(
      $request->user()->id,
      $data['subscription_id'],
      $data['amount'],
      $data['reference'],
      $data['gateway'] ?? 'mock'
    );

    return $this->success($result, 'Subscription payment created');
  }
}
