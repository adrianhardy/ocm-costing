<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalConsignmentReferenceInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->string('supplier_ref', 255)->nullable();
            $table->string('our_order_number', 200)->nullable();
            $table->string('vessel_name', 64)->nullable();
            $table->string('container_number', 32)->nullable();
            $table->string('bl_number', 32)->nullable();
            $table->string('shipping_agent_ref', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consignments', function (Blueprint $table) {
            $table->dropColumn('supplier_ref');
            $table->dropColumn('our_order_number');
            $table->dropColumn('vessel_name');
            $table->dropColumn('container_number');
            $table->dropColumn('bl_number');
            $table->dropColumn('shipping_agent_reference');
        });
    }
}
