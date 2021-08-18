<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('reference', 64)->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->decimal('goods_cost')->nullable();
            $table->decimal('goods_cost_gbp')->nullable();
            $table->decimal('shipping_cost')->nullable();
            $table->decimal('shipping_cost_gbp')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('total_gbp')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('fx_rate')->nullable();
            $table->unsignedBigInteger('supplier_id');

            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_orders');
    }
}
