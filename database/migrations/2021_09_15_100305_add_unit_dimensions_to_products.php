<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitDimensionsToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('unit_width_m', 8, 3)->after('width_m')->nullable();
            $table->decimal('unit_length_m', 8, 3)->after('width_m')->nullable();
            $table->decimal('unit_height_m', 8, 3)->after('width_m')->nullable();
            $table->decimal('unit_weight_kg', 8, 3)->after('case_weight_kg')->nullable();
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
            $table->dropColumn('unit_width_m');
            $table->dropColumn('unit_length_m');
            $table->dropColumn('unit_height_m');
            $table->dropColumn('unit_weight_kg');
        });
    }
}
