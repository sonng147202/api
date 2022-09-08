<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnConfigPriceTypesTableMpProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->string('config_price_types', 1000)->after('default_price_attribute_values')->nullable()->comment('json data');
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
            $table->dropColumn(['config_price_types']);
        });
    }
}
