<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statisticsdatas', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->uuid('advertisement_uuid')->comment('广告UUID');;
            $table->uuid('channel_uuid')->comment('渠道UUID');
            $table->integer('click_count')->comment('点击总数');
            $table->integer('conversion_count')->comment('转化总数');
            $table->integer('total_cost')->comment('转化总额');
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
        Schema::dropIfExists('statisticsdatas');
    }
}
