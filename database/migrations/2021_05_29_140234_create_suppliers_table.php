<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements("supplierId");
            $table->string("password");
            $table->string("name")->nullable(); //Because user might register his name in arabic not in english. That's why it's nullable (can be null)
            $table->string("nameInArabic")->nullable();  //Because user might register his name in english not in arabic. That's why it's nullable (can be null)
            $table->string("companyInEnglish")->nullable(); //Because user might register his company in arabic not in english. That's why it's nullable (can be null)
            $table->string("email")->unique();
            $table->string("verified"); // It is a way to ensure the supplier's account whether verified or not example 0: Not verified, 1:Verified, 2:Suspended
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
        Schema::dropIfExists('suppliers');
    }
}
