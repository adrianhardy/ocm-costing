<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SalesOrdersUsersAuthentication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_authentications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->softDeletes();
            $table->timestamps();
            $table->dateTime('expires_at');
            $table->dateTime('consumed_at')->nullable();
            $table->string('password', 64)->nullable();
            $table->string('token', 128)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sales_order_id');

            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('sales_order_authentications');
    }
}
