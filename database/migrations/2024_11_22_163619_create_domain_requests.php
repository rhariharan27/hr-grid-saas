<?php

use App\Enums\DomainRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Contracts\Domain;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('domain_requests', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('name', 191);
      $table->text('data')->nullable();
      $table->enum('status',[DomainRequestStatus::ACTIVE->value, DomainRequestStatus::REJECTED->value, DomainRequestStatus::APPROVED->value,
        DomainRequestStatus::INACTIVE->value,  DomainRequestStatus::CANCELLED->value,
        DomainRequestStatus::PENDING->value])->default(DomainRequestStatus::PENDING->value);

      $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('rejected_by_id')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('cancelled_by_id')->nullable()->constrained('users')->onDelete('set null');

      $table->string('approve_reason', 191)->nullable();
      $table->string('reject_reason', 191)->nullable();

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
    Schema::dropIfExists('domain_requests');
  }
};
