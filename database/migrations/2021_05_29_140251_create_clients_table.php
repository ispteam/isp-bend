<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements("clientId");
            $table->string("password");
            $table->string("name")->nullable(); //Because user might register his name in arabic not in english. That's why it's nullable (can be null)
            $table->string("nameInArabic")->nullable();  //Because user might register his name in english not in arabic. That's why it's nullable (can be null)
            $table->string("email")->unique();
            $table->json("address"); //Why the format is json? because the address holds many fields such as city, country, zip code, etc...
            $table->string("phone")->unique();
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
        Schema::dropIfExists('clients');
    }
}
