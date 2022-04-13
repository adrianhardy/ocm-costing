<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncreaseDimensionsPrecisionOnProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->unsignedDecimal('height_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('length_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('width_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('unit_height_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('unit_length_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('unit_width_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('total_cubic_m', 15,8)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedDecimal('height_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('length_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('width_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('unit_height_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('unit_length_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('unit_width_m', 15,8)->nullable()->change();
        });

        Schema::table('product_landed_costs', function (Blueprint $table) {
            $table->unsignedDecimal('height_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('length_m', 15,8)->nullable()->change();
            $table->unsignedDecimal('width_m', 15,8)->nullable()->change();
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
            $table->unsignedDecimal('height_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('length_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('width_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('unit_height_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('unit_length_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('unit_width_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('total_cubic_m', 8,2)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedDecimal('height_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('length_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('width_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('unit_height_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('unit_length_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('unit_width_m', 12,5)->nullable()->change();
        });

        Schema::table('product_landed_costs', function (Blueprint $table) {
            $table->unsignedDecimal('height_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('length_m', 12,5)->nullable()->change();
            $table->unsignedDecimal('width_m', 12,5)->nullable()->change();
        });
    }
}
