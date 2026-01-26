<?php

namespace App\Services\Subscription;

use App\Exceptions\DomainException;
use App\Models\Plan;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SubscriptionService
{
  public function __construct(
    private SubscriptionRepositoryInterface $subscriptionRepo,
    private WalletService $walletService
  ) {}

  public function subscribe(
    int $userId,
    string $planCode,
    string $reference
  ) {
    return DB::transaction(function () use ($userId, $planCode, $reference) {

      $plan = Plan::where('code', $planCode)
        ->where('active', true)
        ->first();

      if (!$plan) {
        throw new DomainException('Invalid plan');
      }

      $existing = $this->subscriptionRepo->getActiveByUser($userId);

      if ($existing) {
        throw new DomainException('User already has an active subscription');
      }

      $now = Carbon::now();

      // Trial logic
      if ($plan->trial_days > 0) {
        return $this->subscriptionRepo->create([
          'user_id' => $userId,
          'plan' => $plan->code,
          'status' => 'trialing',
          // tại sao dùng copy()?
          // Trong Carbon, phương thức copy() được sử dụng để tạo một bản sao của đối tượng Carbon hiện tại.
          // Điều này hữu ích khi bạn muốn thực hiện các thao tác trên một đối tượng Carbon mà không làm thay đổi đối tượng gốc.
          // Nếu bạn không sử dụng copy(), các thao tác như addDays() sẽ thay đổi trực tiếp đối tượng gốc, điều này có thể dẫn đến các lỗi không mong muốn trong logic của bạn.
          'trial_ends_at' => $now->copy()->addDays($plan->trial_days),
        ]);
      }

      // Paid subscription
      $this->walletService->debit(
        $userId,
        $plan->price,
        $reference,
        "Subscribe to {$plan->code}"
      );

      return $this->subscriptionRepo->create([
        'user_id' => $userId,
        'plan' => $plan->code,
        'status' => 'active',
        'current_period_start' => $now,
        'current_period_end' => $this->calculatePeriodEnd($now, $plan->interval),
      ]);
    });
  }

  protected function calculatePeriodEnd(Carbon $start, string $interval): Carbon
  {
    return match ($interval) {
      'year' => $start->copy()->addYear(),
      default => $start->copy()->addMonth(),
    };
  }
}
