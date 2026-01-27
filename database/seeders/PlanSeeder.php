<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Plan::insert([
      [
        'code' => 'basic',
        'name' => 'Basic',
        'price' => 100,
        'interval' => 'month',
        'trial_days' => 7,
      ],
      [
        'code' => 'pro',
        'name' => 'Pro',
        'price' => 300,
        'interval' => 'month',
        'trial_days' => 14,
      ],
      [
        'code' => 'pro_yearly',
        'name' => 'Pro Yearly',
        'price' => 3000,
        'interval' => 'year',
        'trial_days' => 0,
      ],
    ]);
  }
}
