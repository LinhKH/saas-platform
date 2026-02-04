<?php

namespace App\Repositories\Eloquent;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
  public function findActiveByUserAndPlan(int $userId, int $planId): ?Subscription
  {
    return Subscription::where('user_id', $userId)
      ->where('plan_id', $planId)
      ->where('status', 'active')
      ->latest()
      ->first();
  }
  public function getActiveByUser(int $userId): ?Subscription
  {
    return Subscription::where('user_id', $userId)
      ->whereIn('status', ['trialing', 'active', 'past_due']) // trialing: đang trong thời gian dùng thử, active: đang hoạt động, past_due: quá hạn thanh toán
      ->latest()
      ->first();
  }

  public function findById(int $id): ?Subscription
  {
    return Subscription::find($id);
  }

  public function create(array $data): Subscription
  {
    return Subscription::create($data);
  }

  public function save(Subscription $subscription): void
  {
    $subscription->save();
  }
}
