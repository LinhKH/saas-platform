<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateWalletForUser
{
  /**
   * Create the event listener.
   */
  public function __construct(private WalletRepositoryInterface $walletRepo)
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(UserRegistered $event): void
  {
    $this->walletRepo->createForUser($event->user->id);
  }
}
