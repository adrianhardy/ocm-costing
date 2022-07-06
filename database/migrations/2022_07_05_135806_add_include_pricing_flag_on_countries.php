<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncludePricingFlagOnCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('has_pricing')
                ->nullable()
                ->default(false);
        });
        \App\Models\Country::query()->whereIn('iso_2_code', [
            'GB', 'FR', 'DE', 'ES', 'IT', 'NL', 'SE', 'PL', 'MX', 'US', 'CA'
        ])->update(['has_pricing' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('has_pricing');
        });
    }
}
