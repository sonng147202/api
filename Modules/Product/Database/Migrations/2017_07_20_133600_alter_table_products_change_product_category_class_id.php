<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableProductsChangeProductCategoryClassId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_products', function (Blueprint $table) {
            $table->renameColumn('product_category_class_id', 'category_class_id');
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
            $table->renameColumn('category_class_id', 'product_category_class_id');
        });
    }
}
