<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmittedAtToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->dateTime('submitted_at')->after('gross_unit_cost_gbp')->nullable();
            $table->unsignedBigInteger('submitter_id')->after('submitted_at')->nullable();
            $table->foreign('submitter_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->dropForeign('product_pricing_submitter_id_foreign');
            $table->dropColumn('submitted_at');
            $table->dropColumn('submitter_id');
        });
    }
}
