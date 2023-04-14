<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityToProductPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_pricing', function (Blueprint $table) {
            $table->string('priority', 32)->nullable();
            $table->dateTime('exported_at')->nullable();
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
            $table->dropColumn('priority');
            $table->dropColumn('exported_at');
        });
    }
}
