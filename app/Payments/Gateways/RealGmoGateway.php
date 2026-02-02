<?php

namespace App\Payments\Gateways;

use App\Payments\Contracts\GmoGatewayInterface;
use App\Payments\DTOs\GmoResult;

class RealGmoGateway implements GmoGatewayInterface
{
  public function entryTran(string $orderId, int $amount): array
  {
    // call GMO EntryTran API
    // return AccessID / AccessPass
    return [];
  }

  public function execTran(string $accessId, string $accessPass): array
  {
    // call GMO ExecTran
    // return redirect URL
    return [];
  }

  public function verifyAndParseResult(array $payload): GmoResult
  {
    // 🔐 VERIFY SIGNATURE (MD5)
    // ShopID + OrderID + Amount + ShopPass

    return new GmoResult(
      orderId: $payload['OrderID'],
      success: $payload['Status'] === 'CAPTURE',
      raw: $payload
    );
  }
}
