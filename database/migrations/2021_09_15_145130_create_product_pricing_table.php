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
            $table->softDeletes();
            $table->unsignedBigInteger('product_id');
            $table->decimal('est_landed_cost_per_unit_gbp');
            $table->decimal('marketplace_commission_pc')->nullable();
            $table->decimal('suggested_unit_price_gbp')->nullable();
            $table->decimal('unit_shipping_gbp')->nullable();
            $table->decimal('unit_price_gbp')->nullable();
            $table->decimal('gross_unit_cost_gbp')->nullable();
            $table->decimal('margin_pc')->nullable();

            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->dateTime('superseded_at')->nullable();

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
