<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIeeeOuiFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ieee_oui_files', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->unsignedBigInteger('size');
            $table->string('hash', 64);
            $table->string('registry', 5);
            $table->boolean('is_processed')->default(false);
            $table->boolean('is_deleted')->default(false);
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
        Schema::dropIfExists('ieee_oui_files');
    }
}
