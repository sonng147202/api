<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlagProductFeatureAndSponsor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->tinyInteger('is_feature')->default(0)->comment('1: is feature')->after('status');
            $table->tinyInteger('is_sponsor')->default(0)->comment('1: is sponsor')->after('is_feature');
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
            $table->dropColumn(['is_feature', 'is_sponsor']);
        });
    }
}
