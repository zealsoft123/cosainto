<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationTable extends Migration {
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up() {
    Schema::create('organizations', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->timestamps();
      $table->bigInteger('user')->nullable();
      $table->string('name');
      $table->string('url');
      $table->string('category');
      $table->string('payment_provider');
      $table->text('street_address');
      $table->string('city');
      $table->string('state');
      $table->string('zipcode');
      $table->string('summary');
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down() {
    Schema::dropIfExists('organization');
  }
}
