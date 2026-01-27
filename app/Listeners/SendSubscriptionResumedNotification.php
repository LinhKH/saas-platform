<?php

namespace App\Listeners;

use App\Events\SubscriptionResumed;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionResumedNotification implements ShouldQueue
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
    public function handle(SubscriptionResumed $event): void
    {
        $event->subscription->user->notify(
            new SubscriptionStatusNotification(
                'Subscription resumed',
                'Your subscription has been resumed successfully.'
            )
        );
    }
}