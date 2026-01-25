<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerification
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
  public function handle(UserRegistered $event): void
  {
    if ($event->user->hasVerifiedEmail()) {
      return;
    }
    $event->user->notify(new VerifyEmailNotification(
      $event->user->id,
      $event->user->email
    ));
  }
}
