<?php

namespace App\Repositories\Contracts;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
  public function findActiveByUserAndPlan(int $userId, int $planId): ?Subscription;
  public function getActiveByUser(int $userId): ?Subscription;

  public function findById(int $id): ?Subscription;

  public function create(array $data): Subscription;

  public function save(Subscription $subscription): void;
}
