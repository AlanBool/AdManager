<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStreamdatas3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('streamdatas', function (Blueprint $table) {
            $table->uuid('sys_click_id')->comment('系统产生的click_id,uuid格式')->nullable();
            $table->string('clicktime')->comment('点击时间')->nullable();;
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
