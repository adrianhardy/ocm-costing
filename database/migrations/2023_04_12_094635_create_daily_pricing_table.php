<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyPricingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_pricing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('submitted_at');
            $table->unsignedBigInteger('submitter_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('country_id');
            $table->decimal('suggested_price', 8,2)->nullable();
            $table->decimal('fba_fee', 8,2)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->boolean('is_viable')->default(false);
            $table->decimal('margin', 8,2)->nullable();
            $table->decimal('profit', 8,2)->nullable();

            $table->foreign('submitter_id')->references('id')->on('users');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('country_id')->references('id')->on('countries');
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
        Schema::dropIfExists('daily_pricing');
    }
}
