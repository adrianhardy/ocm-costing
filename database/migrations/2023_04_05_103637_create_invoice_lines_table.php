<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('sales_order_line_id');
            $table->decimal('unit_price', 8,2);
            $table->unsignedMediumInteger('qty');
            $table->decimal('sub_total', 8,2);

            $table->foreign('invoice_id')->references('id')->on('invoices');
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
        Schema::dropIfExists('invoice_lines');
    }
}
