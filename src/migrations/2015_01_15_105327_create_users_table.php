<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('fb_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('image')->nullable();
            $table->float('credit')->default(0);
            $table->enum('online', ['0', '1'])->default('0');
            $table->enum('status', ['0', '1'])->default('1');
            $table->string('phone_number')->nullable();
            $table->string('gender')->nullable();
            $table->string('password_otp')->nullable();
            $table->string('refferal_code')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('phone_country_code', 10);
            $table->integer('selected_address')->nullable()->default(0);
            $table->text('social_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('users');
    }

}
