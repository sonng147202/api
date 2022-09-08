<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMpCoupons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coupon_code', 8)->unique();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->tinyInteger('status')->default(0)->comment('-1: deleted; 0: un-active; 1: active');
            $table->tinyInteger('sale_off_type', false, true)->default(0)->comment('0: percent; 1: fix price(money)');
            $table->float('sale_off_amount', 12, 2, false, true)->default(0.00);
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
        Schema::dropIfExists('mp_coupons');
    }
}
