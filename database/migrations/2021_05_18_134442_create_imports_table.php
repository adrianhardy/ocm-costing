<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->dateTime('aborted_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->unsignedTinyInteger('skip_header_rows')->default(0);
            $table->string('path', 200);
            $table->string('type', 32);
            $table->mediumInteger('total_rows')->nullable();
            $table->mediumInteger('total_errors')->nullable();
            $table->mediumInteger('current_line')->nullable();
            $table->json('mapping');
            $table->json('config');
            $table->string('log_path', 200);
            $table->foreign('creator_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imports');
    }
}
