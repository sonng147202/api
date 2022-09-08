<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProductAddUnitPriceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products',function(Blueprint $table) {
           $table->text('unit_price_type_health_insurance')
               ->comment('Đơn vị % hoặc vnd của các loại giá trong bảo hiểm sức khỏe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_product', function (Blueprint $table) {
            $table->dropColumn(['unit_price_type_health_insurance']);
        });
    }
}
