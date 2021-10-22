<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesOrderLineToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            // to get all the juicy details from the updated sales order
            $table->unsignedBigInteger('sales_order_line_id')->nullable();
            $table->foreign('sales_order_line_id')->references('id')->on('sales_order_lines');
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
            $table->dropForeign(['sales_order_line']);
            $table->dropColumn('sales_order_line_id');
        });
    }
}
