<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStreamdatas2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('streamdatas', function (Blueprint $table) {
            $table->string('idfa')->nullable()->change();
            $table->string('gaid')->nullable()->change();
            $table->string('p',500)->nullable()->change();
            $table->string('ip')->nullable()->change();
            $table->string('ua')->nullable()->change();
            $table->string('click_id',50)->nullable()->change();
            $table->string('payout')->nullable()->change();
            $table->text('url')->comment('访问URL')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('streamdatas', function (Blueprint $table) {
            //
        });
    }
}
