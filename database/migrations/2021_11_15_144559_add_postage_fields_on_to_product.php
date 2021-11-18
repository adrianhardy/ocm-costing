<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPostageFieldsOnToProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('uk_postage_method', 128)->nullable();
            $table->string('eu_postage_method', 128)->nullable();
            $table->string('row_postage_method', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('uk_postage_method');
            $table->dropColumn('eu_postage_method');
            $table->dropColumn('row_postage_method');
        });
    }
}
