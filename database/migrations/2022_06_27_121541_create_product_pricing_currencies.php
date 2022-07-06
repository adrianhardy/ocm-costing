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

        Schema::table('product_pricing', function(Blueprint $table) {
            $table->dropColumn('uk_vat');
            $table->dropColumn('fr_vat');
            $table->dropColumn('de_vat');
            $table->dropColumn('it_vat');
            $table->dropColumn('nl_vat');
            $table->dropColumn('es_vat');
            $table->dropColumn('au_vat');
            $table->dropColumn('se_vat');
            $table->dropColumn('pl_vat');
            $table->dropColumn('unit_fr_price');
            $table->dropColumn('unit_de_price');
            $table->dropColumn('unit_it_price');
            $table->dropColumn('unit_nl_price');
            $table->dropColumn('unit_es_price');
            $table->dropColumn( 'unit_se_price');
            $table->dropColumn('unit_sf_uk_price');
            $table->dropColumn('unit_sf_de_price');
            $table->dropColumn('unit_sf_it_price');
            $table->dropColumn('unit_sf_nl_price');
            $table->dropColumn('unit_sf_es_price');
            $table->dropColumn('unit_sf_se_price');
            $table->dropColumn('unit_fr_shipping');
            $table->dropColumn('unit_de_shipping');
            $table->dropColumn('unit_it_shipping');
            $table->dropColumn('unit_nl_shipping');
            $table->dropColumn('unit_es_shipping');
            $table->dropColumn('unit_se_shipping');
            $table->dropColumn('unit_usd_shipping');
            $table->dropColumn('unit_cad_shipping');
            $table->dropColumn('unit_mxn_shipping');
            $table->dropColumn('unit_aud_shipping');
            $table->dropColumn('unit_pln_shipping');
            $table->dropColumn('unit_sek_shipping');
            $table->dropColumn('unit_sf_usd_price');
            $table->dropColumn('unit_sf_cad_price');
            $table->dropColumn('unit_sf_mxn_price');
            $table->dropColumn('unit_sf_aud_price');
            $table->dropColumn('unit_sf_pln_price');
            $table->dropColumn('unit_sf_sek_price');
            $table->dropColumn('unit_usd_price');
            $table->dropColumn('unit_cad_price');
            $table->dropColumn('unit_mxn_price');
            $table->dropColumn('unit_aud_price');
            $table->dropColumn('unit_pln_price');
            $table->dropColumn('unit_sek_price');
            $table->dropColumn('usd_fx_rate');
            $table->dropColumn('cad_fx_rate');
            $table->dropColumn('mxn_fx_rate');
            $table->dropColumn('aud_fx_rate');
            $table->dropColumn('pln_fx_rate');
            $table->dropColumn('sek_fx_rate');
            $table->dropColumn('unit_uk_fba_fee');
            $table->dropColumn('unit_se_fba_fee');
            $table->dropColumn('unit_fr_fba_fee');
            $table->dropColumn('unit_de_fba_fee');
            $table->dropColumn('unit_es_fba_fee');
            $table->dropColumn('unit_it_fba_fee');
            $table->dropColumn('unit_nl_fba_fee');
            $table->dropColumn('unit_usd_fba_fee');
            $table->dropColumn('unit_cad_fba_fee');
            $table->dropColumn('unit_mxn_fba_fee');
            $table->dropColumn('unit_aud_fba_fee');
            $table->dropColumn('unit_pln_fba_fee');
            $table->dropColumn('unit_sek_fba_fee');
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
