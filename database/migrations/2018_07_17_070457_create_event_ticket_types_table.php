<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTicketTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->string('ticket_type');
			$table->integer('total_tickets');
            $table->float('ticket_price');
            $table->integer('tickets_sold')->default(0);
			$table->date('ticket_start_date')->nullable();
			$table->date('ticket_end_date')->nullable();
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
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
        Schema::dropIfExists('event_ticket_types');
    }
}
