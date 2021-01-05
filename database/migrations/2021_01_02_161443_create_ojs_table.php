<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOjsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ojs', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('ojname');
            $table->string('ojid');
            $table->integer('checkpoint')->default(0);
            $table->integer('totalSub')->default(0);
            $table->integer('disAc')->default(0);
            $table->integer('totalAc')->default(0);
            $table->integer('totalWa')->default(0);
            $table->integer('totalOt')->default(0);
            $table->json('solvedSet')->default(null)->nullable();
            $table->json('unsolvedSet')->default(null)->nullable();

            $table->timestamps();

            $table->foreign('username')->references('username')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ojs');
    }
}
