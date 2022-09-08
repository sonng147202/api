<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgencyIdToTableMpProductAgencyCommissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_product_agency_commissions', function (Blueprint $table) {
            $table->integer('agency_id')->after('product_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_product_agency_commissions', function (Blueprint $table) {
            $table->dropColumn(['agency_id']);
        });
    }
}
