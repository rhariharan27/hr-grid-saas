<?php

namespace Database\Seeders;

use App\Enums\DomainRequestStatus;
use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Models\SuperAdmin\DomainRequest;
use App\Models\SuperAdmin\Order;
use App\Models\SuperAdmin\Plan;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use App\Services\PlanService\ISubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
  private ISubscriptionService $planService;

  function __construct(ISubscriptionService $planService)
  {
    $this->planService = $planService;
  }

  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    Artisan::call('cache:clear');

    //Create Super Admin Role
    $this->command->info('Seeding Super Admin...');
    Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::create(['name' => 'customer', 'guard_name' => 'web']);

    $user = User::factory()->create([
      'first_name' => 'Super',
      'last_name' => 'Admin',
      'email' => 'superadmin@opencorehr.com',
      'password' => bcrypt('123456'),
      'email_verified_at' => now(),
      'remember_token' => Str::random(10),
      'is_customer' => false,
    ]);

    $customer = User::factory()->create([
      'first_name' => 'Demo',
      'last_name' => 'Customer',
      'email' => 'democustomer@opencorehr.com',
      'password' => bcrypt('123456'),
      'email_verified_at' => now(),
      'remember_token' => Str::random(10),
      'is_customer' => true,
    ]);

    $customer->assignRole('customer');

    $user->assignRole('super_admin');

    $this->command->info('Super Admin seeded!');

    $this->call(SettingsSeeder::class);


    //Need to comment these while taking release
    //$this->call(PlanSeeder::class);

    //Need to uncomment these while taking release
    $this->call(LivePlanSeeder::class);

    $this->seedDemoOrder($customer);
  }

  private function seedDemoOrder(User $customer)
  {

    $plan = Plan::query();
    $plan = $plan->orderByDesc('id')->first();

    $order = new Order();
    $order->user_id = $customer->id;
    $order->plan_id = $plan->id;
    $order->additional_users = 1000;

    $order->per_user_price = 0;
    $order->amount = 0;
    $order->total_amount = 0;
    $order->payment_gateway = 'demo';
    $order->paid_at = now();
    $order->status = OrderStatus::COMPLETED;
    $order->save();

    $endDate = $this->planService->generatePlanExpiryDate($plan);

    $user = User::find($customer->id);
    $user->plan_id = $plan->id;
    $user->plan_expired_date = $endDate;

    $user->save();

    Subscription::create([
      'user_id' => $user->id,
      'plan_id' => $order->plan_id,
      'users_count' => $plan->included_users + $order->additional_users,
      'additional_users' => 1000,
      'total_price' => $order->total_amount,
      'start_date' => now(),
      'end_date' => $endDate,
      'status' => SubscriptionStatus::ACTIVE,
      'tenant_id' => 'fm-tenant'
    ]);

    DomainRequest::create([
      'user_id' => $user->id,
      'name' => 'testdomain',
      'status' => DomainRequestStatus::APPROVED,
    ]);
  }
}
