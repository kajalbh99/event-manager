<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarnivalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carnivals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('carnival_name',45);
            $table->string('carnival_banner',100);
            $table->string('carnival_slug',100);
            $table->enum('is_active',['0','1'])->default('1');
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
        Schema::dropIfExists('carnivals');
    }
}
