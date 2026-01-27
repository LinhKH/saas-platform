<?php

namespace App\Listeners;

use App\Events\SubscriptionExpired;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionExpiredNotification implements ShouldQueue
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
    public function handle(SubscriptionExpired $event): void
    {
        $event->subscription->user->notify(
            new SubscriptionStatusNotification(
                'Subscription expired',
                'Your subscription has expired due to non-payment.'
            )
        );
    }
}
