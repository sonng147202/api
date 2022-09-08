<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMpProductCustomerCommissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_product_customer_commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id', false, true)->default(0);
            $table->tinyInteger('commission_type', false, true)->default(0)->comment('0: percent; 1: fix amount');
            $table->float('commission_amount', 12,2)->default(0);
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
        Schema::dropIfExists('mp_product_customer_commissions');
    }
}
