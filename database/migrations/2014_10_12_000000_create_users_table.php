<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',80);
            $table->string('email',100)->unique();
            $table->string('user_name',100)->nullable();
            $table->string('mobile',255)->nullable();
            $table->string('password',255);
            $table->string('profile_photo',100);
            $table->date('dob')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->enum('gender', ['1','2'])->comment('1 for MALE, 2 for FEMALE')->default('1');
            $table->enum('type', ['admin', 'promoter', 'user'])->default('user');
            $table->enum('login_type',['1','2','3'])->comment('1 for normal, 2 for facebook , 3 for twitter')->default('1');
            $table->enum('is_active',['0','1','2'])->comment('0 fior inactive, 1 for active, 2 for not verified user(pending)');

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
