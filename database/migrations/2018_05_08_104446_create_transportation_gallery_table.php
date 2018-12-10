<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportationGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportation_gallery', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transportation_id');
            $table->string('transportation_gallery_image',100);
            $table->foreign('transportation_id')->references('id')->on('transportation')->onDelete('cascade');
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
        Schema::dropIfExists('transportation_gallery');
    }
}
