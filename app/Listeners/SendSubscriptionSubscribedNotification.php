<?php

namespace App\Listeners;

use App\Events\SubscriptionSubscribed;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionSubscribedNotification implements ShouldQueue
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
  public function handle(SubscriptionSubscribed $event): void
  {
    $event->subscription->user->notify(
      new SubscriptionStatusNotification(
        'Subscription activated',
        'Your subscription has been activated successfully.'
      )
    );
  }
}
