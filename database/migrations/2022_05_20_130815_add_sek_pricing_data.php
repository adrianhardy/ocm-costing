<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSekPricingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('unit_sek_shipping', 8,3)->nullable()->after('unit_aud_shipping');
            $table->decimal('unit_sek_price', 8,3)->nullable()->after('unit_aud_shipping');
            $table->decimal('unit_sf_sek_price', 8,3)->nullable()->after('unit_sf_aud_price');
            $table->unsignedDecimal('unit_sek_fba_fee', 8,3)->nullable()->after('unit_aud_fba_fee');
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
            $table->dropColumn('unit_sek_price');
            $table->dropColumn('unit_sek_shipping');
            $table->dropColumn('unit_sf_sek_price');
            $table->dropColumn('unit_sek_fba_fee');
        });
    }
}
