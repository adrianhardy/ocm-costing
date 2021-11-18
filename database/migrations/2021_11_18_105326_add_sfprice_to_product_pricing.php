<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSfpriceToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('unit_sf_uk_price', 8,3)->nullable();
            $table->decimal('unit_sf_fr_price', 8,3)->nullable();
            $table->decimal('unit_sf_de_price', 8,3)->nullable();
            $table->decimal('unit_sf_it_price', 8,3)->nullable();
            $table->decimal('unit_sf_es_price', 8,3)->nullable();
            $table->decimal('unit_sf_nl_price', 8,3)->nullable();
            $table->decimal('unit_sf_se_price', 8,3)->nullable();
            $table->decimal('unit_sf_usd_price', 8,3)->nullable();
            $table->decimal('unit_sf_cad_price', 8,3)->nullable();
            $table->decimal('unit_sf_mxn_price', 8,3)->nullable();
            $table->decimal('unit_sf_aud_price', 8,3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->dropColumn('unit_sf_uk_price');
            $table->dropColumn('unit_sf_fr_price');
            $table->dropColumn('unit_sf_de_price');
            $table->dropColumn('unit_sf_it_price');
            $table->dropColumn('unit_sf_es_price');
            $table->dropColumn('unit_sf_nl_price');
            $table->dropColumn('unit_sf_se_price');
            $table->dropColumn('unit_sf_usd_price');
            $table->dropColumn('unit_sf_cad_price');
            $table->dropColumn('unit_sf_mxn_price');
            $table->dropColumn('unit_sf_aud_price');
        });
    }
}
