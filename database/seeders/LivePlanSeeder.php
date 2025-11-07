<?php

namespace Database\Seeders;

use App\Enums\PlanDurationType;
use App\Models\SuperAdmin\Plan;
use Illuminate\Database\Seeder;

class LivePlanSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->info('Seeding plans for live...');

    Plan::create([
      'name' => 'Basic',
      'base_price' => 50,
      'per_user_price' => 30,
      'modules' => ["LeaveManagement", "ExpenseManagement", "ClientVisit", "ChatSystem", "AiChat", 'SoS'],
      'description' => 'This is a free plan',
      'included_users' => 1,
      'duration' => 1,
      'duration_type' => PlanDurationType::MONTHS,
    ]);
  }
}
