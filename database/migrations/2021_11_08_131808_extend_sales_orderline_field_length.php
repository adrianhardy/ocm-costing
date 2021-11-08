<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendSalesOrderlineFieldLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->string('packing_instructions', 512)->nullable()->change();
            $table->string('product_description', 512)->nullable()->change();
            $table->string('name', 512)->nullable()->change();
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
            $table->string('packing_instructions', 64)->nullable()->change();
            $table->string('product_description', 64)->nullable()->change();
            $table->string('name', 64)->nullable()->change();
        });
    }
}
