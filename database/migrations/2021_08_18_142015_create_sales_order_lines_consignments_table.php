<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderLinesConsignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_lines_consignments', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_line_id');
            $table->unsignedBigInteger('consignment_id');
            $table->unsignedBigInteger('qty_shipped')->nullable();
            $table->decimal('goods_cost')->nullable();
            $table->decimal('goods_cost_gbp')->nullable();
            $table->decimal('shipping_cost_gbp')->nullable();
            $table->decimal('shipping_cost_gbp_per_unit')->nullable();
            $table->foreign('sales_order_line_id')->references('id')->on('sales_order_lines');
            $table->foreign('consignment_id')->references('id')->on('consignments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_order_lines_consignments');
    }
}
