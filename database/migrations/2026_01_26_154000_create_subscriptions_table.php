<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/**
🧠 Senior notes

Không dùng boolean is_active

Period rõ ràng → audit được

cancelled_at ≠ expired
 */
return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('subscriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('plan_id')->constrained()->restrictOnDelete();
      $table->enum('status', [
        'trialing',
        'active',
        'past_due',
        'cancelled',
        'expired',
      ]);

      $table->timestamp('trial_ends_at')->nullable();
      $table->timestamp('current_period_start')->nullable();
      $table->timestamp('current_period_end')->nullable();
      $table->timestamp('cancelled_at')->nullable();

      $table->timestamps();

      $table->index(['user_id', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('subscriptions');
  }
};
