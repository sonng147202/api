<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnExtraForProductTableMpProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->integer('extra_for_product', false, true)->default(0)->nullable()->after('extra_for_insurance_type');
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
            $table->dropColumn(['extra_for_product']);
        });
    }
}
