<?php

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('subscriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
      $table->integer('users_count')->default(0);
      $table->integer('additional_users')->default(0);
      $table->float('total_price')->default(0);
      $table->float('per_user_price')->default(0);
      $table->timestamp('start_date');
      $table->timestamp('end_date')->nullable();
      $table->enum('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::INACTIVE->value,
        SubscriptionStatus::CANCELLED->value, SubscriptionStatus::EXPIRED->value
      ])->default(SubscriptionStatus::INACTIVE->value);

      $table->string('tenant_id', 191)->nullable();
      $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by_id')->nullable()->constrained('users')->onDelete('set null');
      $table->softDeletes();
      $table->timestamps();
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
