<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('id of the user who is creating hotel');
            $table->integer('carnival_id')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->string('hotel_name',45);
            $table->string('hotel_slug',100);
            $table->string('hotel_banner',100)->nullable();
            $table->string('hotel_title',45)->nullable();
            $table->mediumText('hotel_description');
            $table->string('hotel_location',255);
            $table->enum('hotel_privacy', ['PUBLIC','PRIVATE'])->default('PUBLIC');
             $table->enum('is_active',['0','1']);
            $table->foreign('carnival_id')->references('id')->on('carnivals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
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
        Schema::dropIfExists('hotels');
    }
}
