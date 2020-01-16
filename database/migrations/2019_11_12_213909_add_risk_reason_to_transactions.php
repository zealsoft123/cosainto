<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiskReasonToTransactions extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('transactions', function (Blueprint $table) {
      $table->string('risk_reason')->after('risk_score')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('transactions', function (Blueprint $table) {
      $table->dropColumn('risk_reason');
    });
  }
}
