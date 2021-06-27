<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIeeeOuiUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ieee_oui_updates', function (Blueprint $table) {
            $table->id();
            $table->string('assigment_id', 6);
            $table->string('field');
            $table->string('old_value', 250);
            $table->string('new_value', 250);
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
        Schema::dropIfExists('ieee_oui_updates');
    }
}
