<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements("adminId");
            $table->string("password");
            $table->string("name")->nullable(); //Because user might register his name in arabic not in english. That's why it's nullable (can be null)
            $table->string("nameInArabic")->nullable();  //Because user might register his name in english not in arabic. That's why it's nullable (can be null)
            $table->string("email")->unique();
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
        Schema::dropIfExists('admins');
    }
}
