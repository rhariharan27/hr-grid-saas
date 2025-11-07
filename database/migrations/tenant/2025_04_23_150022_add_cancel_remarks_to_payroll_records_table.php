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
    Schema::table('payroll_records', function (Blueprint $table) {
      $table->text('cancel_remarks')->nullable()->after('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('payroll_records', function (Blueprint $table) {
      $table->dropColumn('cancel_remarks');
    });
  }
};
