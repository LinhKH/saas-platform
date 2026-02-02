<?php

namespace App\Payments\Gateways;

use App\Payments\Contracts\GmoGatewayInterface;
use App\Payments\DTOs\GmoResult;

class MockGmoGateway implements GmoGatewayInterface
{
  public function entryTran(string $orderId, int $amount): array
  {
    return [
      'AccessID'   => 'mock_access_' . uniqid(),
      'AccessPass' => 'mock_pass_' . uniqid(),
    ];
  }

  public function execTran(string $accessId, string $accessPass): array
  {
    return [
      'payment_url' => 'https://mock-gmo.test/pay?access=' . $accessId,
    ];
  }

  public function verifyAndParseResult(array $payload): GmoResult
  {
    // mock = trust payload
    return new GmoResult(
      orderId: $payload['OrderID'],
      success: $payload['Status'] === 'SUCCESS',
      raw: $payload
    );
  }

  public function searchTrade(string $orderId): array
  {
    // giả lập GMO trả kết quả thành công
    return [
      'OrderID' => $orderId,
      'Status'  => 'SUCCESS',
      // ... other fields
    ];
  }
}
