<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotel_gallery', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_id');
            $table->string('hotel_gallery_image',100);
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
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
        Schema::dropIfExists('hotel_gallery');
    }
}
