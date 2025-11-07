<?php

use App\Enums\DiscountType;
use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('coupons', function (Blueprint $table) {
      $table->id();
      $table->enum('discount_type', [DiscountType::FIXED->value, DiscountType::PERCENTAGE->value])->default(DiscountType::FIXED->value);
      $table->string('code', 191)->unique();
      $table->dateTime('expiry_date')->nullable();
      $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
      $table->float('discount')->default(0.00);
      $table->float('limit')->default(0);
      $table->longText('description')->nullable();
      $table->enum('status', [Status::ACTIVE->value, Status::INACTIVE->value])->default(Status::ACTIVE->value);

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
    Schema::dropIfExists('coupons');
  }
};
