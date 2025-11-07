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
    Schema::table('attendances', function (Blueprint $table) {
      // Change the status field to string
      $table->string('status')->change();

      // Add a new column for attendance type
      $table->string('attendance_type')->after('status')->nullable();

      // Add a new column for attendance date
      $table->date('attendance_date')->after('attendance_type')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('attendances', function (Blueprint $table) {

      // Drop the new columns
      $table->dropColumn('attendance_type');
      $table->dropColumn('attendance_date');
    });
  }
};
