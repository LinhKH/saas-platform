<?php

namespace App\Repositories\Contracts;

use App\Models\Wallet;

interface WalletRepositoryInterface
{
  public function findByUserIdForUpdate(int $userId): Wallet;

  public function updateBalance(Wallet $wallet, float $balance): void;
}
