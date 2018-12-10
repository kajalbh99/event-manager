<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('band_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('section_name',255)->nullable();
            $table->integer('band_id',11)->nullable();
            $table->integer('user_id',11)->nullable();
            $table->enum('is_active',['0','1','2'])->comment('0 for inactive, 1 for active')->default('0');
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
        Schema::dropIfExists('band_sections');
    }
}
