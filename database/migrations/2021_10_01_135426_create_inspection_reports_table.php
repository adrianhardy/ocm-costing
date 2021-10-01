<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->unsignedBigInteger('sales_order_line_id');
            $table->unsignedBigInteger('consignment_id');

            $table->decimal('unit_height_m', 8,3);
            $table->decimal('unit_weight_m', 8,3);
            $table->decimal('unit_length_m', 8,3);
            $table->decimal('case_height_m', 8,3);
            $table->decimal('case_weight_m', 8,3);
            $table->decimal('case_length_m', 8,3);
            $table->boolean('is_unit_size_ok')->nullable();
            $table->boolean('is_case_size_ok')->nullable();
            $table->string('dimensions_comments',512)->nullable();

            //worth making things like packaging / instructions + other checkboxes boolean, and anything that
            //needs to be said is in the comments?
            $table->boolean('is_packaging_ok')->nullable();
            $table->string('packaging_comments', 512)->nullable();
            $table->string('is_instructions_ok')->nullable();
            $table->string('instructions_comments', 512)->nullable();
            $table->string('is_product_test_run_ok')->nullable();
            $table->string('product_test_comments', 512)->nullable();
            $table->string('is_appearance_ok')->nullable();
            $table->string('appearance_comments', 512)->nullable();
            $table->string('is_barcode_ok')->nullable();
            $table->string('barcode_comments', 512)->nullable();
            $table->string('is_barcode_inner_okay')->nullable();
            $table->string('barcode_inner_comments', 512)->nullable();
            $table->string('barcode_outer')->nullable();
            $table->string('barcode_outer_comments', 512)->nullable();
            $table->string('comments', 512)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('approver_id')->nullable();

            $table->foreign('approver_id')->references('id')->on('users');
            $table->foreign('sales_order_line_id')->references('id')->on('sales_order_lines');
            $table->foreign('consignment_id')->references('id')->on('consignments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inspection_reports');
    }
}
