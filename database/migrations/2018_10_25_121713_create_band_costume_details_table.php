<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandCostumeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('band_costume_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('costume_id',11)->nullable();
            $table->integer('variant_id',11)->nullable();
            $table->integer('variant_size_id',11)->nullable();
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
        Schema::dropIfExists('band_costume_details');
    }
}
