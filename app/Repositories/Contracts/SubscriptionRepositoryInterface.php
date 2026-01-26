<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
  public function getActiveByUser(int $userId): ?Subscription;

  public function create(array $data): Subscription;
}
