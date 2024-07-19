<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseFxRatesTo4dpOnCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->unsignedDecimal('default_fx_to_gbp', 8, 4)->nullable()->change();
            $table->unsignedDecimal('pricing_fx_to_gbp', 8, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->unsignedDecimal('default_fx_to_gbp', 8, 2)->change();
            $table->unsignedDecimal('pricing_fx_to_gbp', 8, 2)->change();
        });
    }
}
