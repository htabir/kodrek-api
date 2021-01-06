<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresetUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preset_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->integer('presetId');
            $table->boolean('status')->default(true);
            $table->boolean('like')->default(false);
            $table->integer('days')->default(0);
            $table->timestamps();

            $table->foreign('username')->references('username')->on('users');
            //$table->foreign('presetId')->references('presetId')->on('presets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preset_users');
    }
}
