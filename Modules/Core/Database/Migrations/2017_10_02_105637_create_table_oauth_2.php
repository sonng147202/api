<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('access_token', 40)->primary();
            $table->string('app_keycode', 256)->nullable();
            $table->string('client_id', 80);
            $table->string('user_id', 255)->nullable();
            $table->timestamp('expires');
            $table->string('scope', 2000)->nullable();
            $table->tinyInteger('user_type')->nullable()->comment('1: customer, 2: agency');
            $table->string('device_token', 512)->nullable();
            $table->timestamps();
        });
        Schema::create('oauth_authorization_codes', function (Blueprint $table) {
            $table->string('authorization_code', 40)->primary();
            $table->string('client_id', 80);
            $table->string('user_id', 255)->nullable();
            $table->string('redirect_uri', 2000)->nullable();
            $table->timestamp('expires');
            $table->string('scope', 2000)->nullable();
        });
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('client_id', 40)->primary();
            $table->string('client_secret', 80)->nullable();
            $table->string('redirect_uri', 2000)->nullable();
            $table->string('grant_types', 80)->nullable();
            $table->string('scope', 100)->nullable();
            $table->string('user_id', 80)->nullable();
        });
        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('refresh_token', 40)->primary();
            $table->string('client_id', 80);
            $table->string('user_id', 255)->nullable();
            $table->timestamp('expires');
            $table->string('scope', 2000)->nullable();
        });
        Schema::create('oauth_scopes', function (Blueprint $table) {
            $table->text('scope', 40);
            $table->tinyInteger('is_default', 80);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_authorization_codes');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_scopes');
    }
}
