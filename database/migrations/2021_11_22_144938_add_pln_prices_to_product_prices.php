<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlnPricesToProductPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('pl_vat', 8,3)->nullable();
            $table->decimal('pln_fx_rate', 8,3)->nullable();
            $table->decimal('sek_fx_rate', 8,3)->nullable();
            $table->decimal('unit_pln_price', 8,3)->nullable();
            $table->decimal('unit_pln_shipping', 8,3)->nullable();
            $table->decimal('unit_sf_pln_price', 8,3)->nullable();
            $table->unsignedDecimal('unit_pln_fba_fee', 8,3)->nullable();
            $pln = new \App\Models\Currency([
                'code' => 'PLN',
                'name' => 'Polish Złoty',
                'default_fx_to_gbp' => 0.18
            ]);
            /** @var \App\Models\Country $country */
            $country = \App\Models\Country::query()->where('iso_2_code', 'pl')->first();
            $country->currency()->associate($pln);
            $country->save();

            $sek = new \App\Models\Currency([
                'code' => 'SEK',
                'name' => 'Swedish Krona',
                'default_fx_to_gbp' => 0.083
            ]);
            $country = \App\Models\Country::query()->where('iso_2_code', 'se')->first();
            $country->currency()->associate($sek);
            $country->save();


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
            $table->dropColumn('unit_sf_pln_price');
            $table->dropColumn('pl_vat');
            $table->dropColumn('unit_pln_price');
            $table->dropColumn('unit_pln_shipping');
            $table->dropColumn('pln_fx_rate');
            $table->dropColumn('sek_fx_rate');
            $table->dropColumn('unit_pln_fba_fee');
        });
    }
}
