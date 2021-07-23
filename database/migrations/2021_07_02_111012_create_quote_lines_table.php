<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuoteLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('quote_id');

            $table->string('packing_instructions', 512)->nullable();
            $table->string('barcode', 32)->nullable();

            $table->unsignedDecimal('unit_price')->nullable();
            $table->unsignedInteger('case_qty')->nullable();
            $table->unsignedInteger('case_inner_qty')->nullable();
            $table->unsignedInteger('total_unit_qty')->nullable();

            $table->unsignedDecimal('case_volume_cu_m')->nullable();
            $table->unsignedDecimal('case_width_m')->nullable();
            $table->unsignedDecimal('case_height_m')->nullable();
            $table->unsignedDecimal('case_depth_m')->nullable();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('quote_id')->references('id')->on('quotes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_lines');
    }
}
