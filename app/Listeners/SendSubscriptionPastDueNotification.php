<?php

namespace App\Listeners;

use App\Events\SubscriptionPastDue;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionPastDueNotification implements ShouldQueue
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
  public function handle(SubscriptionPastDue $event): void
  {
    $event->subscription->user->notify(
      new SubscriptionStatusNotification(
        'Payment failed, subscription past due',
        'We could not renew your subscription. Please update your payment.'
      )
    );
  }
}
