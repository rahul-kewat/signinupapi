<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameFieldInAdressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function($table) {
            $table->string('name')->nullable()->after('place_id');
            $table->string('phone')->nullable()->after('name');
            $table->string('country')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('pincode')->nullable()->change();
            $table->string('full_address')->nullable()->change();
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
