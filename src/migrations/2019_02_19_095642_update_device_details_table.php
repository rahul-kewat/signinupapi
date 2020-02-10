<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDeviceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('device_details', function($table) {
	    $table->text('device_token')->nullable()->change();
            $table->text('device_id')->nullable()->change();
            $table->decimal('build_version', 8, 2)->nullable()->change();
            $table->boolean('platform')->nullable()->change();
            $table->string('build')->nullable()->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
