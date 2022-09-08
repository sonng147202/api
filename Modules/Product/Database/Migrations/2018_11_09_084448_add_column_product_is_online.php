<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnProductIsOnline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->tinyInteger('product_type_online')->default(1)
                ->comment('Sản phẩm cấp đơn ngoại tuyến hay trực tuyến 0:ngoại tuyến. 1: trực tuyến');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->dropColumn('product_type_online');
        });
    }
}
