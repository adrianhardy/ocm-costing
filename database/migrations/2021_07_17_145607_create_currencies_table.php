<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('code', 3);
            $table->string('name', 64);
            $table->decimal('default_fx_to_gbp')->nullable();
        });

        \Illuminate\Support\Facades\DB::beginTransaction();
        $table = \Illuminate\Support\Facades\DB::table('currencies');
        $table->insert([
            'name' => 'Renminbi',
            'code' => 'RMB',
            'default_fx_to_gbp' => 0.11
        ]);
        $table->insert([
            'name' => 'US Dollar',
            'code' => 'USD',
            'default_fx_to_gbp' => 1.35
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
