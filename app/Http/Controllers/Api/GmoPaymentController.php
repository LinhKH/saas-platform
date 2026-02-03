<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Payment\GmoPaymentService;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Support\Str;

class GmoPaymentController extends Controller
{
  public function create(Request $request, GmoPaymentService $service, PaymentRepositoryInterface $paymentRepository)
  {
    $request->validate([
      'amount'  => 'required|integer|min:100',
      'purpose' => 'required|string',
    ]);

    // 1️⃣ Tạo OrderID (ASCII ONLY)
    $orderId = 'ORD_' . Str::upper(Str::random(12));

    // 2️⃣ Tạo payment intent
    $payment = $paymentRepository->create([
      'gateway'   => 'gmo',
      'order_id'  => $orderId,
      'user_id'   => 1, // test cứng
      'amount'    => $request->amount,
      'purpose'   => $request->purpose,
      'status'    => 'pending',
    ]);
    // $payment = Payment::create([
    //   'gateway'   => 'gmo',
    //   'order_id'  => $orderId,
    //   'user_id'   => 1, // test cứng
    //   'amount'    => $request->amount,
    //   'purpose'   => $request->purpose,
    //   'status'    => 'pending',
    // ]);

    // 3️⃣ EntryTran + ExecTran
    $paymentUrl = $service->entryAndExec($orderId);

    return response()->json([
      'order_id'    => $orderId,
      'payment_url' => $paymentUrl,
    ]);
  }
}
