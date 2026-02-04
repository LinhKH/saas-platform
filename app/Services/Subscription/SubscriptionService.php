<?php

namespace App\Services\Subscription;

use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionExpired;
use App\Events\SubscriptionPastDue;
use App\Events\SubscriptionRenewed;
use App\Events\SubscriptionResumed;
use App\Events\SubscriptionSubscribed;
use App\Exceptions\DomainException;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
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
        throw new DomainException('Invalid plan, cannot subscribe');
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
          'plan_id' => $plan->id,
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

      $subscription = $this->subscriptionRepo->create([
        'user_id' => $userId,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_start' => $now,
        'current_period_end' => $this->calculatePeriodEnd($now, $plan->interval),
      ]);

      event(new SubscriptionSubscribed($subscription));
      return $subscription;
    });
  }

  protected function calculatePeriodEnd(Carbon $start, string $interval): Carbon
  {
    return match ($interval) {
      'year' => $start->copy()->addYear(),
      default => $start->copy()->addMonth(),
    };
  }

  public function renew(Subscription $subscription): void
  {
    // 1️⃣ Không xử lý nếu đã expired / cancelled
    if (in_array($subscription->status, ['expired', 'cancelled'])) {
      return;
    }

    // 2️⃣ Nếu past_due → kiểm tra grace period
    if ($subscription->status === 'past_due') {

      if (!$subscription->isInGracePeriod()) {
        // ⬅⬅⬅ ĐOẠN BẠN HỎI ĐẶT Ở ĐÂY
        $subscription->update([
          'status' => 'expired',
        ]);
        event(new SubscriptionExpired($subscription));
      }

      return; // ⛔ KHÔNG tiếp tục renew
    }

    // Only active subscriptions
    // 3️⃣ Chỉ xử lý active subscription
    if ($subscription->status !== 'active') {
      return;
    }

    // Not yet due
    // 4️⃣ Chưa tới hạn → không làm gì
    if ($subscription->current_period_end->isFuture()) {
      return;
    }

    $reference = 'renew_' . $subscription->id . '_' . $subscription->current_period_end->format('Ymd');

    try {
      $this->walletService->debit(
        $subscription->user_id,
        $this->getPlanPrice($subscription),
        $reference,
        'Subscription renewal'
      );

      // Success → extend period
      $subscription->update([
        'current_period_start' => now(),
        'current_period_end' => $this->calculatePeriodEnd(now(), $this->getPlanInterval($subscription)),
      ]);
      event(new SubscriptionRenewed($subscription));
    } catch (DomainException $e) {
      // Payment failed → past_due
      $subscription->update([
        'status' => 'past_due',
      ]);
      event(new SubscriptionPastDue($subscription));
    }
  }
  /**
   * Summary of cancelAtPeriodEnd
   * @param Subscription $subscription
   * @throws DomainException
   * @return void
   */
  public function cancelAtPeriodEnd(Subscription $subscription): void
  {
    if (!in_array($subscription->status, ['active', 'trialing'])) {
      throw new DomainException('Subscription cannot be cancelled');
    }

    $subscription->update([
      'status' => 'cancelled',
      'cancelled_at' => now(),
    ]);
    event(new SubscriptionCancelled($subscription));
  }
  /*
🧠 Senior note

Không xoá subscription

Không refund ngay

User vẫn dùng tới hết kỳ
  */

  public function resume(Subscription $subscription): void
  {
    if ($subscription->status !== 'cancelled') {
      throw new DomainException('Subscription cannot be resumed');
    }

    // Only resume if still within period
    // isPast() là một phương thức của đối tượng Carbon, được sử dụng để kiểm tra xem một thời điểm cụ thể có nằm trong quá khứ so với thời điểm hiện tại hay không.
    if ($subscription->current_period_end->isPast()) {
      throw new DomainException('Subscription already expired');
    }

    $subscription->update([
      'status' => 'active',
      'cancelled_at' => null,
    ]);
    event(new SubscriptionResumed($subscription));
  }

  protected function getPlanPrice(Subscription $subscription): float
  {
    return $subscription->plan->price;
  }

  protected function getPlanInterval(Subscription $subscription): string
  {
    return $subscription->plan->interval;
  }

  /**
   * Activate or renew subscription from successful payment
   */
  public function activateFromPayment(Payment $payment): void
  {
    // 1️⃣ Validate payment
    if ($payment->status !== 'succeeded') {
      throw new DomainException('Payment is not successful');
    }

    if ($payment->purpose !== 'subscription') {
      throw new DomainException('Payment is not for subscription');
    }

    if (!$payment->subscription_id) {
      throw new DomainException('Payment missing subscription_id');
    }

    // 2️⃣ Load subscription
    $subscription = $this->subscriptionRepo->findById($payment->subscription_id);

    if (!$subscription) {
      throw new DomainException('Subscription not found');
    }

    // 3️⃣ Verify payment belongs to subscription owner
    if ($subscription->user_id !== $payment->user_id) {
      throw new DomainException('Payment user mismatch with subscription owner');
    }

    $now = Carbon::now();
    $plan = $subscription->plan;

    // 4️⃣ Calculate new period end based on plan interval
    $periodEnd = $this->calculatePeriodEnd($now, $plan->interval);

    // 5️⃣ ACTIVATE or RENEW subscription
    if ($subscription->status === 'active' && $subscription->current_period_end->greaterThan($now)) {
      // Nếu còn hạn → cộng dồn thêm 1 kỳ
      $subscription->current_period_end = $this->calculatePeriodEnd($subscription->current_period_end, $plan->interval);
    } else {
      // Nếu chưa active hoặc đã hết hạn → reset period
      $subscription->status = 'active';
      $subscription->current_period_start = $now;
      $subscription->current_period_end = $periodEnd;
    }

    $this->subscriptionRepo->save($subscription);
  }
}
