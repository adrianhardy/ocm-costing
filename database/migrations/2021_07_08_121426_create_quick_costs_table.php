<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_costs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('reference', 200);
            $table->unsignedBigInteger('user_id')->nullable();

            $table->decimal('exchange_rate');
            $table->decimal('unit_price');

            $table->unsignedInteger('units_per_case');
            $table->decimal('case_width_m');
            $table->decimal('case_height_m');
            $table->decimal('case_depth_m');

            $table->decimal('cost_to_ship_cu_m');
            $table->decimal('commission_rate');
            $table->decimal('import_duty_rate');

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quick_costs');
    }
}
