<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicktPdfs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ticket_type_pdfs', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('ticket_type_id');
			$table->string('file');
			$table->boolean('allocated')->default(0);
			$table->foreign('ticket_type_id')->references('id')->on('event_ticket_types')->onDelete('cascade');
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
        Schema::dropIfExists('ticket_type_pdfs');
    }
}
