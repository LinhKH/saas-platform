<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Subscription\SubscriptionService;

class SubscriptionController extends BaseApiController
{
  public function __construct(
    private SubscriptionService $subscriptionService
  ) {}

  public function subscribe(Request $request)
  {
    $data = $request->validate([
      'plan' => 'required|string',
    ]);

    $subscription = $this->subscriptionService->subscribe(
      $request->user()->id,
      $data['plan'],
      'subscription_' . uniqid()
    );

    return $this->success($subscription, 'Subscribed');
  }

  public function cancel(Request $request)
  {
    $subscription = $request->user()->subscription;

    $this->subscriptionService->cancelAtPeriodEnd($subscription);

    return $this->success(null, 'Subscription cancelled at period end');
  }

  public function resume(Request $request)
  {
    $subscription = $request->user()->subscription;

    $this->subscriptionService->resume($subscription);

    return $this->success(null, 'Subscription resumed');
  }
}
