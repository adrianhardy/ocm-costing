<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGbpCurrencyToCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $gbp = new \App\Models\Currency([
            'name' => 'Pound Sterling',
            'code' => 'GBP',
            'default_fx_to_gbp' => 1
        ]);
        $gbp->save();
        $uk = \App\Models\Country::query()->where('iso_2_code', 'GB')->first();
        $uk->currency()->associate($gbp);
        $uk->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var \App\Models\Country $uk */
        $uk = \App\Models\Country::query()->where('iso_2_code', 'UK')->first();
        $uk->currency()->dissociate();
        $uk->save();

        $gbp = \App\Models\Currency::query()->where('code', 'GBP')->first();
        $gbp->save();
    }
}
