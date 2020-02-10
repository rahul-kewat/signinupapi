<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVenderSlots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vender_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vender_id')->unsigned()->index();
            $table->foreign('vender_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('slot_id')->unsigned()->index();
            $table->foreign('slot_id')->references('id')->on('slots')->onDelete('cascade');
            $table->timestamps();
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
