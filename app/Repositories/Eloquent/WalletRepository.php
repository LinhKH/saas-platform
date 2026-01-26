<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\DomainException;
use App\Models\Wallet;
use App\Repositories\Contracts\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
  public function findByUserIdForUpdate(int $userId): Wallet
  {
    $wallet = Wallet::where('user_id', $userId)
      ->lockForUpdate()
      ->first();
    if (!$wallet) {
      throw new DomainException('Wallet not found for user');
    }

    return $wallet;
  }

  public function updateBalance(Wallet $wallet, float $balance): void
  {
    $wallet->update(['balance' => $balance]);
  }
}
