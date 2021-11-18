<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAustralianAndSwedishPricingToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('se_vat', 8,3)->nullable();
            $table->decimal('au_vat', 8,3)->nullable();
            $table->decimal('unit_se_price', 8,3)->nullable();
            $table->decimal('unit_se_shipping', 8,3)->nullable();
            $table->decimal('unit_aud_price', 8,3)->nullable();
            $table->decimal('unit_aud_shipping', 8,3)->nullable();
            $table->decimal('aud_fx_rate', 8,3)->nullable();

            $currency = new \App\Models\Currency([
                'code' => 'AUD',
                'name' => 'Australian Dollar',
                'default_fx_to_gbp' => 0.54
            ]);
            $currency->save();

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
            $table->dropColumn('se_vat');
            $table->dropColumn('au_vat');
            $table->dropColumn('unit_se_price');
            $table->dropColumn('unit_se_shipping');
            $table->dropColumn('unit_aud_price');
            $table->dropColumn('unit_aud_shipping');
            $table->dropColumn('aud_fx_rate');

            \App\Models\Currency::query()
                ->where('code', 'AUD')
                ->delete();

        });
    }
}
