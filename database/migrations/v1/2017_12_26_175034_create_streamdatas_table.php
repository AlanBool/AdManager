<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streamdatas', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->uuid('advertisement_uuid')->comment('广告UUID');
            $table->uuid('channel_uuid')->comment('渠道UUID');
            $table->string('type')->comment('数据类型，click 点击; conversion 转化');
            $table->string('idfa')->comment('设备idfa');
            $table->string('gaid')->comment('设备gaid');
            $table->string('p',500)->comment('P参数');
            $table->string('ip')->comment('ip');
            $table->string('ua')->comment('ua');
            $table->string('click_id',50)->comment('ua');
            $table->string('payout')->comment('转化单价');
            $table->timestamps();
            $table->index('advertisement_uuid');
            $table->index('channel_uuid');
            $table->index('click_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streamdatas');
    }
}
