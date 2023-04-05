<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOaFieldsToSalesOrderLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->dateTime('oa_eta')->nullable();
            $table->unsignedMediumInteger('oa_reorder_number')->nullable();
            $table->string('oa_primary_sku', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->dropColumn('oa_eta');
            $table->dropColumn('oa_reorder_number');
            $table->dropColumn('oa_primary_sku');
        });
    }
}
