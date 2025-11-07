<?php

use App\Enums\OrderStatus;
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
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
      $table->integer('additional_users')->default(0);
      $table->float('per_user_price')->default(0);
      $table->float('amount');
      $table->float('discount_amount')->nullable();
      $table->float('total_amount');
      $table->enum('status', [OrderStatus::PENDING->value, OrderStatus::PROCESSING->value,
        OrderStatus::COMPLETED->value, OrderStatus::CANCELLED->value, OrderStatus::REFUNDED->value,
        OrderStatus::FAILED->value])->default(OrderStatus::PENDING->value);

      $table->enum('type', [OrderType::PLAN->value, OrderType::ADDITIONAL_USER->value,
        OrderType::RENEWAL->value, OrderType::UPGRADE->value, OrderType::DOWNGRADE->value
      ])->default(OrderType::PLAN->value);

      $table->timestamp('paid_at')->nullable();
      $table->string('payment_id')->nullable();
      $table->text('payment_response')->nullable();
      $table->text('payment_data')->nullable();
      $table->string('payment_gateway')->nullable();

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
    Schema::dropIfExists('orders');
  }
};
