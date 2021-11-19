<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFbaShippingFeesToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->unsignedDecimal('unit_uk_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_se_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_fr_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_de_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_es_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_it_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_nl_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_usd_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_cad_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_mxn_fba_fee', 8,3)->after('es_vat');
            $table->unsignedDecimal('unit_aud_fba_fee', 8,3)->after('es_vat');
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
        });
    }
}
