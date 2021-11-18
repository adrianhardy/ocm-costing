<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSkusToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('primary_sku', 128)->nullable();
            $table->string('primary_asin', 128)->nullable();
            $table->string('additional_skus', 2048)->nullable();
            $table->string('additional_asins', 2048)->nullable();
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
            $table->dropColumn('primary_sku');
            $table->dropColumn('primary_asin');
            $table->dropColumn('additional_skus');
            $table->dropColumn('additional_asins');
        });
    }
}
