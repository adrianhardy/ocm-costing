<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLandedCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_landed_costs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->dateTime('date');
            $table->unsignedMediumInteger('units_per_case');
            $table->decimal('unit_price');
            $table->decimal('length_m');
            $table->decimal('height_m');
            $table->decimal('width_m');
            $table->decimal('fx_rate');
            $table->decimal('shipping_cost_per_cuft_usd');
            $table->decimal('import_duty_pc');
            $table->decimal('commission_rate_pc');
            $table->decimal('landed_cost_per_unit');
            $table->decimal('landed_cost_per_unit_gbp');

            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_landed_costs');
    }
}
