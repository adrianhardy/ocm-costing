<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClonedProductFieldsToSalesOrderLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->unsignedMediumInteger('units_per_case')->nullable();
            $table->unsignedMediumInteger('inner_qty')->nullable();
            $table->unsignedDecimal('case_weight_kg')->nullable();
            $table->unsignedDecimal('height_m')->nullable();
            $table->unsignedDecimal('length_m')->nullable();
            $table->unsignedDecimal('width_m')->nullable();
            $table->decimal('unit_width_m', 8, 3)->nullable();
            $table->decimal('unit_length_m', 8, 3)->nullable();
            $table->decimal('unit_height_m', 8, 3)->nullable();
            $table->decimal('unit_weight_kg', 8, 3)->nullable();
            $table->string('barcode', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            //
        });
    }
}
