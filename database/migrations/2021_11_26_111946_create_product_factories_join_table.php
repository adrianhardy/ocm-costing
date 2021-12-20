<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductFactoriesJoinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['factory_id']);
            $table->dropColumn('factory_id');
        });
        Schema::create('products_factories', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('factory_id');
            $table->unique(['product_id', 'factory_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('factory_id')->references('id')->on('factories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_factories');
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('factory_id')->nullable();
            $table->foreign('factory_id')->references('id')->on('factories');
        });
    }
}
