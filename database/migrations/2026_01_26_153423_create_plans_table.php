<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
🧠 Senior notes

code dùng cho business

id chỉ là kỹ thuật

active=false để ngưng bán plan
 */
return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('plans', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique(); // basic, pro, enterprise
      $table->string('name');
      $table->decimal('price', 16, 2);
      $table->enum('interval', ['month', 'year']);
      $table->integer('trial_days')->default(0);
      $table->boolean('active')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('plans');
  }
};
