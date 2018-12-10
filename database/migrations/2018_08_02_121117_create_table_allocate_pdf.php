<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAllocatePdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('alocate_pdf_to_users', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('pdf_id');
			$table->integer('ticket_type_id');
			$table->integer('ticket_id');
			$table->integer('user_id');
			$table->foreign('ticket_type_id')->references('id')->on('event_ticket_types')->onDelete('cascade');
			$table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('pdf_id')->references('id')->on('ticket_type_pdfs')->onDelete('cascade');
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
        Schema::dropIfExists('alocate_pdf_to_users');
    }
}
