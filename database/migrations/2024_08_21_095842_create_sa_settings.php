<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('sa_settings', function (Blueprint $table) {

      $table->id();
      $table->string('app_version')->default('1.0.0');
      $table->boolean('app_force_update')->default(false);
      $table->string('currency')->default('USD');
      $table->string('currency_symbol')->default('$');
      $table->string('currency_position')->default('left');
      $table->string('privacy_policy_url')->nullable();

      //Gateway Settings
      $table->boolean('paypal_enabled')->default(false);
      $table->string('paypal_mode')->default('sandbox');
      $table->string('paypal_client_id')->nullable();
      $table->string('paypal_secret')->nullable();

      $table->boolean('razorpay_enabled')->default(false);
      $table->string('razorpay_key')->nullable();
      $table->string('razorpay_secret')->nullable();

      $table->boolean('offline_payment_enabled')->default(false);
      $table->string('offline_payment_instructions')->nullable();

      $table->boolean('use_per_tenant_map_key')->default(false);

      $table->string('support_email')->nullable();
      $table->string('support_phone')->nullable();
      $table->string('support_whatsapp')->nullable();
      $table->string('website')->nullable();

    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sa_settings');
  }
};
