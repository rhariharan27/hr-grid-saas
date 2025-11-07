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
    Schema::table('users', function (Blueprint $table) {
      $table->foreignId('plan_id')->nullable()->after('password')->constrained('plans')->onDelete('set null');
      $table->dateTime('plan_expired_date')->nullable()->after('plan_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('plan_id');
      $table->dropColumn('plan_expired_date');
    });
  }
};
