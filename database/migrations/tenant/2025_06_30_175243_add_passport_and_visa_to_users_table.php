<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('passport_number')->nullable()->after('gender');
      $table->date('passport_expiry_date')->nullable()->after('passport_number');
      $table->string('visa_number')->nullable()->after('passport_expiry_date');
      $table->date('visa_expiry_date')->nullable()->after('visa_number');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn([
        'passport_number',
        'passport_expiry_date',
        'visa_number',
        'visa_expiry_date'
      ]);
    });
  }
};
