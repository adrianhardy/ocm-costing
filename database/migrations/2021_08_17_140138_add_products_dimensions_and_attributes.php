<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductsDimensionsAndAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedMediumInteger('units_per_case')->nullable();
            $table->unsignedMediumInteger('inner_qty')->nullable();
            $table->unsignedDecimal('case_weight_kg')->nullable();
            $table->unsignedDecimal('height_m')->nullable();
            $table->unsignedDecimal('length_m')->nullable();
            $table->unsignedDecimal('width_m')->nullable();
            $table->unsignedDecimal('unit_price')->nullable();
            $table->unsignedDecimal('est_landed_cost_gbp')->nullable();
            $table->string('barcode', 64)->nullable();
            $table->string('packing_instructions', 512)->nullable();
            $table->string('product_description', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('units_per_case');
            $table->dropColumn('inner_qty');
            $table->dropColumn('unit_price');
            $table->dropColumn('case_weight_kg');
            $table->dropColumn('height_m');
            $table->dropColumn('length_m');
            $table->dropColumn('width_m');
            $table->dropColumn('barcode');
            $table->dropColumn('est_landed_cost_gbp');
            $table->dropColumn('packing_instructions');
            $table->dropColumn('product_description');
        });
    }
}
