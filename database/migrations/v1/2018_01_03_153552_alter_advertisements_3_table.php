<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAdvertisements3Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('loading_page')->nullable()->change();
            $table->string('click_track_url')->nullable()->change();
            $table->string('source')->nullable()->change();
            $table->string('source_offer_id')->nullable()->change();
            $table->float('payout')->nullable()->change();
            $table->string('payout_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advertisements', function (Blueprint $table) {
            //
        });
    }
}
