<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPricingCurrencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_pricing_countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('product_pricing_id');
            $table->unsignedDecimal('gbp_fx_rate')->nullable();
            $table->unsignedDecimal('vat_rate')->nullable();
            $table->unsignedDecimal('unit_fba_price')->nullable();
            $table->unsignedDecimal('unit_fba_fee')->nullable();
            $table->unsignedDecimal('unit_sf_price')->nullable();
            $table->unsignedDecimal('unit_sf_shipping')->nullable();
            $table->unsignedDecimal('unit_fba_shipping')->nullable();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('product_pricing_id')->references('id')->on('product_pricing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_pricing_currencies');
    }
}
