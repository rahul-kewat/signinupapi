<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCodeToBookingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings_details', function (Blueprint $table) {
            $table->string('code', 50)->after('booking_id');
            $table->string('label', 50)->after('code');
            $table->unsignedDecimal('amount', 10, 2)->after('label');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings_details', function (Blueprint $table) {
            $this->dropColumn(['code', 'label', 'amount']);
        });
    }
}
