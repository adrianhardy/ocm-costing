<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFbaToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->decimal('marketplace_fee')->nullable();
            $table->decimal('profit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->dropColumn('marketplace_fee');
            $table->dropColumn('profit');

        });
    }
}
