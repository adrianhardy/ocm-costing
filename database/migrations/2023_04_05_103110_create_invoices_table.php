<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('creator_id');
            $table->string('reference', 64);
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->decimal('sub_total', 8, 2)->nullable();
            $table->boolean('is_approval_required')->default(false);

            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
