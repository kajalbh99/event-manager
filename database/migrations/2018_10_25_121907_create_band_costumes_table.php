<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandCostumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('band_costumes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('band_section_id',11)->nullable();
            $table->integer('costume_type_id',11)->nullable();
            $table->string('costume_photo',255)->nullable();
            $table->string('upgrade_options',255)->nullable();
            $table->double('base_price')->nullable();
            $table->double('deposit_amount')->nullable();
            $table->date('due_date')->nullable();
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
        Schema::dropIfExists('band_costumes');
    }
}
