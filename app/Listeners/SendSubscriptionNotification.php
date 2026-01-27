<?php

namespace App\Listeners;

use App\Events\SubscriptionRenewed;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionNotification implements ShouldQueue
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
  public function handle(SubscriptionRenewed $event): void
  {
    $event->subscription->user->notify(
      new SubscriptionStatusNotification(
        'Subscription renewed',
        'Your subscription has been renewed successfully.'
      )
    );
  }
}
