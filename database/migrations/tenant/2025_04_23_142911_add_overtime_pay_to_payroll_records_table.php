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
      $table->decimal('overtime_pay', 10, 2) // Match precision with other salary fields
      ->default(0)
        ->after('basic_salary') // Place after basic salary
        ->comment('Calculated overtime earnings for the period');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('payroll_records', function (Blueprint $table) {
      $table->dropColumn('overtime_pay');
    });
  }
};
