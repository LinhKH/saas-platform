<?php

namespace App\Providers;

use App\Payments\Contracts\GmoGatewayInterface;
use App\Payments\Gateways\MockGmoGateway;
use App\Payments\Gateways\RealGmoGateway;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\WalletRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    $bindings = [
      UserRepositoryInterface::class => UserRepository::class,
      WalletRepositoryInterface::class => WalletRepository::class,
      SubscriptionRepositoryInterface::class => SubscriptionRepository::class,
      PaymentRepositoryInterface::class => PaymentRepository::class,
      GmoGatewayInterface::class => MockGmoGateway::class,
      // GmoGatewayInterface::class => RealGmoGateway::class, // 👉 Chuyển sang dùng gateway thật khi deploy production
    ];

    foreach ($bindings as $abstract => $concrete) {
      $this->app->bind($abstract, $concrete); // interface to implementation
    }
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    //
  }
}
