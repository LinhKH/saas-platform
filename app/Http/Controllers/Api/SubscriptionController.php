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
}
