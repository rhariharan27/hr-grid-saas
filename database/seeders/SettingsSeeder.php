<?php

namespace Database\Seeders;

use App\Models\SuperAdmin\SaSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->info('Seeding SA settings...');

    SaSettings::create([
      'app_version' => '4.1.2',
      'currency' => 'USD',
      'currency_symbol' => '$',
      'currency_position' => 'left',
      'offline_payment_enabled' => true,
      'privacy_policy_url' => 'https://czappstudio.com/privacy-policy/',
      'website' => 'https://czappstudio.com',
      'support_email' => 'support@czappstudio.com',
      'support_phone' => '+91 88254 39260',
      'support_whatsapp' => '+91 88254 39260',
      'offline_payment_instructions' => 'Please make your payment to the following bank account number: 1234567890',
    ]);

    $this->command->info('SA Settings seeded!');
  }
}
