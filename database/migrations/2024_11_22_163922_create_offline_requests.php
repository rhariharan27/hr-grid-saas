<?php

use App\Enums\OfflineRequestStatus;
use App\Enums\OrderType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('offline_requests', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
      $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
      $table->integer('additional_users')->default(0);

      $table->float('per_user_price')->default(0);
      $table->float('amount');
      $table->float('discount_amount')->nullable();
      $table->float('total_amount');

      $table->enum('type', [OrderType::PLAN->value, OrderType::ADDITIONAL_USER->value,
        OrderType::RENEWAL->value, OrderType::UPGRADE->value, OrderType::DOWNGRADE->value
      ])->default(OrderType::PLAN->value);

      $table->enum('status', [OfflineRequestStatus::PENDING->value, OfflineRequestStatus::APPROVED->value,
        OfflineRequestStatus::CANCELLED->value,
        OfflineRequestStatus::REJECTED->value])->default(OfflineRequestStatus::PENDING->value);

      $table->string('cancelled_reason', 500)->nullable();
      $table->string('approval_reason', 500)->nullable();
      $table->string('notes', 500)->nullable();
      $table->string('reject_reason', 500)->nullable();

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
    Schema::dropIfExists('offline_requests');
  }
};
