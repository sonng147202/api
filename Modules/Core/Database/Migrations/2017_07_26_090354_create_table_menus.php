<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id');
            $table->string('title', 150);
            $table->string('link_type', 50)->comment('page|post')->nullable();
            $table->string('link_type_value', 150)->nullable();
            $table->string('external_url', 250)->nullable();
            $table->integer('order_number', false, true)->default(0);
            $table->tinyInteger('status')->default(1)->comment('-1: deleted; 0: un-active; 1: active');
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
        Schema::dropIfExists('menus');
    }
}
