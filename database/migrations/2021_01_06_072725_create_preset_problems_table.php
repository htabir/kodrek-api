<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresetProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preset_problems', function (Blueprint $table) {
            $table->id();
            $table->integer('presetId');
            $table->string('ojName', 5);
            $table->string('problemId', 25);
            $table->timestamps();

            //$table->foreign('presetId')->constrained('presets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preset_problems');
    }
}
