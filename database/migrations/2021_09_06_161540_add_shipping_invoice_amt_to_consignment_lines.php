<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingInvoiceAmtToConsignmentLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_lines_consignments', function (Blueprint $table) {
            $table->decimal('invoice_amt')->nullable();
            $table->decimal('est_landed_cost_per_unit')->nullable();
            $table->decimal('est_landed_cost_per_unit_gbp')->nullable();
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
            $table->dropColumn('invoice_amt');
            $table->dropColumn('est_landed_cost_per_unit');
            $table->dropColumn('est_landed_cost_per_unit_gbp');
        });
    }
}
