<?php

use App\Enums\DeviceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('user_devices', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->on('users')->onDelete('cascade');
      $table->string('device_id');
      $table->enum('device_type', [DeviceType::ANDROID->value, DeviceType::IOS->value, DeviceType::WEB->value, DeviceType::LINUX->value, DeviceType::WINDOWS->value, DeviceType::MAC->value, DeviceType::OTHER->value])->default(DeviceType::OTHER->value);
      $table->string('device_name')->nullable();
      $table->string('device_model')->nullable();
      $table->string('os_version')->nullable();
      $table->string('app_version')->nullable();
      $table->string('push_token')->nullable();

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
    Schema::dropIfExists('user_devices');
  }
};
