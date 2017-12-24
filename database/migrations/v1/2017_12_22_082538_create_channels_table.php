<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name')->comment('渠道名称');
            $table->unsignedInteger('parent_id')->default(0)->comment('父渠道id,0为父渠道');
            $table->unsignedInteger('add_user_id')->comment('广告添加用户id');
            $table->uuid('token')->comment('渠道token');
            $table->timestamps();
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
}
