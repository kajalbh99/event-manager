<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('member_id');
            $table->integer('user_id');
            $table->integer('count');
            $table->boolean('status')->default('0');
            $table->string('token_id')->nullable();
            $table->integer('ticket_type')->nullable();
            $table->longText('payment_response')->nullable();
            $table->longText('refund_response')->nullable();
            $table->timestamps();
			$table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
		
    }
}
