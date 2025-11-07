<?php

use App\Enums\Gender;
use App\Enums\Language;
use App\Enums\UserAccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('first_name');
      $table->string('last_name');
      $table->string('phone')->unique();
      $table->timestamp('phone_verified_at')->nullable();
      $table->enum('status', [UserAccountStatus::ACTIVE->value, UserAccountStatus::INACTIVE->value,
        UserAccountStatus::PENDING->value, UserAccountStatus::DELETED->value])->default(UserAccountStatus::ACTIVE->value);

      $table->dateTime('delete_request_at')->nullable();
      $table->string('delete_request_reason')->nullable();
      $table->enum('language', [Language::ENGLISH->value, Language::ARABIC->value])->default(Language::ENGLISH->value);
      $table->date('dob')->nullable();
      $table->enum('gender', [Gender::MALE->value, Gender::FEMALE->value, Gender::OTHER->value])->default(Gender::MALE->value);
      $table->string('profile_picture')->nullable();
      $table->string('cover_picture')->nullable();
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');

      $table->string('address', 191)->nullable();
      $table->string('country', 191)->default('India');
      $table->string('country_code', 191)->nullable();
      $table->string('dial_code', 191)->nullable();

      $table->string('tenant_id', 191)->nullable();
      $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by_id')->nullable()->constrained('users')->onDelete('set null');
      $table->boolean('is_customer')->default(true);
      $table->rememberToken();
      $table->softDeletes();
      $table->timestamps();
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
      $table->string('email')->primary();
      $table->string('token');
      $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->foreignId('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->longText('payload');
      $table->integer('last_activity')->index();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('sessions');
  }
};
