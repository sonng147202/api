<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInsuranceFormulaIdTableMpProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->integer('insurance_formula_id', false, true)->after('extra_fees')->default(0);
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
            $table->dropColumn(['insurance_formula_id']);
        });
    }
}
