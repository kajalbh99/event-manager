<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransportationReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportation_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transportation_id');
            $table->integer('user_id');
            $table->string('review_title',45);
            $table->string('review_description',45);
            $table->integer('rating');
			$table->tinyInteger('is_approved',1)->default(0);
            $table->foreign('transportation_id')->references('id')->on('transportation')->onDelete('cascade');
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
        Schema::dropIfExists('transportation_reviews');
    }
}
