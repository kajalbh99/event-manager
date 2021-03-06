<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFriendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_friend', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id');
			$table->integer('friend_id');
			$table->boolean('is_friend')->default(false);			
			$table->date('request_on');
			$table->date('approved_on');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('friend_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('user_friend');		
    }
}
