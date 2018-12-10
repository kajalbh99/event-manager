<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('id of the user who is creating event');
            $table->integer('carnival_id');
            $table->integer('country_id');
            $table->string('event_name',45);
            $table->string('event_slug',100);
            $table->string('event_banner',100);
            $table->string('event_title',45)->nullable();
            $table->mediumText('event_description');
            $table->string('event_location',255);
            $table->date('event_date')->nullable();
            $table->date('event_start_datetime')->nullable();
            $table->date('event_end_datetime')->nullable();
            $table->enum('event_privacy', ['PUBLIC','PRIVATE'])->default('PUBLIC');
            $table->string('event_type',45);
            $table->integer('total_tickets');
            $table->float('basic_ticket_price');
            $table->float('ticket_service_tax');
            $table->float('final_ticket_price');
            $table->enum('is_active',['0','1']);
            $table->enum('is_approved',['0','1'])->default();
            $table->enum('yearly',['0','1']);
            $table->integer('number_of_clicks')->default(0);
            $table->longText('ticketing_website')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('carnival_id')->references('id')->on('carnivals')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
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
        Schema::dropIfExists('events');
    }
}
