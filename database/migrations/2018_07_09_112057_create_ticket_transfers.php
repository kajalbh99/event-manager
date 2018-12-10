<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketTransfers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
    {
       
        Schema::create('ticket_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id');
            $table->integer('sender_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('events')->onDelete('cascade');
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
         Schema::dropIfExists('ticket_transfers');
    }
}
