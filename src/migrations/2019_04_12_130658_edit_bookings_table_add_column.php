<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditBookingsTableAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('vender_id')->nullable()->after('user_id')->comment('User who accepted service request');
            $table->double('price', 10, 2)->nullable()->after('vender_id')->comment('Price of user for booked slot');
            $table->double('total_price', 10, 2)->nullable()->after('price')->comment('Total amount taken for booked task');
            $table->string('notes')->nullable()->after('total_price')->comment('Description added for task');
            $table->enum('booking_type', ['hourly', 'fixed'])->nullable()->after('notes')->comment('Type of job hourly or fixed');
            $table->string('feedback')->nullable()->after('booking_type')->comment('Feedback for this booking');
            $table->string('slot')->nullable()->after('vender_id')->comment('Booking is made for which time');
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
