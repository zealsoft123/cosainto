<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionApprovedPhoneToUsersTable extends Migration {
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up() {
    Schema::table('users', function (Blueprint $table) {
      $table->boolean('approved')->after('id')->default('0');
      $table->string('position')->after('name')->nullable();
      $table->string('phone_number')->after('email_verified_at')->nullable();
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down() {
  Schema::table('users', function (Blueprint $table) {
    $table->dropColumn(['approved','position', 'phone_number' ]);
  });
  }
}
