<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandCarnivalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('band_carnivals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('band_id');
            $table->integer('carnival_id');
            $table->foreign('band_id')->references('id')->on('bands')->onDelete('cascade');
            $table->foreign('carnival_id')->references('id')->on('carnivals')->onDelete('cascade');
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
        Schema::dropIfExists('band_carnivals');
    }
}
