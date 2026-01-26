<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RenewSubscriptionsJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(SubscriptionService $subscriptionService): void
  {
    Subscription::where('status', 'active')
      ->where('current_period_end', '<=', now())
      ->chunkById(100, function ($subscriptions) use ($subscriptionService) {
        foreach ($subscriptions as $subscription) {
          try {
            $subscriptionService->renew($subscription);
          } catch (\Throwable $e) {
            // log & continue
            \Log::warning('Renew failed', [
              'subscription_id' => $subscription->id,
              'error' => $e->getMessage(),
            ]);
          }
        }
      });
  }
}
