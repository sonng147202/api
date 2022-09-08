<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCommissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->tinyInteger('commission_type', false, true)->default(0)->comment('0: percent; 1: fix price(money)');
            $table->float('commission_amount', 12, 2, false, true)->default(0.00);
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
        Schema::dropIfExists('mp_commissions');
    }
}
