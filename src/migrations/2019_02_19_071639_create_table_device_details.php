<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDeviceDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_details', function (Blueprint $table) {
            $table->increments('id');
            $table->text('device_token')->nullable();
            $table->text('device_id')->nullable();
            $table->decimal('build_version', 8, 2)->nullable();
            $table->boolean('platform')->nullable();
            $table->string('build')->nullable();
            $table->string('access_token_id', 250);
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('device_details');
    }

}
