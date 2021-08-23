<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('item_number', 64)->nullable();
            $table->string('article_number', 64)->nullable();
            $table->string('packing_instructions', 64)->nullable();
            $table->string('product_description', 64)->nullable();
            $table->string('name', 64)->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedMediumInteger('units_requested')->nullable();

            $table->string('barcode', 64)->nullable();
            $table->unsignedMediumInteger('units_per_case')->nullable();
            $table->unsignedMediumInteger('inner_qty')->nullable();
            $table->unsignedDecimal('case_weight_kg')->nullable();
            $table->unsignedDecimal('height_m')->nullable();
            $table->unsignedDecimal('length_m')->nullable();
            $table->unsignedDecimal('width_m')->nullable();
            $table->decimal('unit_price')->nullable();
            $table->decimal('unit_price_gbp')->nullable();

            $table->decimal('total')->nullable();
            $table->decimal('total_gbp')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_lines');
    }
}
