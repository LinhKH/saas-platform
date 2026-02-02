<?php

namespace App\Payments\Contracts;

use App\Payments\DTOs\GmoResult;

interface GmoGatewayInterface
{
  public function entryTran(string $orderId, int $amount): array;

  public function execTran(string $accessId, string $accessPass): array;

  public function verifyAndParseResult(array $payload): GmoResult;

  public function searchTrade(string $orderId): array;
}
//👉 Gateway biết GMO, Service không.
