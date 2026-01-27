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
}
