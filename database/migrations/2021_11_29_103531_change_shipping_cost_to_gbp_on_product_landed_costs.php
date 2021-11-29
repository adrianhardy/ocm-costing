<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShippingCostToGbpOnProductLandedCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_landed_costs', function (Blueprint $table) {
            $table->renameColumn(
                'shipping_cost_per_cuft_usd',
                'shipping_cost_per_cuft'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_landed_costs', function (Blueprint $table) {
            $table->renameColumn(
                'shipping_cost_per_cuft',
                'shipping_cost_per_cuft_usd'
            );
        });
    }
}
