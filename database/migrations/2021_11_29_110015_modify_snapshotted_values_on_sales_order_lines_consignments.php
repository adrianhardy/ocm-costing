<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySnapshottedValuesOnSalesOrderLinesConsignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_lines_consignments', function (Blueprint $table) {
            $table->dropColumn('goods_cost');
            $table->dropColumn('goods_cost_gbp');
            $table->dropColumn('invoice_amt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_order_lines_consignments', function (Blueprint $table) {
            $table->decimal('goods_cost')->nullable();
            $table->decimal('goods_cost_gbp')->nullable();
            $table->decimal('invoice_amt')->nullable();
        });
    }
}
