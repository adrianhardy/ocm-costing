<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatAndRowPricePointsToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->dropColumn('suggested_unit_price_gbp');
            $table->dropColumn('profit');
            $table->dropColumn('margin_pc');
            $table->dropColumn('marketplace_fee');

            $table->decimal('uk_vat', 8,3)->nullable();
            $table->decimal('fr_vat', 8,3)->nullable();
            $table->decimal('de_vat', 8,3)->nullable();
            $table->decimal('it_vat', 8,3)->nullable();
            $table->decimal('nl_vat', 8,3)->nullable();
            $table->decimal('es_vat', 8,3)->nullable();
            $table->decimal('unit_fr_price', 8,3)->nullable();
            $table->decimal('unit_de_price', 8,3)->nullable();
            $table->decimal('unit_it_price', 8,3)->nullable();
            $table->decimal('unit_nl_price', 8,3)->nullable();
            $table->decimal('unit_es_price', 8,3)->nullable();
            $table->decimal('unit_fr_shipping', 8,3)->nullable();
            $table->decimal('unit_de_shipping', 8,3)->nullable();
            $table->decimal('unit_it_shipping', 8,3)->nullable();
            $table->decimal('unit_nl_shipping', 8,3)->nullable();
            $table->decimal('unit_es_shipping', 8,3)->nullable();
            $table->decimal('unit_usd_shipping', 8,3)->nullable();
            $table->decimal('unit_cad_shipping', 8,3)->nullable();
            $table->decimal('unit_mxn_shipping', 8,3)->nullable();
            $table->decimal('unit_usd_price', 8,3)->nullable();
            $table->decimal('unit_cad_price', 8,3)->nullable();
            $table->decimal('unit_mxn_price', 8,3)->nullable();
            $table->decimal('eur_fx_rate', 8,3)->nullable();
            $table->decimal('usd_fx_rate', 8,3)->nullable();
            $table->decimal('cad_fx_rate', 8,3)->nullable();
            $table->decimal('mxn_fx_rate', 8,3)->nullable();
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
            $table->decimal('suggested_unit_price_gbp')->nullable();
            $table->decimal('margin_pc')->nullable();
            $table->decimal('profit')->nullable();
            $table->decimal('marketplace_fee')->nullable();

            $table->dropColumn('uk_vat');
            $table->dropColumn('fr_vat');
            $table->dropColumn('de_vat');
            $table->dropColumn('it_vat');
            $table->dropColumn('nl_vat');
            $table->dropColumn('es_vat');
            $table->dropColumn('unit_fr_price');
            $table->dropColumn('unit_de_price');
            $table->dropColumn('unit_it_price');
            $table->dropColumn('unit_nl_price');
            $table->dropColumn('unit_es_price');
            $table->dropColumn('unit_fr_shipping');
            $table->dropColumn('unit_de_shipping');
            $table->dropColumn('unit_it_shipping');
            $table->dropColumn('unit_nl_shipping');
            $table->dropColumn('unit_es_shipping');
            $table->dropColumn('unit_usd_shipping');
            $table->dropColumn('unit_cad_shipping');
            $table->dropColumn('unit_mxn_shipping');
            $table->dropColumn('unit_usd_price');
            $table->dropColumn('unit_cad_price');
            $table->dropColumn('unit_mxn_price');
            $table->dropColumn('eur_fx_rate');
            $table->dropColumn('usd_fx_rate');
            $table->dropColumn('cad_fx_rate');
            $table->dropColumn('mxn_fx_rate');

        });
    }
}
