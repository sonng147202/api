<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommissionIdToAgenciesTableAndCreateInsuranceAgencyCommissionLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('insurance_agencies', function (Blueprint $table) {
            $table->bigInteger('commission_id')->nullable()->after('manager_id');
        });

        Schema::create('insurance_agency_commission_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('insurance_agency_id', false, true)->default(0);
            $table->bigInteger('commission_id', false, true)->default(0);
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
        Schema::table('insurance_agencies', function (Blueprint $table) {
            $table->dropColumn(['commission_id']);
        });
        Schema::dropIfExists('insurance_agency_commission_levels');
    }
}
