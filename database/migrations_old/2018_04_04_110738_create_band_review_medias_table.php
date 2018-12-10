<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandReviewMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('band_review_medias', function (Blueprint $table) {
            $table->increments('id');      
            $table->integer('review_id');      
            $table->string('file_name',45);
            $table->string('file_type',45);      
            $table->foreign('review_id')->references('id')->on('band_reviews')->onDelete('cascade');         
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
        Schema::dropIfExists('band_review_medias');
    }
}
