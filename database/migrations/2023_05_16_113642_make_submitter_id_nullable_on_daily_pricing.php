<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSubmitterIdNullableOnDailyPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_pricing', function (Blueprint $table) {
            $table->unsignedBigInteger('submitter_id')->nullable()->change();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daily_pricing', function (Blueprint $table) {
            $table->unsignedBigInteger('submitter_id')->change();
        });
    }
}
