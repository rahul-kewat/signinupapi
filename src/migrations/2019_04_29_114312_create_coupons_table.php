<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('code', 50);
            $table->string('type', 50);
            $table->double('discount', 10, 2);
            $table->double('minAmount', 10, 2)->comment('Min amount for coupon to be used');
            $table->dateTimeTz('startDateTime');
            $table->dateTimeTz('endDateTime');
            $table->unsignedBigInteger('maxTotalUse')->nullable()->comment('Max number of times the coupon can be used in total');
            $table->unsignedBigInteger('maxUseCustomer')->nullable()->comment('Max number of times the cooupon can be used by a customer');
            $table->unsignedTinyInteger('status')->comment('Enabled or disabled');
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
        Schema::dropIfExists('coupons');
    }
}
