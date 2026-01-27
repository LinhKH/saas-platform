<?php

namespace App\Listeners;

use App\Events\SubscriptionCancelled;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionCancelledNotification implements ShouldQueue
{
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(SubscriptionCancelled $event): void
  {
    $event->subscription->user->notify(
      new SubscriptionStatusNotification(
        'Subscription cancelled',
        'Your subscription will end at the current billing period.'
      )
    );
  }
}
