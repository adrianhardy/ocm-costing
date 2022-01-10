<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingNameAndProductsToFactories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factories', function (Blueprint $table) {
            $table->string('shipping_name', 128)->nullable();
            $table->string('products_description', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factories', function (Blueprint $table) {
            $table->dropColumn('shipping_name');
            $table->dropColumn('products_description');
        });
    }
}
