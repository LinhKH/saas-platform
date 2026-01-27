<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RenewSubscriptionsJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(SubscriptionService $subscriptionService): void
  {
    /**
    Job pick: active
    → renew()
      → debit OK → active (extend)

    Job pick: active
    → renew()
      → debit FAIL → past_due

    Job pick: past_due
    → renew()
      → grace còn → return

    Job pick: past_due
    → renew()
      → grace hết → expired + event
     */
    Subscription::whereIn('status', ['active', 'past_due'])
      ->where('current_period_end', '<=', now())
      ->chunkById(100, function ($subscriptions) use ($subscriptionService) {
        foreach ($subscriptions as $subscription) {
          try {
            $subscriptionService->renew($subscription);
          } catch (\Throwable $e) {
            // log & continue
            \Log::warning('Renew failed', [
              'subscription_id' => $subscription->id,
              'error' => $e->getMessage(),
            ]);
          }
        }
      });
  }
}
/**
 * TEST CASES
 * 2️⃣ RENEW SUCCESS → MAIL RENEWED
 * 2.1 Ép subscription tới hạn:
    UPDATE subscriptions
    SET current_period_end = NOW() - INTERVAL 5 DAY
    WHERE user_id = 1;
  * 2.2 nạp tiền vào ví (đủ tiền):
    php artisan tinker
    app(\App\Services\Wallet\WalletService::class)->credit(1, 1000, 'topup_test', 'Topup for renewal');
    dispatch(new \App\Jobs\RenewSubscriptionsJob);
  * 2.3 Kiểm tra kết quả:
    ✅ Kết quả:

    status = active

    current_period_end được extend

    Log mail: "Subscription renewed"
  * 3️⃣ RENEW FAIL → MAIL PAST_DUE
  * 3.1 Ép subscription tới hạn:
      UPDATE subscriptions
      SET current_period_end = NOW() - INTERVAL 5 DAY
      WHERE user_id = 1;
  * 3.2 Đảm bảo ví không đủ tiền (hoặc reset ví):
      php artisan tinker
      app(\App\Services\Wallet\WalletService::class)->debit(1, 9999, 'drain_wallet', 'Drain wallet'); // giả sử số dư không đủ
  * 3.3 Chạy job:
      dispatch(new \App\Jobs\RenewSubscriptionsJob);
  * 3.4 Kiểm tra kết quả:
      ✅ Kết quả:
      status = past_due
      Log mail: "Payment failed, subscription past due"
  * 4️⃣ GRACE END → MAIL EXPIRED
  * 4.1 Ép subscription sang past_due và hết grace period:
      UPDATE subscriptions
      SET status = 'past_due',
          updated_at = NOW() - INTERVAL 10 DAY,
          current_period_end = NOW() - INTERVAL 5 DAY
      WHERE user_id = 1;
  * 4.2 Chạy job:
      dispatch(new \App\Jobs\RenewSubscriptionsJob);
  * 4.3 Kiểm tra kết quả:
      ✅ Kết quả:
      status = expired
      Log mail: "Subscription expired"

  * 5️⃣ CANCEL → MAIL CANCELLED
      Chỉ test khi subscription đang active hoặc trialing
  * 5.1 Hủy subscription:
      curl -X POST http://localhost:8000/api/subscription/cancel -H "Authorization: Bearer $TOKEN" -H "Accept: application/json"
  * 5.2 Kiểm tra kết quả:
      ✅ Kết quả:
      status = cancelled
      Log mail: "Subscription cancelled"

    * 6️⃣ RESUME → MAIL RESUMED
      Chỉ test khi subscription đang ở trạng thái cancelled và trong thời gian có thể resume (ví dụ: chưa quá 7 ngày kể từ khi hủy)
  * 6.1 Resume subscription:
      curl -X POST http://localhost:8000/api/subscription/resume -H "Authorization: Bearer $TOKEN" -H "Accept: application/json"
  * 6.2 Kiểm tra kết quả:
      ✅ Kết quả:
      status = active
      cancelled_at = null
      current_period_end được tính lại dựa trên thời điểm resume
      Log mail: "Subscription resumed"
 */

