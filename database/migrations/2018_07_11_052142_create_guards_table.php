<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name',100);
            $table->string('password',255);
            $table->string('profile_photo',100)->nullable();
            $table->integer('promoter_id')->nullable();
            $table->integer('event_id')->nullable();
            $table->enum('is_active',['0','1','2'])->comment('0 fior inactive, 1 for active, 2 for not verified user(pending)');

            $table->rememberToken();
			$table->foreign('promoter_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
			$table->softDeletes(); 
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
        Schema::dropIfExists('guards');
    }
}
