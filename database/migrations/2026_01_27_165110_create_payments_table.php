<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('payments', function (Blueprint $table) {
      // $table->id();
      // $table->string('gateway'); // stripe, mock
      // $table->string('gateway_payment_id')->nullable();
      // $table->string('reference')->unique(); // idempotency key
      // $table->decimal('amount', 16, 2);
      // $table->enum('status', [
      //   'pending',
      //   'succeeded',
      //   'failed',
      // ]);
      // $table->json('payload')->nullable(); // raw webhook data
      // $table->timestamps();

      // $table->index(['gateway', 'gateway_payment_id']);
      $table->id();

      // Gateway info
      $table->string('gateway', 32); // gmo, stripe, mock...

      // 🔑 GMO CORE
      $table->string('order_id', 64)->unique(); // OrderID (ASCII only)
      $table->string('access_id', 128)->nullable();
      $table->string('access_pass', 128)->nullable();

      // Payment data
      $table->unsignedBigInteger('user_id');
      $table->unsignedInteger('amount'); // JPY integer only
      $table->string('status', 32)->default('pending');
      // pending | succeeded | failed

      // Business binding
      $table->string('purpose', 32);
      // topup | subscription
      $table->unsignedBigInteger('subscription_id')->nullable();
      // subscription_id nếu có

      // Raw payload from GMO (callback / search)
      $table->json('raw_result')->nullable();

      $table->timestamps();

      // Indexes (performance + reconcile)
      $table->index(['gateway', 'status']);
      $table->index('user_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('payments');
  }
};
