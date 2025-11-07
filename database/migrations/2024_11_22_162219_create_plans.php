<?php

use App\Enums\PlanDurationType;
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
    Schema::create('plans', function (Blueprint $table) {
      $table->id();
      $table->string('name', 191)->unique();
      $table->float('base_price')->default(0);
      $table->float('per_user_price')->default(0);
      $table->string('duration', 191);
      $table->text('description')->nullable();
      $table->enum('duration_type', [PlanDurationType::DAYS->value, PlanDurationType::MONTHS->value,
        PlanDurationType::YEARS->value]);
      $table->integer('included_users')->default(0);
      $table->json('modules')->nullable();
      $table->enum('status', [Status::ACTIVE->value, Status::INACTIVE->value])->default(Status::ACTIVE);

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
    Schema::dropIfExists('plans');
  }
};
