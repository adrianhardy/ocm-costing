<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPricingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_pricing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('product_id');
            $table->decimal('est_landed_cost_per_unit_gbp');
            $table->decimal('marketplace_unit_commission_gbp')->nullable();
            $table->decimal('marketplace_unit_fee_gbp')->nullable();
            $table->decimal('unit_shipping_gbp')->nullable();
            $table->decimal('unit_duty_gbp')->nullable();
            $table->decimal('suggested_price_gbp')->nullable();
            $table->decimal('price_gbp')->nullable();

            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_pricing');
    }
}
