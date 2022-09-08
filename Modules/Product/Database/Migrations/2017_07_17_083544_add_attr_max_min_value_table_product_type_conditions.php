<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttrMaxMinValueTableProductTypeConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_price_conditions', function (Blueprint $table) {
            $table->string('attr_min_value', 150)->after('attr_value')->nullable();
            $table->string('attr_max_value', 150)->after('attr_min_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_price_conditions', function (Blueprint $table) {
            $table->dropColumn(['attr_min_value', 'attr_max_value']);
        });
    }
}
