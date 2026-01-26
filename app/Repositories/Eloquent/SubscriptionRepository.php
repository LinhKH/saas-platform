<?php

namespace App\Repositories\Eloquent;

use App\Models\Subscription;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
  public function getActiveByUser(int $userId): ?Subscription
  {
    return Subscription::where('user_id', $userId)
      ->whereIn('status', ['trialing', 'active', 'past_due'])
      ->latest()
      ->first();
  }

  public function create(array $data): Subscription
  {
    return Subscription::create($data);
  }
}
