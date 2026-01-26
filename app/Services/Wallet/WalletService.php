<?php

namespace App\Services\Wallet;

use App\Exceptions\DomainException;
use App\Models\WalletTransaction;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
🧠 Senior notes

Ledger insert trước, update balance sau (trong cùng transaction)

Debit validate business rule

Không query WalletTransaction trong Repository (để Service kiểm soát)
 */
class WalletService
{
  public function __construct(
    private WalletRepositoryInterface $walletRepo
  ) {}

  public function credit(
    int $userId,
    float $amount,
    string $reference,
    string $description = null
  ): void {
    $this->runWithRetry(function () use ($userId, $amount, $reference, $description) {
      DB::transaction(function () use ($userId, $amount, $reference, $description) {

        $wallet = $this->walletRepo->findByUserIdForUpdate($userId);

        // ✅ Idempotency check
        $exists = WalletTransaction::where('wallet_id', $wallet->id)
          ->where('reference', $reference)
          ->exists();

        if ($exists) {
          return; // already processed
        }

        $newBalance = $wallet->balance + $amount;

        WalletTransaction::create([
          'wallet_id' => $wallet->id,
          'type' => 'credit',
          'amount' => $amount,
          'balance_after' => $newBalance,
          'reference' => $reference,
          'description' => $description,
        ]);

        $this->walletRepo->updateBalance($wallet, $newBalance);
      });
    });
  }

  public function debit(
    int $userId,
    float $amount,
    string $reference,
    string $description = null
  ): void {
    $this->runWithRetry(function () use ($userId, $amount, $reference, $description) {
      DB::transaction(function () use ($userId, $amount, $reference, $description) {

        $wallet = $this->walletRepo->findByUserIdForUpdate($userId);

        $exists = WalletTransaction::where('wallet_id', $wallet->id)
          ->where('reference', $reference)
          ->exists();

        if ($exists) {
          return;
        }

        if ($wallet->balance < $amount) {
          throw new DomainException('Insufficient balance', 422);
        }

        $newBalance = $wallet->balance - $amount;

        WalletTransaction::create([
          'wallet_id' => $wallet->id,
          'type' => 'debit',
          'amount' => $amount,
          'balance_after' => $newBalance,
          'reference' => $reference,
          'description' => $description,
        ]);

        $this->walletRepo->updateBalance($wallet, $newBalance);
      });
    });
  }

  protected function runWithRetry(callable $callback, int $times = 3): void
  {
    retry($times, function () use ($callback) {
      try {
        $callback();
      } catch (QueryException $e) {
        // MySQL deadlock error code
        if ($e->getCode() === '40001') {
          throw $e;
        }
        throw $e;
      }
    }, 100); // 100ms delay
  }
}
