<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name')->comment('广告名称');
            $table->string('track_type')->comment('广告跟踪类型');
            $table->string('loading_page')->comment('广告落地页');
            $table->string('click_track_url')->comment('广告点击汇报地址');
            $table->unsignedInteger('add_user_id')->comment('广告添加用户id');
            $table->unsignedInteger('update_user_id')->comment('广告最后一次修改用户id');
            $table->uuid('uuid')->comment('广告推广id');
            $table->timestamps();
            $table->index('uuid');//建立索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisements');
    }
}
