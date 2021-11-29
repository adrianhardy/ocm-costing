<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorAndFxRateToShipments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedDecimal('invoice_amt')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropForeign(['sales_order_id']);
            $table->dropColumn('invoice_amt');
            $table->dropColumn('creator_id');
            $table->dropColumn('sales_order_id');
        });
    }
}
