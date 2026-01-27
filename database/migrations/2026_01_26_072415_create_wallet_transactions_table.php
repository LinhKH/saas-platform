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
    Schema::create('wallet_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
      $table->enum('type', ['credit', 'debit']);
      $table->decimal('amount', 16, 2);
      $table->decimal('balance_after', 16, 2);
      $table->string('reference')->nullable();
      $table->string('description')->nullable();
      $table->timestamps();

      $table->index(['wallet_id', 'created_at']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('wallet_transactions');
  }
};
/**
🧠 Senior notes

❌ Không updated_at logic (ledger bất biến)

balance_after giúp audit nhanh và dễ dàng hơn
(audit là quá trình kiểm tra, đánh giá và xác minh các giao dịch tài chính để đảm bảo tính chính xác và tuân thủ quy định.)

reference dùng cho idempotency sau này
 */