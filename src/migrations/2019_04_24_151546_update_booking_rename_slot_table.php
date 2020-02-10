<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBookingRenameSlotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('slot');
            $table->time('slot_start_from')->after('price');
            $table->time('slot_start_end')->after('slot_start_from');
            $table->unsignedInteger('slot_id')->after('slot_start_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->double('amount', 11, 2)->after('address');
            $table->string('slot', 191)->after('vender_id');
            $table->dropColumn('slot_start_from');
            $table->dropColumn('slot_start_end');
            $table->dropColumn('slot_id');
        });
    }
}
