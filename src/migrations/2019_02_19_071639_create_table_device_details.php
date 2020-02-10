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
            $table->string('access_token_id', 250);
            $table->text('device_token');
            $table->text('device_id');
            $table->decimal('build_version', 8, 2);
            $table->tinyInteger('platform');
            $table->string('build',100)->default(config('app.bulid'));
            $table->timestamps();
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
