<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIeeeOuiStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ieee_oui_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('registry', 10);
            $table->unsignedInteger('additions')->default(0);
            $table->unsignedInteger('deletions')->default(0);
            $table->unsignedInteger('updates')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ieee_oui_statistics');
    }
}
