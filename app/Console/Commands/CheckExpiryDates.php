<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\ExpiryNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class CheckExpiryDates extends Command
{
  /**
   * The name and signature of the console command.
   * This defines the command's name 'app:check-expiry-dates'.
   *
   * @var string
   */
  protected $signature = 'app:check-expiry-dates'; // <-- THIS WAS MISSING

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Checks for expiring passports and visas and sends notifications to admins.'; // <-- THIS WAS MISSING

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('--- Starting Expiry Date Check ---');

    $tenants = Tenant::all();
    $this->info("Found tenants: " . $tenants->pluck('id')->implode(', '));

    foreach ($tenants as $tenant) {
      tenancy()->initialize($tenant);
      $this->line("\n--- Checking Tenant: [{$tenant->id}] ---");

      // --- DEBUG ADMINS ---
      $adminUsers = User::whereHas('roles', fn($query) => $query->where('name', 'admin'))->get();
      if ($adminUsers->isEmpty()) {
        $this->warn("No admin users found for tenant {$tenant->id}. Skipping.");
        continue;
      }
      $this->info("Found {$adminUsers->count()} admin user(s) in this tenant.");

      $notificationThresholds = [30, 15, 7];

      foreach ($notificationThresholds as $days) {
        $targetExpiryDate = now()->addDays($days)->toDateString();
        $this->line("-> Checking for expiry in {$days} days (Target Date: {$targetExpiryDate})");

        // --- DEBUG PASSPORTS ---
        $expiringPassports = User::whereDate('passport_expiry_date', $targetExpiryDate)->get();
        $this->info("   - Found {$expiringPassports->count()} user(s) with expiring passports.");
        if ($expiringPassports->isNotEmpty()) {
          Notification::send($adminUsers, new ExpiryNotification($expiringPassports->first(), 'passport', $days));
        }

        // --- DEBUG VISAS ---
        $expiringVisas = User::whereDate('visa_expiry_date', $targetExpiryDate)->get();
        $this->info("   - Found {$expiringVisas->count()} user(s) with expiring visas.");
        if ($expiringVisas->isNotEmpty()) {
          Notification::send($adminUsers, new ExpiryNotification($expiringVisas->first(), 'visa', $days));
        }
      }
    }

    $this->info("\n--- All tenants checked successfully. ---");
    return 0;
  }
}
