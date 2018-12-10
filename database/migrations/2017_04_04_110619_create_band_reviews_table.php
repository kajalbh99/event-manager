<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('band_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('band_id');
            $table->integer('user_id');
            $table->string('review_title',45);
            $table->string('review_description',45);
            $table->integer('rating');
            $table->foreign('band_id')->references('id')->on('bands')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('band_reviews');
    }
}
